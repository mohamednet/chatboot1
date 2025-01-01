<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\IncomingMessage;
use App\Services\OpenAIService;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\Facades\Config;

class ProcessMessagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $facebookId;
    public $tries = 3; // Add retries
    public $timeout = 120; // Add timeout

    public function __construct($facebookId)
    {
        $this->facebookId = $facebookId;
    }

    public function handle(OpenAIService $openAIService)
    {
        \Log::info('Processing messages for user:', ['facebook_id' => $this->facebookId]);
        
        // Get all unprocessed messages for this user
        $messages = IncomingMessage::getPendingMessages($this->facebookId);

        if ($messages->isEmpty()) {
            \Log::info('No pending messages found');
            return;
        }

        try {
            // Update status to processing
            $messages->each(function ($message) {
                $message->update(['status' => 'processing']);
            });

            // Combine all messages
            $combinedMessage = $messages->pluck('message')->implode(' ');
            \Log::info('Combined message:', ['message' => $combinedMessage]);
            
            // Get response from OpenAI
            $response = $openAIService->ask($combinedMessage, $this->facebookId);
            \Log::info('OpenAI response:', ['response' => $response]);

            // Send response back to user
            $this->sendResponse($response);

            // Mark messages as processed
            $messages->each(function ($message) {
                $message->update(['status' => 'processed']);
            });

            \Log::info('Successfully processed messages');

        } catch (\Exception $e) {
            // Log error and revert status
            \Log::error('Error processing messages:', [
                'facebook_id' => $this->facebookId,
                'error' => $e->getMessage()
            ]);

            $messages->each(function ($message) {
                $message->update(['status' => 'unprocessed']);
            });

            throw $e;
        }
    }

    protected function sendResponse($message)
    {
        $client = new HttpClient();
        $accessToken = config('services.facebook.page_access_token');
        
        \Log::info('Sending response:', [
            'facebook_id' => $this->facebookId,
            'message' => $message
        ]);
        
        try {
            $response = $client->post('https://graph.facebook.com/v12.0/me/messages', [
                'query' => [
                    'access_token' => $accessToken
                ],
                'json' => [
                    'recipient' => ['id' => $this->facebookId],
                    'message' => ['text' => $message]
                ]
            ]);

            $result = json_decode($response->getBody(), true);
            \Log::info('Facebook API response:', ['response' => $result]);
            return $result;
            
        } catch (\Exception $e) {
            \Log::error('Error sending message:', [
                'facebook_id' => $this->facebookId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
