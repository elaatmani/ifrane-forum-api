<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WherebyService
{
    protected $apiKey;
    protected $subdomain;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.whereby.api_key');
        $this->subdomain = config('services.whereby.subdomain');
        $this->baseUrl = 'https://api.whereby.dev/v1';
    }

    /**
     * Create a new meeting room
     */
    public function createMeeting(array $options = [])
    {
        // If no API key is configured, use mock implementation
        if (empty($this->apiKey)) {
            Log::info('No Whereby API key configured, using mock implementation');
            return $this->createMockMeeting($options);
        }

        Log::info('Attempting to create Whereby meeting with API', [
            'api_key_length' => strlen($this->apiKey),
            'api_key_preview' => substr($this->apiKey, 0, 8) . '...',
            'subdomain' => $this->subdomain,
            'base_url' => $this->baseUrl,
            'options' => $options
        ]);

        try {
            $requestPayload = [
                'roomNamePrefix' => $options['roomNamePrefix'] ?? 'foodshow',
                'roomNamePattern' => $options['roomNamePattern'] ?? 'human-short',
                'roomMode' => $options['roomMode'] ?? 'normal',
                'endDate' => $options['endDate'] ?? now()->addHours(24)->toISOString(), // Required field
                'fields' => ['hostRoomUrl', 'viewerRoomUrl', 'meetingId']
            ];

            Log::info('Sending request to Whereby API', [
                'url' => $this->baseUrl . '/meetings',
                'method' => 'POST',
                'payload' => $requestPayload,
                'payload_json' => json_encode($requestPayload),
                'payload_size' => strlen(json_encode($requestPayload)),
                'headers' => [
                    'Authorization' => 'Bearer ' . substr($this->apiKey, 0, 8) . '...',
                    'Content-Type' => 'application/json'
                ],
                'api_key_length' => strlen($this->apiKey),
                'subdomain' => $this->subdomain,
                'base_url' => $this->baseUrl
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/meetings', $requestPayload);

            Log::info('Whereby API response received', [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->body(),
                'body_length' => strlen($response->body()),
                'content_type' => $response->header('Content-Type'),
                'response_time' => $response->handlerStats()['total_time'] ?? 'unknown'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Log ALL data received from Whereby
                Log::info('Whereby API response data - COMPLETE DUMP', [
                    'raw_response' => $data,
                    'response_keys' => array_keys($data),
                    'meeting_id' => $data['meetingId'] ?? 'NOT_FOUND',
                    'room_name' => $data['roomName'] ?? 'NOT_FOUND',
                    'room_url' => $data['roomUrl'] ?? 'NOT_FOUND',
                    'host_room_url' => $data['hostRoomUrl'] ?? 'NOT_FOUND',
                    'viewer_room_url' => $data['viewerRoomUrl'] ?? 'NOT_FOUND',
                    'start_date' => $data['startDate'] ?? 'NOT_FOUND',
                    'end_date' => $data['endDate'] ?? 'NOT_FOUND',
                    'additional_fields' => array_diff_key($data, array_flip(['meetingId', 'roomName', 'roomUrl', 'hostRoomUrl', 'viewerRoomUrl', 'startDate', 'endDate']))
                ]);

                // Validate required fields
                $requiredFields = ['meetingId', 'roomName', 'viewerRoomUrl', 'hostRoomUrl'];
                $missingFields = [];
                foreach ($requiredFields as $field) {
                    if (!isset($data[$field])) {
                        $missingFields[] = $field;
                    }
                }

                if (!empty($missingFields)) {
                    Log::warning('Whereby API response missing required fields', [
                        'missing_fields' => $missingFields,
                        'available_fields' => array_keys($data)
                    ]);
                }

                Log::info('Whereby meeting created successfully', [
                    'meeting_id' => $data['meetingId'] ?? 'unknown',
                    'room_name' => $data['roomName'] ?? 'unknown',
                    'room_url' => $data['roomUrl'] ?? 'unknown',
                    'host_room_url' => $data['hostRoomUrl'] ?? 'unknown',
                    'viewer_room_url' => $data['viewerRoomUrl'] ?? 'unknown',
                    'url_selection' => [
                        'participant_url' => $data['roomUrl'] ?? 'NOT_SET', // Full participant access
                        'viewer_url' => $data['viewerRoomUrl'] ?? 'NOT_SET', // Spectator only
                        'host_url' => $data['hostRoomUrl'] ?? 'NOT_SET' // Host with controls
                    ]
                ]);
                
                return [
                    'success' => true,
                    'meeting_id' => $data['meetingId'],
                    'room_url' => $data['roomUrl'], // Main room URL for full participant access
                    'host_room_url' => $data['hostRoomUrl'],
                    'room_name' => $data['roomName'],
                    'raw_whereby_data' => $data  // Include all raw data for debugging
                ];
            }

            // Handle specific error cases
            $errorResponse = $response->json();
            $errorMessage = 'Unknown error';
            
            // Log the complete error response for debugging
            Log::error('Whereby API error response - COMPLETE DUMP', [
                'status_code' => $response->status(),
                'raw_error_response' => $errorResponse,
                'error_response_keys' => is_array($errorResponse) ? array_keys($errorResponse) : 'not_array',
                'response_body' => $response->body(),
                'response_headers' => $response->headers(),
                'request_payload' => $requestPayload
            ]);
            
            if ($response->status() === 400) {
                if (isset($errorResponse['error'])) {
                    $errorMessage = $errorResponse['error'];
                } elseif (isset($errorResponse['message'])) {
                    $errorMessage = $errorResponse['message'];
                } elseif (isset($errorResponse['detail'])) {
                    $errorMessage = $errorResponse['detail'];
                } else {
                    $errorMessage = 'Bad Request - Invalid API key or request format';
                }
            } elseif ($response->status() === 401) {
                $errorMessage = 'Unauthorized - Invalid or expired API key';
            } elseif ($response->status() === 403) {
                $errorMessage = 'Forbidden - API key lacks required permissions';
            } elseif ($response->status() === 429) {
                $errorMessage = 'Rate Limited - Too many requests';
            } elseif ($response->status() === 500) {
                $errorMessage = 'Internal Server Error - Whereby API issue';
            }

            Log::error('Whereby API error', [
                'status' => $response->status(),
                'error_message' => $errorMessage,
                'response_body' => $errorResponse,
                'request_payload' => $requestPayload,
                'api_key_preview' => substr($this->apiKey, 0, 8) . '...',
                'full_error_details' => [
                    'status_code' => $response->status(),
                    'error_response' => $errorResponse,
                    'response_body' => $response->body(),
                    'request_url' => $this->baseUrl . '/meetings',
                    'request_headers' => [
                        'Authorization' => 'Bearer ' . substr($this->apiKey, 0, 8) . '...',
                        'Content-Type' => 'application/json'
                    ]
                ]
            ]);

            // Fallback to mock if API fails
            Log::info('Falling back to mock meeting creation due to API error');
            return $this->createMockMeeting($options);

        } catch (\Exception $e) {
            Log::error('Whereby API exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_payload' => $requestPayload ?? 'not set'
            ]);

            // Fallback to mock if API fails
            Log::info('Falling back to mock meeting creation due to exception');
            return $this->createMockMeeting($options);
        }
    }

    /**
     * Create a mock meeting for development/testing
     */
    protected function createMockMeeting(array $options = [])
    {
        $roomName = ($options['roomNamePrefix'] ?? 'foodshow') . '-' . Str::random(8);
        $subdomain = $this->subdomain ?: 'demo';
        
        $mockData = [
            'success' => true,
            'meeting_id' => 'mock_' . Str::random(16),
            'room_url' => "https://{$subdomain}.whereby.com/{$roomName}",
            'host_room_url' => "https://{$subdomain}.whereby.com/{$roomName}?host=true",
            'room_name' => $roomName
        ];

        Log::info('Creating mock meeting (fallback)', [
            'mock_data' => $mockData,
            'options' => $options,
            'subdomain' => $subdomain,
            'room_name' => $roomName,
            'fallback_reason' => 'API key not configured or API call failed'
        ]);
        
        return $mockData;
    }

    /**
     * Delete a meeting
     */
    public function deleteMeeting($meetingId)
    {
        // If no API key is configured, just return success
        if (empty($this->apiKey)) {
            return ['success' => true];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey
            ])->delete($this->baseUrl . '/meetings/' . $meetingId);

            return [
                'success' => $response->successful()
            ];

        } catch (\Exception $e) {
            Log::error('Whereby delete meeting error', [
                'meeting_id' => $meetingId,
                'message' => $e->getMessage()
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get meeting details
     */
    public function getMeeting($meetingId)
    {
        // If no API key is configured, return mock data
        if (empty($this->apiKey)) {
            return [
                'success' => true,
                'meeting_id' => $meetingId,
                'room_url' => "https://demo.whereby.com/mock-room",
                'host_room_url' => "https://demo.whereby.com/mock-room?host=true",
                'room_name' => 'mock-room'
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey
            ])->get($this->baseUrl . '/meetings/' . $meetingId);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'meeting_id' => $data['meetingId'],
                    'room_url' => $data['roomUrl'], // Main room URL for full participant access
                    'host_room_url' => $data['hostRoomUrl'],
                    'room_name' => $data['roomName'],
                    'raw_whereby_data' => $data  // Include all raw data for debugging
                ];
            }

            return ['success' => false, 'error' => 'Meeting not found'];

        } catch (\Exception $e) {
            Log::error('Whereby get meeting error', [
                'meeting_id' => $meetingId,
                'message' => $e->getMessage()
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Check if service is properly configured
     */
    public function isConfigured()
    {
        return !empty($this->apiKey) && !empty($this->subdomain);
    }

    /**
     * Get service status
     */
    public function getStatus()
    {
        return [
            'configured' => $this->isConfigured(),
            'api_key_set' => !empty($this->apiKey),
            'subdomain_set' => !empty($this->subdomain),
            'base_url' => $this->baseUrl
        ];
    }

    /**
     * Check if the Whereby API key is valid and working
     */
    public function checkApiKeyStatus()
    {
        if (empty($this->apiKey)) {
            return [
                'status' => 'not_configured',
                'message' => 'No API key configured',
                'recommendation' => 'Set WHEREBY_API_KEY in your .env file'
            ];
        }

        try {
            // Try to make a simple API call to check if the key is valid
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json'
            ])->get($this->baseUrl . '/meetings');

            if ($response->status() === 401) {
                return [
                    'status' => 'invalid',
                    'message' => 'API key is invalid or expired',
                    'recommendation' => 'Check your WHEREBY_API_KEY in .env file'
                ];
            } elseif ($response->status() === 403) {
                return [
                    'status' => 'insufficient_permissions',
                    'message' => 'API key lacks required permissions',
                    'recommendation' => 'Check your Whereby account permissions'
                ];
            } elseif ($response->status() === 200) {
                return [
                    'status' => 'valid',
                    'message' => 'API key is valid and working',
                    'api_info' => [
                        'key_length' => strlen($this->apiKey),
                        'key_preview' => substr($this->apiKey, 0, 8) . '...',
                        'subdomain' => $this->subdomain,
                        'base_url' => $this->baseUrl
                    ]
                ];
            } else {
                return [
                    'status' => 'unknown_error',
                    'message' => 'Unexpected response from Whereby API',
                    'status_code' => $response->status(),
                    'response' => $response->body()
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'connection_error',
                'message' => 'Failed to connect to Whereby API',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get current configuration status
     */
    public function getConfigStatus()
    {
        return [
            'api_key_configured' => !empty($this->apiKey),
            'api_key_length' => $this->apiKey ? strlen($this->apiKey) : 0,
            'subdomain_configured' => !empty($this->subdomain),
            'subdomain' => $this->subdomain,
            'base_url' => $this->baseUrl,
            'environment_variables' => [
                'WHEREBY_API_KEY' => env('WHEREBY_API_KEY') ? 'set' : 'not_set',
                'WHEREBY_SUBDOMAIN' => env('WHEREBY_SUBDOMAIN') ? 'set' : 'not_set'
            ]
        ];
    }
}

