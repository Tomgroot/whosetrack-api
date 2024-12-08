<?php

namespace App\Console\Commands;

use App\Models\Track;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendGuessesForTrack extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send-guesses-for-track {trackId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send in guesses for track.';

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

        $track = Track::find($this->argument('trackId'));

        if (!$track) {
            throw new \Exception('Track not found'. $this->argument('trackId'));
        }

        $guess = [
            'track_id' => $this->argument('trackId'),
            'guessed_user_id' => 101,
            'ready' => true,
        ];

        foreach ([101, 102, 103, 104] as $id) {
            $guess['user_id'] = $id;
            DB::table('guesses')->insertOrIgnore($guess);
        }
    }
}
