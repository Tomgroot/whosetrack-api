<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Round;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->index();
            $table->foreignId('created_by')->index();
            $table->integer('currently_playing_track')->default(0);
            $table->enum('status', [
                Round::STATUS_JOINING,
                Round::STATUS_PICK_TRACK,
                Round::STATUS_GUESS_WHOSE,
                Round::STATUS_FINISHED
            ])->default(Round::STATUS_JOINING);
            $table->string('gamemode')->default('custom');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rounds');
    }
};
