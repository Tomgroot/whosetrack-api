<?php

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
        Schema::create('round_user', function (Blueprint $table) {
            $table->foreignId('user_id')->index();
            $table->foreignId('round_id')->index();
            $table->timestamps();
            $table->unique(['user_id', 'round_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('round_user');
    }
};
