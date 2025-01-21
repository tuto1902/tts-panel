<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('twitch_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_id');
            $table->string('nickname');
            $table->string('name');
            $table->string('email');
            $table->string('avatar');
            $table->string('access_token');
            $table->string('refresh_token');
            $table->string('status')->default('not_connected');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('twitch_accounts');
    }
};
