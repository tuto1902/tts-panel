<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class TwitchEvent extends Model
{
    /** @use HasFactory<\Database\Factories\TwitchEventFactory> */
    use HasFactory;

    protected $fillable = [
        'message',
        'file_path',
        'nickname',
        'avatar',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'played_at' => 'datetime',
        ];
    }
}
