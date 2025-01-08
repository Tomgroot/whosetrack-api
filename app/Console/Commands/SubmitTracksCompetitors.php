<?php

namespace App\Console\Commands;

use App\Models\Competition;
use App\Models\Track;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SubmitTracksCompetitors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'submit-tracks-competitors {joinCode?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Submit tracks for specific users.';

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

        $round = $competition->mostRecentRound();

        $tracks = [
            [
                'user_id' => 101,
                'round_id' => $round->id,
                'ready' => true,
                'spotify_url' => 'https://open.spotify.com/track/6lKaiRbX5DtGMGNvE4xRbx?si=56ff71c45dbc40cb'
            ],
            [
                'user_id' => 102,
                'round_id' => $round->id,
                'ready' => true,
                'spotify_url' => 'https://open.spotify.com/track/1V6ecjVT6IgPBiAtNyDWhh?si=e9627d25b78044e6'
            ],
            [
                'user_id' => 103,
                'round_id' => $round->id,
                'ready' => true,
                'spotify_url' => 'https://open.spotify.com/track/4YEsNhGhXXtefznetrzhMb?si=62052ba2c97048ac'
            ],
            [
                'user_id' => 104,
                'round_id' => $round->id,
                'ready' => true,
                'spotify_url' => 'https://open.spotify.com/track/46HNZY1i7O6jwTA7Slo2PI?si=dd25fb86c9f6411e'
            ]
        ];

        DB::table('tracks')->insertOrIgnore($tracks);
    }
}
