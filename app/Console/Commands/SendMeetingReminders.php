<?php

namespace App\Console\Commands;

use App\Models\Meeting;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendMeetingReminders extends Command
{
    protected $signature = 'meetings:send-reminders {--window=15 : Minutes before start to remind} {--dry-run : Show targets without sending}';

    protected $description = 'Send reminder emails for upcoming meetings while avoiding duplicates';

    public function __construct(private NotificationService $notificationService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $window = (int) $this->option('window');
        $dryRun = (bool) $this->option('dry-run');

        $start = now()->addMinutes($window)->subMinute();
        $end = now()->addMinutes($window)->addMinute();

        $meetings = Meeting::accepted()
            ->whereBetween('scheduled_at', [$start, $end])
            ->with(['acceptedParticipants.user', 'organizer'])
            ->get();

        $totalSent = 0;

        foreach ($meetings as $meeting) {
            foreach ($meeting->acceptedParticipants as $participant) {
                if ($participant->reminder_sent_at) {
                    continue;
                }

                $user = $participant->user;
                if (!$user) {
                    continue;
                }

                if ($dryRun) {
                    $this->line("Would send reminder to {$user->email} for meeting {$meeting->id}");
                } else {
                    DB::transaction(function () use ($participant, $user, $meeting) {
                        $joinUrl = url("/meetings/{$meeting->id}");
                        $this->notificationService->sendMeetingReminderEmail($user, $meeting, $joinUrl);
                        $participant->update([
                            'reminder_sent_at' => now(),
                            'reminder_offset_minutes' => $this->option('window'),
                        ]);
                    });
                    $totalSent++;
                }
            }
        }

        $this->info($dryRun ? "Dry run complete. {$totalSent} queued messages (simulated)." : "Queued {$totalSent} reminder emails.");

        return 0;
    }
}

