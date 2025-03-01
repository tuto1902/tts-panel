<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

final class TwitchEvent extends Model
{
    /** @use HasFactory<\Database\Factories\TwitchEventFactory> */
    use HasFactory, Prunable, SoftDeletes;

    protected $fillable = [
        'message',
        'file_path',
        'nickname',
        'avatar',
        'color',
    ];

    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return self::where('created_at', '<=', now()->subDay());
    }

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

    /**
     * Prepare the model for pruning.
     */
    protected function pruning(): void
    {
        Storage::disk('public')->delete($this->file_path);
    }
}
