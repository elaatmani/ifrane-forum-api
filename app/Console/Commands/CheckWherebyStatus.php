<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WherebyService;

class CheckWherebyStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whereby:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Whereby API configuration and status';

    /**
     * Execute the console command.
     */
    public function handle(WherebyService $wherebyService): int
    {
        $this->info('🔍 Checking Whereby API Configuration...');
        $this->newLine();

        // Check configuration status
        $configStatus = $wherebyService->getConfigStatus();
        
        $this->info('📋 Configuration Status:');
        $this->table(
            ['Setting', 'Status', 'Value'],
            [
                ['API Key', $configStatus['api_key_configured'] ? '✅ Configured' : '❌ Not Configured', $configStatus['api_key_length'] > 0 ? $configStatus['api_key_length'] . ' characters' : 'Not set'],
                ['Subdomain', $configStatus['subdomain_configured'] ? '✅ Configured' : '❌ Not Configured', $configStatus['subdomain'] ?: 'Not set'],
                ['Base URL', '✅ Set', $configStatus['base_url']],
            ]
        );

        $this->newLine();
        $this->info('🔑 Environment Variables:');
        $this->table(
            ['Variable', 'Status'],
            [
                ['WHEREBY_API_KEY', $configStatus['environment_variables']['WHEREBY_API_KEY'] === 'set' ? '✅ Set' : '❌ Not Set'],
                ['WHEREBY_SUBDOMAIN', $configStatus['environment_variables']['WHEREBY_SUBDOMAIN'] === 'set' ? '✅ Set' : '❌ Not Set'],
            ]
        );

        $this->newLine();
        $this->info('🌐 Testing API Connection...');

        // Check API key status
        $apiStatus = $wherebyService->checkApiKeyStatus();
        
        $this->info('📡 API Status: ' . $this->getStatusIcon($apiStatus['status']) . ' ' . ucfirst($apiStatus['status']));
        $this->info('💬 Message: ' . $apiStatus['message']);
        
        if (isset($apiStatus['recommendation'])) {
            $this->warn('💡 Recommendation: ' . $apiStatus['recommendation']);
        }

        if (isset($apiStatus['api_info'])) {
            $this->newLine();
            $this->info('🔧 API Information:');
            $this->table(
                ['Property', 'Value'],
                [
                    ['API Key Preview', $apiStatus['api_info']['key_preview']],
                    ['Subdomain', $apiStatus['api_info']['subdomain'] ?: 'Not set'],
                    ['Base URL', $apiStatus['api_info']['base_url']],
                ]
            );
        }

        $this->newLine();
        
        if ($apiStatus['status'] === 'valid') {
            $this->info('✅ Whereby API is working correctly!');
        } elseif ($apiStatus['status'] === 'not_configured') {
            $this->error('❌ Whereby API key is not configured. Video calls will use mock meetings.');
            $this->warn('💡 To fix this, add WHEREBY_API_KEY to your .env file');
        } else {
            $this->error('❌ Whereby API has issues. Video calls will fall back to mock meetings.');
        }

        $this->newLine();
        $this->newLine();
        
        // Check recent video calls for debugging
        $this->checkRecentVideoCalls();

        $this->newLine();
        $this->newLine();
        
        // Test Whereby API directly
        $this->testWherebyApi();

        $this->newLine();
        
        if ($apiStatus['status'] === 'valid') {
            return 0;
        } elseif ($apiStatus['status'] === 'not_configured') {
            return 1;
        } else {
            return 1;
        }
    }

    /**
     * Check recent video calls for debugging
     */
    public function checkRecentVideoCalls()
    {
        $this->info('🔍 Checking Recent Video Calls...');
        $this->newLine();

        try {
            $recentCalls = \App\Models\VideoCall::with(['room'])
                ->latest()
                ->take(10)
                ->get();

            if ($recentCalls->isEmpty()) {
                $this->warn('No video calls found in database');
                return;
            }

            $this->info('📞 Recent Video Calls:');
            $this->table(
                ['Call ID', 'Room ID', 'Status', 'Created', 'Conversation ID'],
                $recentCalls->map(function ($call) {
                    return [
                        $call->id,
                        $call->room_id,
                        $call->status,
                        $call->created_at->format('Y-m-d H:i:s'),
                        $call->conversation_id
                    ];
                })->toArray()
            );

            $this->newLine();
            $this->info('🏠 Recent Video Call Rooms:');
            $recentRooms = \App\Models\VideoCallRoom::latest()->take(5)->get();
            
            $this->table(
                ['Room ID', 'Meeting ID', 'Status', 'Created', 'Conversation ID'],
                $recentRooms->map(function ($room) {
                    return [
                        $room->id,
                        $room->whereby_meeting_id,
                        $room->status,
                        $room->created_at->format('Y-m-d H:i:s'),
                        $room->conversation_id
                    ];
                })->toArray()
            );

        } catch (\Exception $e) {
            $this->error('Failed to check video calls: ' . $e->getMessage());
        }
    }

    /**
     * Test Whereby API directly and show complete response
     */
    public function testWherebyApi()
    {
        $this->info('🧪 Testing Whereby API Directly...');
        $this->newLine();

        try {
            $wherebyService = app(\App\Services\WherebyService::class);
            
            $this->info('Creating test meeting...');
            $result = $wherebyService->createMeeting([
                'roomNamePrefix' => 'test',
                'roomNamePattern' => 'human-short',
                'endDate' => now()->addHours(1)->toISOString()
            ]);

            $this->info('Test meeting result:');
            $this->table(
                ['Key', 'Value'],
                collect($result)->map(function ($value, $key) {
                    if (is_array($value)) {
                        return [$key, json_encode($value)];
                    }
                    return [$key, (string) $value];
                })->toArray()
            );

            if (isset($result['raw_whereby_data'])) {
                $this->newLine();
                $this->info('Raw Whereby API Response:');
                $this->table(
                    ['Field', 'Value'],
                    collect($result['raw_whereby_data'])->map(function ($value, $key) {
                        return [$key, (string) $value];
                    })->toArray()
                );
            }

        } catch (\Exception $e) {
            $this->error('Failed to test Whereby API: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
        }
    }

    private function getStatusIcon(string $status): string
    {
        return match($status) {
            'valid' => '✅',
            'not_configured' => '❌',
            'invalid' => '🚫',
            'insufficient_permissions' => '🔒',
            'connection_error' => '🌐',
            'unknown_error' => '❓',
            default => '❓'
        };
    }
}
