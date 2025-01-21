<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TwitchAccount extends Model
{
    /** @use HasFactory<\Database\Factories\TwitchAccountFactory> */
    use HasFactory;

    protected $fillable = [
        'account_id',
        'nickname',
        'name',
        'email',
        'avatar',
        'access_token',
        'refresh_token',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
