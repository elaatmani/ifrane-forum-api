<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\VideoCallService;

class CleanupExpiredVideoCalls extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'video-calls:cleanup';

    /**
     * The console command description.
     */
    protected $description = 'Clean up expired video calls and rooms';

    /**
     * Execute the console command.
     */
    public function handle(VideoCallService $videoCallService): int
    {
        $this->info('Starting cleanup of expired video calls and rooms...');

        try {
            // Clean up expired calls
            $expiredCallsCount = $videoCallService->expireExpiredCalls();
            $this->info("Expired {$expiredCallsCount} calls");

            // Clean up expired rooms
            $expiredRoomsCount = $videoCallService->cleanupExpiredRooms();
            $this->info("Cleaned up {$expiredRoomsCount} expired rooms");

            $this->info('Cleanup completed successfully!');
            return 0;

        } catch (\Exception $e) {
            $this->error('Cleanup failed: ' . $e->getMessage());
            return 1;
        }
    }
}

