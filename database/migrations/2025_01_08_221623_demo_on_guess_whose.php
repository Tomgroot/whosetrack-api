<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared(<<<EO
            DROP TRIGGER IF EXISTS round_to_guess_whose_when_tracks_submitted;
            CREATE TRIGGER demo_on_guess_whose
            AFTER UPDATE ON rounds
            FOR EACH ROW
            BEGIN
                IF NEW.status = 'guess_whose' AND OLD.status != 'guess_whose' AND (NEW.id = 1 OR NEW.id = 2) THEN
                    INSERT INTO guesses (user_id, track_id, guessed_user_id, ready)
                    SELECT ru.user_id, t.id, t.user_id, 1
                    FROM round_user ru
                    JOIN tracks t ON t.round_id = ru.round_id
                    WHERE ru.round_id = 2 AND ru.user_id IN (2, 3);
                END IF;
            END
        EO);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared(<<<EO
            DROP TRIGGER IF EXISTS round_to_guess_whose_when_tracks_submitted;
        EO);
    }
};
