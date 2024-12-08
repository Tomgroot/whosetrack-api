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
    protected $signature = 'create-and-ready-competitors {joinCode?} {userId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates users, join the specific competition, submit a track and ready up.';

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

        if (!is_null($userId = $this->argument('userId'))) {
            $tracks[] = [
                'user_id' => $userId,
                'round_id' => $round->id,
                'ready' => true,
                'spotify_url' => 'https://open.spotify.com/track/44qWZU2uA8T5JHGPvk1lUs?si=81813ff140334bc3'
            ];
        }

        $count = DB::table('tracks')->insertOrIgnore($tracks);

        $round->updateStatus();

        if (is_null($userId)) {
            $userId = $competition->creator->id;
        }

        $insertedIds = DB::table('tracks')
            ->orderBy('id', 'desc')
            ->take(5)
            ->pluck('id');

        $guesses = [
            [
                'track_id' => $insertedIds[0],
                'guessed_user_id' => 101,
                'ready' => true,
            ],
            [
                'track_id' => $insertedIds[1],
                'guessed_user_id' => 102,
                'ready' => true,
            ],
            [
                'track_id' => $insertedIds[2],
                'guessed_user_id' => 103,
                'ready' => true,
            ],
            [
                'track_id' => $insertedIds[3],
                'guessed_user_id' => 104,
                'ready' => true,
            ],
            [
                'track_id' => $insertedIds[4],
                'guessed_user_id' => $userId,
                'ready' => true,
            ]
        ];

        foreach ([101, 102, 103, 104] as $id) {
            foreach ($guesses as $guess) {
                $guess['user_id'] = $id;
                DB::table('guesses')->insertOrIgnore($guess);
            }
        }
    }
}
