<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WalletSetting extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'day_transfer_limit',
        'night_transfer_limit',
        'day_deposit_limit',
        'night_deposit_limit',
    ];

    // Ensure that values are returned as decimals rathar than string
    protected function casts(): array
    {
        return [
            'day_transfer_limit' => 'decimal:2',
            'night_transfer_limit' => 'decimal:2',
            'day_deposit_limit' => 'decimal:2',
            'night_deposit_limit' => 'decimal:2',
        ];
    }

    // Get the use that owns the wallet setting
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
