<?php

declare(strict_types=1);

namespace App\Models\Goals;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Goal extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'goal_value',
        'deadline',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
