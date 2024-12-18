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
            CREATE TRIGGER demo_on_finished
            AFTER UPDATE ON rounds
            FOR EACH ROW
            BEGIN
                DECLARE other_demo_round INT;

                IF NEW.status = 'finished' AND OLD.status != 'finished' AND (NEW.id = 1 OR NEW.id = 2) THEN
                    IF NEW.id = 1 THEN
                        SET other_demo_round = 2;
                    ELSE
                        SET other_demo_round = 1;
                    END IF;

                    DELETE FROM guesses
                    WHERE track_id IN (
                        SELECT t.id
                        FROM tracks t
                        WHERE user_id = 1 AND round_id = other_demo_round
                    );

                    DELETE FROM tracks
                    WHERE user_id = 1 AND round_id = other_demo_round;
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
