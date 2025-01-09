<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $roundIdOne = config('demo_constants.demo_round_id_1');
        $roundIdTwo = config('demo_constants.demo_round_id_2');
        $demoUserOne = config('demo_constants.demo_user_id_1');
        $demoUserTwo = config('demo_constants.demo_user_id_2');
        $demoUserThree = config('demo_constants.demo_user_id_3');
        DB::unprepared(<<<EO
            DROP TRIGGER IF EXISTS demo_on_pick_track;
            CREATE TRIGGER demo_on_pick_track
            BEFORE UPDATE ON rounds
            FOR EACH ROW
            BEGIN
                IF NEW.status = 'joining' AND OLD.status != 'joining' AND (NEW.id = $roundIdOne OR NEW.id = $roundIdTwo) THEN
                    DELETE FROM round_user WHERE round_id = NEW.id AND user_id NOT IN ($demoUserTwo, $demoUserThree, $demoUserOne);
                    DELETE FROM competition_user WHERE competition_id = NEW.competition_id AND user_id NOT IN ($demoUserTwo, $demoUserThree, $demoUserOne);
                    DELETE FROM guesses WHERE track_id IN (SELECT t.id FROM tracks t WHERE t.round_id = NEW.id);
                    DELETE FROM tracks WHERE round_id = NEW.id AND user_id NOT IN ($demoUserTwo, $demoUserThree);
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
