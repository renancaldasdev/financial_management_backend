<?php

declare(strict_types=1);

namespace App\Models\Accounts;

use App\Models\Transactions\Transaction;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    const DECIMAL_TWO_PLACES = 'decimal:2';

    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'accounts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'account_type',
        'provider_name',
        'provider_branch_code',
        'provider_branch_name',
        'account_number_encrypted',
        'account_mask',
        'balance',
        'credit_limit',
        'interest_rate',
        'due_date',
        'currency',
        'status',
        'last_synced_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'balance' => self::DECIMAL_TWO_PLACES,
        'credit_limit' => self::DECIMAL_TWO_PLACES,
        'interest_rate' => self::DECIMAL_TWO_PLACES,
        'due_date' => 'date',
        'last_synced_at' => 'datetime',
    ];

    /**
     * Relationship: an Account belongs to a User.
     *
     * @return BelongsTo<User, Account>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

}
