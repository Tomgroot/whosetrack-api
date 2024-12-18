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
            CREATE TRIGGER demo_on_pick_track
            BEFORE UPDATE ON rounds
            FOR EACH ROW
            BEGIN
                IF NEW.status = 'pick_track' AND OLD.status != 'pick_track' AND (NEW.id = 1 OR NEW.id = 2) THEN
                    DELETE FROM guesses g WHERE g.track_id IN (SELECT t.id FROM tracks t WHERE t.round_id = NEW.id AND t.user_id = 1);
                    DELETE FROM guesses g WHERE g.user_id = 1 AND g.track_id IN (SELECT t.id FROM tracks t WHERE t.round_id = NEW.id);
                    DELETE FROM tracks t WHERE t.round_id = NEW.id AND t.user_id = 1;
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
