<?php

namespace App\Http\Controllers;

use App\Services\ChatbotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatbotController extends Controller
{
    protected $chatbotService;

    public function __construct(ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }

    /**
     * Handle chatbot message
     */
    public function message(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $user = Auth::user();
        $message = $request->input('message');

        try {
            $response = $this->chatbotService->processMessage($message, $user);
            
            return response()->json([
                'success' => true,
                'response' => $response['response'],
                'type' => $response['type'] ?? 'info'
            ]);
        } catch (\Exception $e) {
            \Log::error('Chatbot Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'message' => $message,
                'user_id' => $user->id
            ]);
            
            return response()->json([
                'success' => false,
                'response' => 'Sorry, I encountered an error. Please try again or contact support.',
                'type' => 'error'
            ], 500);
        }
    }
}

