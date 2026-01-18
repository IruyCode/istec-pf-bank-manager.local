<?php

namespace App\Modules\BankManager\Services;

use App\Modules\BankManager\Models\FcmToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Google\Client as GoogleClient;

class PushNotificationService
{
    private ?string $accessToken = null;
    private string $fcmUrl = 'https://fcm.googleapis.com/v1/projects/iruycode-final/messages:send';
    private string $credentialsPath;

    public function __construct()
    {
        $this->credentialsPath = storage_path('app/firebase-credentials.json');
        $this->initializeAccessToken();
    }

    private function initializeAccessToken(): void
    {
        if (!file_exists($this->credentialsPath)) {
            Log::warning('Firebase credentials file not found', ['path' => $this->credentialsPath]);
            return;
        }

        try {
            $client = new GoogleClient();
            $client->setAuthConfig($this->credentialsPath);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            
            $this->accessToken = $client->fetchAccessTokenWithAssertion()['access_token'] ?? null;
            
            if (!$this->accessToken) {
                Log::error('Failed to obtain Firebase access token');
            }
        } catch (\Exception $e) {
            Log::error('Firebase authentication failed', ['error' => $e->getMessage()]);
        }
    }

    public function sendToUser(int $userId, array $notification): array
    {
        $tokens = FcmToken::getUserTokens($userId);

        if (empty($tokens)) {
            Log::info("No FCM tokens found for user {$userId}");
            return [
                'success' => false,
                'message' => 'No tokens registered',
                'sent' => 0,
                'failed' => 0,
            ];
        }

        return $this->sendToTokens($tokens, $notification);
    }

    public function sendToTokens(array $tokens, array $notification): array
    {
        $results = [
            'success' => true,
            'sent' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($tokens as $token) {
            try {
                $response = $this->sendToToken($token, $notification);
                
                if ($response['success']) {
                    $results['sent']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = [
                        'token' => substr($token, 0, 20) . '...',
                        'error' => $response['error'] ?? 'Unknown error',
                    ];
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'token' => substr($token, 0, 20) . '...',
                    'error' => $e->getMessage(),
                ];
                Log::error("FCM send failed: " . $e->getMessage());
            }
        }

        if ($results['failed'] > 0) {
            $results['success'] = false;
        }

        Log::info("FCM notification sent", $results);

        return $results;
    }

    private function sendToToken(string $token, array $notification): array
    {
        if (empty($this->accessToken)) {
            Log::warning('Firebase access token not available');
            return [
                'success' => false,
                'error' => 'Firebase not configured',
            ];
        }

        $payload = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $notification['title'] ?? 'Bank Manager',
                    'body' => $notification['message'] ?? '',
                ],
                'data' => array_merge(
                    $notification['data'] ?? [],
                    ['click_action' => $notification['link'] ?? '/']
                ),
                'webpush' => [
                    'notification' => [
                        'icon' => $notification['icon'] ?? '/icon.png',
                    ],
                    'fcm_options' => [
                        'link' => $notification['link'] ?? '/',
                    ],
                ],
            ],
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Content-Type' => 'application/json',
        ])->post($this->fcmUrl, $payload);

        if ($response->successful()) {
            return ['success' => true];
        }

        $error = $response->json();
        $errorMessage = $error['error']['message'] ?? 'Unknown error';
        
        if (str_contains($errorMessage, 'not a valid FCM registration token') || 
            str_contains($errorMessage, 'Requested entity was not found')) {
            FcmToken::where('token', $token)->delete();
            Log::info("Invalid FCM token removed", ['token' => substr($token, 0, 20) . '...']);
        }

        Log::error("FCM send failed", [
            'error' => $errorMessage,
            'status' => $response->status(),
        ]);

        return [
            'success' => false,
            'error' => $errorMessage,
        ];
    }
}
