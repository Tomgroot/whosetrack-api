<?php

namespace App\Console\Commands;

use App\Models\Competition;
use App\Models\Track;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FillMissingGuessesForCompetition extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fill-missing-guesses {joinCode?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send in guesses for tracks that are still missing.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!is_null($this->argument('joinCode'))) {
            $competition = Competition::where('join_code', $this->argument('joinCode'))->first();
        } else {
            $competition = Competition::orderBy('created_at', 'desc')->first();
        }

        if (!$competition) {
            throw new \Exception('Competition not found');
        }

        // all users should have posted a guess to all tracks

        $round = $competition->mostRecentRound();

        $tracks = $round->tracks;
        $users = $round->users;

        foreach ($tracks as $track) {
            $guessedUserIds = $track->guesses->pluck('user_id')->unique();

            $missingUserIds = $users->pluck('id')->diff($guessedUserIds);

            foreach ($missingUserIds as $userId) {
                DB::table('guesses')->insertOrIgnore([
                    'track_id' => $track->id,
                    'user_id' => $userId,
                    'guessed_user_id' => 101,
                    'ready' => true,
                ]);
            }
        }

        $round->updateStatus();
    }
}
