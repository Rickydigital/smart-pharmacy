<?php

namespace App\Console\Commands;

use App\Services\SmartControl\SmartControlClient;
use Illuminate\Console\Command;

class SmartControlHeartbeat extends Command
{
    protected $signature = 'smart-control:heartbeat';

    protected $description = 'Send Smart Pharmacy heartbeat to central control';

    public function handle(SmartControlClient $client): int
    {
        if (! config('smartcontrol.enabled')) {
            $this->warn('Smart control is disabled.');
            return self::SUCCESS;
        }

        $state = $client->heartbeat();

        $this->info('Heartbeat sent.');
        $this->line('Allowed: ' . ($state->allowed ? 'Yes' : 'No'));
        $this->line('Message: ' . $state->message);

        return self::SUCCESS;
    }
}