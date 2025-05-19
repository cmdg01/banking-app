<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * Display the chat interface.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('chat.index');
    }
}