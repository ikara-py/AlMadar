<?php
namespace Database\Seeders;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $guardian = User::where('email', 'guardian@example.com')->firstOrFail();
        $minor    = User::where('email', 'minor@example.com')->firstOrFail();
        $client   = User::where('email', 'client@example.com')->firstOrFail();

        $a1 = Account::create([
            'account_number' => 'MA0000001',
            'type' => 'COURANT',
            'status' => 'ACTIVE',
            'balance' => 5000.00,
            'overdraft_limit' => 5000.00,
            'monthly_fee' => 50.00
        ]);
        $a1->users()->attach($guardian->id, ['role' => 'owner']);

        $a2 = Account::create([
            'account_number' => 'MA0000002',
            'type' => 'EPARGNE',
            'status' => 'ACTIVE',
            'balance' => 20000.00,
            'interest_rate' => 3.5
        ]);
        $a2->users()->attach($guardian->id, ['role' => 'owner']);

        $a3 = Account::create([
            'account_number' => 'MA0000003',
            'type' => 'MINEUR',
            'status' => 'ACTIVE',
            'balance' => 1000.00,
            'interest_rate' => 2.0,
            'guardian_id' => $guardian->id
        ]);
        $a3->users()->attach($minor->id, ['role' => 'owner']);

        $a4 = Account::create([
            'account_number' => 'MA0000004',
            'type' => 'COURANT',
            'status' => 'ACTIVE',
            'balance' => 3000.00,
            'overdraft_limit' => 5000.00,
            'monthly_fee' => 50.00
        ]);
        $a4->users()->attach($client->id, ['role' => 'owner']);

        $a5 = Account::create([
            'account_number' => 'MA0000005',
            'type' => 'COURANT',
            'status' => 'BLOCKED',
            'balance' => 0.00,
            'overdraft_limit' => 5000.00,
            'monthly_fee' => 50.00,
            'blocked_reason' => 'Test blocked account'
        ]);
        $a5->users()->attach($client->id, ['role' => 'owner']);
    }
}
