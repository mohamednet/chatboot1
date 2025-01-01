<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OpenAIService;
use App\Services\DeviceService;
use App\Models\Client;
use App\Models\IncomingMessage;
use GuzzleHttp\Client as HttpClient;
use App\Jobs\ProcessMessagesJob;

class MessengerController extends Controller
{
    protected $openAIService;
    protected $deviceService;
    protected $pageAccessToken;

    public function __construct(OpenAIService $openAIService, DeviceService $deviceService)
    {
        $this->openAIService = $openAIService;
        $this->deviceService = $deviceService;
        $this->pageAccessToken = env('FACEBOOK_PAGE_ACCESS_TOKEN');
    }

    protected function getClientInfo($facebookId)
    {
        try {
            $client = new HttpClient();
            $response = $client->get("https://graph.facebook.com/v12.0/{$facebookId}", [
                'query' => [
                    'fields' => 'name,profile_pic,locale',
                    'access_token' => $this->pageAccessToken
                ]
            ]);

            $userData = json_decode($response->getBody(), true);
            \Log::info('Facebook user data:', ['data' => $userData]);

            return $userData;
        } catch (\Exception $e) {
            \Log::error('Error fetching Facebook user data:', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function handleMessage(Request $request)
    {
        // Log the entire webhook payload
        \Log::info('Webhook Payload:', ['payload' => $request->all()]);

        // Handling the message here
        $input = $request->all();
        $senderId = $input['entry'][0]['messaging'][0]['sender']['id'];
        
        // Get or create client record
        $client = Client::firstOrNew(['facebook_id' => $senderId]);
        
        if (!$client->exists || !$client->name) {
            // Fetch user info from Facebook if we don't have it
            if ($userData = $this->getClientInfo($senderId)) {
                $client->name = $userData['name'] ?? null;
                $client->profile_pic = $userData['profile_pic'] ?? null;
                $client->locale = $userData['locale'] ?? null;
            }
        }
        
        $client->last_interaction = now();
        $client->save();

        // Store the message
        if (isset($input['entry'][0]['messaging'][0]['message'])) {
            $message = $input['entry'][0]['messaging'][0]['message'];
            
            // For text messages
            if (isset($message['text'])) {
                IncomingMessage::create([
                    'facebook_id' => $senderId,
                    'message' => $message['text'],
                    'status' => 'unprocessed'
                ]);
            }
            // For image messages
            elseif (isset($message['attachments'])) {
                $attachment = $message['attachments'][0];
                if ($attachment['type'] === 'image') {
                    IncomingMessage::create([
                        'facebook_id' => $senderId,
                        'message' => 'IMAGE: ' . $attachment['payload']['url'],
                        'status' => 'unprocessed'
                    ]);
                }
            }

            // Dispatch job with 10 second delay
            ProcessMessagesJob::dispatch($senderId)->delay(now()->addSeconds(10));
        }

        // Return 200 OK immediately
        return response()->json(['status' => 'received']);
    }

    public function verifyWebhook(Request $request)
    {
        $verifyToken = env('FACEBOOK_VERIFY_TOKEN');
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode && $token && $mode === 'subscribe' && $token === $verifyToken) {
            return response($challenge, 200);
        }

        return response('Forbidden', 403);
    }
}
