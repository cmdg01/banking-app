<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ChatService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    protected $chatService;

    /**
     * Create a new controller instance.
     *
     * @param ChatService $chatService
     * @return void
     */
    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
        // Remove the middleware call from here - it's already in the routes
    }

    /**
     * Display the chat interface.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('chat.index');
    }

    /**
     * Process a message sent by the user
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        try {
            $chatService = app(ChatService::class);
            $response = $chatService->generateResponse(Auth::user(), $request->message);
            
            return response()->json([
                'success' => true,
                'response' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('Chat error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Sorry, I could not process your request. Please try again later.'
            ], 500);
        }
    }
}