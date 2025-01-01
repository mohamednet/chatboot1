<?php


namespace App\Services;

use OpenAI;

class OpenAIService
{
    protected $client;

    public function __construct()
    {
        // Initialize OpenAI client using the API key from the environment variables
        $this->client = OpenAI::client(env('OPENAI_API_KEY'));
    }

    public function ask($prompt, $userId = null)
    {
        // Get recent conversation history if userId is provided
        $messages = [
            [
                'role' => 'system', 'content' => 
                "You're Alex, an IPTV support rep. Keep responses short and natural. No excessive politeness or emojis. Speak casually but professionally.

                Key Actions:
                1. For new users: Offer trial
                2. For device setup:
                   - Android,firestick: Direct to Downloader app (code: 439873)
                   - Smart TVs/iOS: Check for existing players
                   - Fallback: IBOPlayer (code: 597218)

                Pricing (1 Device):
                - Month: $15
                - 3 Months: $35
                - 6 Months: $49
                - Year: $79

                Multi-device discounts:
                - 2nd: 35% off
                - 3rd: 45% off
                - 4th: 55% off
                - 5th: 60% off
                - 6th: 65% off

                Payment: PayPal, Zelle, CashApp, Credit Cards"
            ]
        ];

        if ($userId) {
            $history = \App\Models\ChatHistory::getRecentHistory($userId);
            foreach ($history as $chat) {
                $messages[] = ['role' => 'user', 'content' => $chat->message];
                $messages[] = ['role' => 'assistant', 'content' => $chat->response];
            }
        }

        // Add the current message
        $messages[] = ['role' => 'user', 'content' => $prompt];

        $response = $this->client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'temperature' => 0.01,
            'messages' => $messages
        ]);

        $responseText = $response->choices[0]->message->content;

        // Save the conversation if userId is provided
        if ($userId) {
            \App\Models\ChatHistory::create([
                'user_id' => $userId,
                'message' => $prompt,
                'response' => $responseText
            ]);
        }

        return $responseText;
    }

    public function analyzeImage($imageUrl)
    {
        try {
            $response = $this->client->chat()->create([
                'model' => 'gpt-4-vision',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are Alex, a helpful IPTV support agent. Analyze the error screenshot and provide clear, step-by-step solutions. Focus on common IPTV app installation issues and their fixes. If you can't clearly see the error or if it's not IPTV-related, politely explain that. Use a friendly, helpful tone."
                    ],
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'image_url',
                                'image_url' => ['url' => $imageUrl]
                            ],
                            [
                                'type' => 'text',
                                'text' => "I'm having this error while installing/using the IPTV app. Can you help me fix it?"
                            ]
                        ]
                    ]
                ],
                'max_tokens' => 500
            ]);

            return $response->choices[0]->message->content;
        } catch (\Exception $e) {
            \Log::error('OpenAI Vision API Error:', [
                'error' => $e->getMessage(),
                'image_url' => $imageUrl
            ]);
            return "I apologize, but I'm having trouble analyzing the image at the moment. Could you please describe the error you're seeing, or try sending the image again? I'll be happy to help you troubleshoot the issue.";
        }
    }
}
