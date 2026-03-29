<?php
namespace App\Console\Commands;

use App\Services\ScheduledTaskService;
use Illuminate\Console\Command;

class ChargeMonthlyFees extends Command
{
    protected $signature   = 'fees:charge-monthly';
    protected $description = 'Charge monthly maintenance fees to all active COURANT accounts';

    public function handle(ScheduledTaskService $service): void
    {
        $this->info('Charging monthly fees...');
        $service->chargeMonthlyFees();
        $this->info('Done.');
    }
}
