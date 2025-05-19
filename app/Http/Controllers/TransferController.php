<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransferRequest;
use App\Models\Bank;
use App\Models\Transfer;
use App\Models\User;
use App\Services\DirectDwollaService;
use Carbon\Carbon;
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

    /**
     * Show the form for initiating a new transfer.
     */
    public function create()
    {
        // Get all banks linked to Dwolla for the authenticated user
        $banks = auth()->user()->banks()
            ->whereNotNull('dwolla_funding_source_url')
            ->get();

        if ($banks->isEmpty()) {
            return redirect()->route('banks.index')
                ->with('error', 'You need to link at least one bank account to Dwolla before you can make transfers.');
        }

        return view('transfers.create', compact('banks'));
    }

    /**
     * Display a listing of the transfers (sent or received by the user).
     */
    public function index()
    {
        $user = auth()->user();

        $transfers = Transfer::with(['sender', 'receiver', 'senderBank', 'receiverBank'])
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            })
            ->latest()
            ->paginate(10);

        return view('transfers.index', compact('transfers'));
    }

    /**
     * Store a new transfer.
     */
    public function store(StoreTransferRequest $request)
    {
        try {
            // Begin transaction
            DB::beginTransaction();

            $user = auth()->user();
            $sourceBank = Bank::where('id', $request->source_bank_id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            // Determine destination bank based on transfer type
            if ($request->destination_type === 'own_account') {
                // Transfer to user's own account
                $destinationBank = Bank::where('id', $request->destination_bank_id)
                    ->where('user_id', $user->id)
                    ->firstOrFail();

                $receiverId = $user->id;

            } else {
                // Transfer to another user
                $recipient = User::where('email', $request->recipient_email)->first();

                if (!$recipient) {
                    throw new \Exception('Recipient not found. They must have an account in the system.');
                }

                if (empty($recipient->dwolla_customer_url)) {
                    throw new \Exception('Recipient has not completed their Dwolla verification.');
                }

                // Get recipient's primary bank account
                $destinationBank = $recipient->banks()
                    ->whereNotNull('dwolla_funding_source_url')
                    ->first(); // Assuming the first one is the primary or default

                if (!$destinationBank) {
                    throw new \Exception('Recipient does not have a bank account linked to Dwolla.');
                }

                $receiverId = $recipient->id;
            }

            // Ensure source and destination are different
            if ($sourceBank->id === $destinationBank->id) {
                throw new \Exception('Source and destination accounts cannot be the same.');
            }

            // Ensure funding source URLs are present
            if (empty($sourceBank->dwolla_funding_source_url)) {
                throw new \Exception('Source bank account is not linked to Dwolla correctly.');
            }
            if (empty($destinationBank->dwolla_funding_source_url)) {
                throw new \Exception('Destination bank account is not linked to Dwolla correctly.');
            }

            // Initiate the transfer with Dwolla using DirectDwollaService
            $transferResult = $this->dwollaService->createTransfer(
                $sourceBank->dwolla_funding_source_url,
                $destinationBank->dwolla_funding_source_url,
                $request->amount,
                'USD',
                ['note' => $request->note ?? 'Transfer between accounts']
            );

            // Record the transfer in our database
            $transfer = new Transfer([
                'sender_id' => $user->id,
                'sender_bank_id' => $sourceBank->id,
                'receiver_id' => $receiverId,
                'receiver_bank_id' => $destinationBank->id,
                'amount' => $request->amount,
                'status' => $transferResult['status'] ?? 'pending',
                'dwolla_transfer_id' => $transferResult['id'] ?? null,
                'dwolla_transfer_url' => $transferResult['url'] ?? null,
                'metadata' => json_encode([
                    'note' => $request->note,
                    'transfer_type' => $request->destination_type,
                    'raw_response' => $transferResult,
                ]),
            ]);

            $transfer->save();

            // Commit the transaction
            DB::commit();

            return redirect()->route('transfers.index')
                ->with('success', 'Transfer initiated successfully. Status: ' . ($transferResult['status'] ?? 'pending') . '. It may take 1-3 business days to complete.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Transfer failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'request' => $request->except(['note']),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Transfer failed: ' . $e->getMessage());
        }
    }
}