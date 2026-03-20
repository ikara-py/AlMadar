<?php

return [
    'overdraft_limit'         => env('OVERDRAFT_LIMIT', 5000),
    'monthly_fee'             => env('MONTHLY_FEE', 50),
    'savings_interest_rate'   => env('SAVINGS_INTEREST_RATE', 3.5),
    'minor_interest_rate'     => env('MINOR_INTEREST_RATE', 2.0),
    'daily_transfer_limit'    => env('DAILY_TRANSFER_LIMIT', 10000),
    'max_savings_withdrawals' => env('MAX_SAVINGS_WITHDRAWALS', 3),
    'max_minor_withdrawals'   => env('MAX_MINOR_WITHDRAWALS', 2),
];
