<?php

return [
    'limits' => [
        'day_transfer' => env('WALLET_DAY_TRANSFER_LIMIT', 5000.00),
        'night_transfer' => env('WALLET_NIGHT_TRANSFER_LIMIT', 1000.00),
        'day_deposit' => env('WALLET_DAY_DEPOSIT_LIMIT', 5000.00),
        'night_deposit' => env('WALLET_NIGHT_DEPOSIT_LIMIT', 1000.00),
    ],
];
