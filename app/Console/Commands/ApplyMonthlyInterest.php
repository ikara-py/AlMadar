<?php
namespace App\Console\Commands;

use App\Services\ScheduledTaskService;
use Illuminate\Console\Command;

class ApplyMonthlyInterest extends Command
{
    protected $signature   = 'interest:apply-monthly';
    protected $description = 'Apply monthly interest to all active EPARGNE and MINEUR accounts';

    public function handle(ScheduledTaskService $service): void
    {
        $this->info('Applying monthly interest...');
        $service->applyMonthlyInterest();
        $this->info('Done.');
    }
}
