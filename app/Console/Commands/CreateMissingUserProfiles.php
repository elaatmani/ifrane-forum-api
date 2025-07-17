<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateMissingUserProfiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:create-missing-profiles {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create profiles for users who don\'t have them yet';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking for users without profiles...');

        // Find users without profiles
        $usersWithoutProfiles = User::whereDoesntHave('profile')->get();

        if ($usersWithoutProfiles->isEmpty()) {
            $this->info('✅ All users already have profiles!');
            return 0;
        }

        $count = $usersWithoutProfiles->count();
        $this->warn("Found {$count} users without profiles:");

        // Show users that will be affected
        $this->table(
            ['ID', 'Name', 'Email', 'Created At'],
            $usersWithoutProfiles->map(function ($user) {
                return [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->created_at->format('Y-m-d H:i:s')
                ];
            })->toArray()
        );

        // Dry run mode
        if ($this->option('dry-run')) {
            $this->info('🔍 DRY RUN: No changes were made. Remove --dry-run to create profiles.');
            return 0;
        }

        // Confirm before proceeding
        if (!$this->confirm("Create profiles for these {$count} users?")) {
            $this->info('❌ Operation cancelled.');
            return 0;
        }

        // Create profiles with progress bar
        $this->info('🚀 Creating profiles...');
        $progressBar = $this->output->createProgressBar($count);
        $progressBar->start();

        $created = 0;
        $failed = 0;

        foreach ($usersWithoutProfiles as $user) {
            try {
                $user->profile()->create();
                $created++;
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Failed to create profile for user {$user->id} ({$user->name}): " . $e->getMessage());
                $failed++;
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->info("✅ Successfully created {$created} profiles");
        if ($failed > 0) {
            $this->warn("⚠️  Failed to create {$failed} profiles");
        }

        return 0;
    }
}
