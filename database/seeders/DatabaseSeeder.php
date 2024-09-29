<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Competition;
use App\Models\Track;
use App\Models\Guess;
use App\Models\Round;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $user_1 = User::create([
            'id' => $_ENV['DEMO_USER_ID'],
            'nickname' => 'Demo user 1',
        ]);
        
        $user_2 = User::create([
            'nickname' => 'Demo user 2',
        ]);
        
        $user_3 = User::create([
            'nickname' => 'Demo user 3',
        ]);

        $demo_competition = Competition::create([
            'id' => $_ENV['DEMO_COMPETITION_ID'],
            'name' => 'Demo competition',
            'join_code' => 'DEMOOO',
            'joinable' => false,
            'created_by' => $user_1->id,
        ]);

        $demo_competition->users()->attach($user_1);
        $demo_competition->users()->attach($user_2);
        $demo_competition->users()->attach($user_3);
        
        $track_urls = [
            'https://open.spotify.com/track/6lKaiRbX5DtGMGNvE4xRbx?si=56ff71c45dbc40cb',
            'https://open.spotify.com/track/1V6ecjVT6IgPBiAtNyDWhh?si=e9627d25b78044e6',
            'https://open.spotify.com/track/4kZ9yt773M4ybgQsQQzaVH?si=86b140e6902041fe'
        ];        

        $cycle_round = Round::create([
            'id' => $_ENV['DEMO_ROUND_ID_1'],
            'competition_id' => $demo_competition->id,
            'status' => 'finished',
            'created_by' => $user_1->id,
            'currently_playing_track' => 0,
        ]);

        $cycle_round->users()->attach($user_1);
        $cycle_round->users()->attach($user_2);
        $cycle_round->users()->attach($user_3);
        
        foreach([$user_1, $user_2, $user_3] as $key => $demo_user){
            $track = Track::create([
                'user_id' => $demo_user->id,
                'round_id' => $cycle_round->id,
                'ready' => true,
                'spotify_url' => $track_urls[$key]
            ]);

            foreach([$user_1, $user_2, $user_3] as $guess_user){
                $guess = Guess::create([
                    'user_id' => $guess_user->id,
                    'track_id' => $track->id,
                    'guessed_user_id' => $demo_user->id,
                    'ready' => true,
                ]);
            }
            
        }

        DB::table('rounds')->where('id', $cycle_round->id)->update(['created_at' => date('Y-m-d H:i:s', time() - 1000000)]);

        $round = Round::create([
            'id' => $_ENV['DEMO_ROUND_ID_2'],
            'competition_id' => $demo_competition->id,
            'status' => 'pick_track',
            'created_by' => $user_1->id,
            'currently_playing_track' => 0,
        ]);

        $round->users()->attach($user_1);
        $round->users()->attach($user_2);
        $round->users()->attach($user_3);

        foreach([$user_2, $user_3] as $key => $demo_user){
            $track = Track::create([
                'user_id' => $demo_user->id,
                'round_id' => $round->id,
                'ready' => true,
                'spotify_url' => $track_urls[$key]
            ]);

            foreach([$user_2, $user_3] as $guess_user){
                $guess = Guess::create([
                    'user_id' => $guess_user->id,
                    'track_id' => $track->id,
                    'guessed_user_id' => $demo_user->id,
                    'ready' => true,
                ]);
            }
            
        }
    }
}
