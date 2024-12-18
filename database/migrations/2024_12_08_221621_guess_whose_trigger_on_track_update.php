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
            CREATE TRIGGER round_to_guess_whose_when_tracks_submitted_$lowerTrigger
            AFTER $trigger ON tracks
            FOR EACH ROW
            BEGIN
                DECLARE count_not_ready INT;

                IF NEW.ready = 1 AND (SELECT r.status FROM rounds r WHERE r.id = NEW.round_id) = 'pick_track' THEN

                    SELECT COUNT(*) INTO count_not_ready
                    FROM (
                        SELECT ru.user_id
                        FROM round_user ru
                        LEFT JOIN tracks t ON t.round_id = ru.round_id
                        AND t.user_id = ru.user_id
                        AND t.ready = 1
                        WHERE ru.round_id = NEW.round_id
                        GROUP BY ru.user_id
                        HAVING COUNT(t.id) = 0
                    ) AS sub;

                    if count_not_ready = 0 THEN
                        UPDATE rounds
                        SET status = 'guess_whose'
                        WHERE id = NEW.round_id;
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
