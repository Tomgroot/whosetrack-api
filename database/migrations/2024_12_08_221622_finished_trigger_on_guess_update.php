<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (['INSERT', 'UPDATE'] as $trigger) {
            $this->createTrigger($trigger);
        }
    }

    public function createTrigger($trigger) {
        $lowerTrigger = strtolower($trigger);
        DB::unprepared(<<<EO
            CREATE TRIGGER round_to_finished_when_guesses_submitted_$lowerTrigger
            AFTER $trigger ON guesses
            FOR EACH ROW
            BEGIN
                DECLARE round_guess_id INT;
                DECLARE count_not_ready INT;

                IF NEW.ready = 1 THEN
                    SELECT t.round_id INTO round_guess_id FROM tracks t WHERE t.id = NEW.track_id;

                    IF (SELECT r.status FROM rounds r WHERE r.id = round_guess_id) = 'guess_whose' THEN

                        SELECT COUNT(*) INTO count_not_ready
                        FROM (
                            SELECT ru.user_id
                            FROM round_user ru
                            JOIN tracks t ON t.round_id = ru.round_id
                            LEFT JOIN guesses g ON g.user_id = ru.user_id AND g.track_id = t.id AND g.ready = 1
                            WHERE ru.round_id = round_guess_id
                            GROUP BY ru.user_id
                            HAVING COUNT(g.user_id) < COUNT(t.id)
                        ) AS sub;

                        IF count_not_ready = 0 THEN
                            UPDATE rounds
                            SET status = 'finished', currently_playing_track = 0
                            WHERE id = round_guess_id;
                        END IF;
                    END IF;
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
