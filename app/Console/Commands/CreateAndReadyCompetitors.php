<?php

namespace App\Console\Commands;

use App\Models\Competition;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateAndReadyCompetitors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create-and-join-competitors {joinCode?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates users and join the specific competition';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::table('users')->insertOrIgnore([
            ['id' => 101, 'nickname' => 'Henk', 'image_url' => null, 'spotify_guid' => null],
            ['id' => 102, 'nickname' => 'Peter', 'image_url' => null, 'spotify_guid' => null],
            ['id' => 103, 'nickname' => 'Jan', 'image_url' => null, 'spotify_guid' => null],
            ['id' => 104, 'nickname' => 'Freek', 'image_url' => null, 'spotify_guid' => null],
        ]);

        if (!is_null($this->argument('joinCode'))) {
            $competition = Competition::where('join_code', $this->argument('joinCode'))->first();
        } else {
            $competition = Competition::orderBy('created_at', 'desc')->first();
        }

        if (!$competition) {
            throw new \Exception('Competition not found');
        }
        DB::table('competition_user')->insertOrIgnore([
            ['user_id' => 101, 'competition_id' => $competition->id],
            ['user_id' => 102, 'competition_id' => $competition->id],
            ['user_id' => 103, 'competition_id' => $competition->id],
            ['user_id' => 104, 'competition_id' => $competition->id],
        ]);

        $round = $competition->mostRecentRound();

        echo "Joining competition " . $competition->id . " with most recent round id " . $round->id
            . " and creator " . $competition->creator->id;

        DB::table('round_user')->insertOrIgnore([
            ['user_id' => 101, 'round_id' => $round->id],
            ['user_id' => 102, 'round_id' => $round->id],
            ['user_id' => 103, 'round_id' => $round->id],
            ['user_id' => 104, 'round_id' => $round->id],
        ]);
    }
}
