<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OpenAIService;

class ChatController extends Controller
{
    protected $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    public function chat(Request $request)
    {
        $prompt = $request->input('prompt');

        // Ask OpenAI and get a response
        $response = $this->openAIService->ask($prompt);

        return response()->json([
            'message' => $response,
        ]);
    }
}
