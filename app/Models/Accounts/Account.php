<?php

declare(strict_types=1);

namespace App\Models\Accounts;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Account extends Model
{

    protected $fillable = [
        'user_id',
        'account_name',
        'opening_balance',
        'balance',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
