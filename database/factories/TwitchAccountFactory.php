<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TwitchAccount>
 */
final class TwitchAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Str::random(),
            'nickname' => fake()->userName(),
            'name' => fake()->name(),
            'email' => fake()->email(),
            'avatar' => 'avatar.png',
            'access_token' => Str::random(),
            'refresh_token' => Str::random(),
            // 'status' => 'not_subscribed'
        ];
    }
}
