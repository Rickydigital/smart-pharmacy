<?php

namespace App\Console\Commands;

use App\Services\SmartControl\SmartControlClient;
use Illuminate\Console\Command;

class SmartControlSync extends Command
{
    protected $signature = 'smart-control:sync';

    protected $description = 'Sync Smart Pharmacy license status with central control';

    public function handle(SmartControlClient $client): int
    {
        if (! config('smartcontrol.enabled')) {
            $this->warn('Smart control is disabled.');
            return self::SUCCESS;
        }

        $state = $client->statusCheck();

        $this->info('Smart control synced.');
        $this->line('Allowed: ' . ($state->allowed ? 'Yes' : 'No'));
        $this->line('Force logout: ' . ($state->force_logout ? 'Yes' : 'No'));
        $this->line('Message: ' . $state->message);

        return $state->allowed ? self::SUCCESS : self::FAILURE;
    }
}