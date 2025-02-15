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
    use HasFactory, SoftDeletes, Prunable;

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

    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subDay());
    }

    /**
     * Prepare the model for pruning.
     */
    protected function pruning(): void
    {
        // dd($this->file_path);
        Storage::disk('public')->delete($this->file_path);
    }
}
