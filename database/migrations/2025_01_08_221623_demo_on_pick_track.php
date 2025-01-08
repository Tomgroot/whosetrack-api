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
                    DELETE FROM guesses WHERE track_id IN (SELECT t.id FROM tracks t WHERE t.round_id = NEW.id);
                    DELETE FROM tracks WHERE round_id = NEW.id AND user_id NOT IN (2, 3);
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
            DROP TRIGGER IF EXISTS demo_on_pick_track;
        EO);
    }
};
