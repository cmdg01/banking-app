<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransferRequest;
use App\Models\Bank;
use App\Models\Transfer;
use App\Models\User;
use App\Services\DirectDwollaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransferController extends Controller
{
    protected $dwollaService;

    public function __construct(DirectDwollaService $dwollaService)
    {
        $this->dwollaService = $dwollaService;
    }

    public function index()
    {
        $user = auth()->user();
        $transfers = Transfer::with(['sender', 'receiver', 'senderBank', 'receiverBank'])
            ->where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('transfers.index', compact('transfers'));
    }

    public function create()
    {
        $banks = auth()->user()->banks()
            ->whereNotNull('dwolla_funding_source_id')
            ->get();

        if ($banks->count() < 2) {
            return redirect()->route('banks.index')
                ->with('error', 'You need at least two bank accounts linked to make a transfer.');
        }

        return view('transfers.create', compact('banks'));
    }

    public function store(StoreTransferRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = auth()->user();
            
            // Get source bank
            $sourceBank = Bank::where('id', $request->source_bank_id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            // Get destination bank
            $destinationBank = Bank::findOrFail($request->destination_bank_id);

            // Basic validation
            if ($sourceBank->id === $destinationBank->id) {
                throw new \Exception('Source and destination accounts cannot be the same.');
            }

            // Create transfer record
            $transfer = new Transfer();
            $transfer->sender_id = $user->id;
            $transfer->receiver_id = $destinationBank->user_id;
            $transfer->sender_bank_id = $sourceBank->id;
            $transfer->receiver_bank_id = $destinationBank->id;
            $transfer->amount = $request->amount;
            $transfer->status = 'pending';
            $transfer->metadata = json_encode(['note' => $request->note]);
            $transfer->save();

            // Initiate Dwolla transfer
            $transferResult = $this->dwollaService->createTransfer(
                $sourceBank->dwolla_funding_source_id,
                $destinationBank->dwolla_funding_source_id,
                (float) $request->amount,
                $request->note ?? 'Transfer'
            );

            // Update transfer with Dwolla ID if successful
            $transfer->update([
                'status' => $transferResult['status'] ?? 'pending',
                'dwolla_transfer_id' => $transferResult['id'] ?? null,
            ]);

            // Update balances using the existing balance_available column
            $sourceBank->decrement('balance_available', $request->amount);
            $destinationBank->increment('balance_available', $request->amount);

            // If the transfer is already completed in Dwolla, update the status
            if (isset($transferResult['status']) && $transferResult['status'] === 'completed') {
                $transfer->update(['status' => 'completed']);
            }

            // Log the balance updates
            \Log::info('Balances updated after transfer', [
                'transfer_id' => $transfer->id,
                'source_bank_id' => $sourceBank->id,
                'source_new_balance' => $sourceBank->fresh()->balance_available,
                'destination_bank_id' => $destinationBank->id,
                'destination_new_balance' => $destinationBank->fresh()->balance_available,
            ]);

            DB::commit();

            return redirect()->route('transfers.index')
                ->with('success', 'Transfer initiated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transfer failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'error' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Transfer failed: ' . $e->getMessage())
                         ->withInput();
        }
    }
}