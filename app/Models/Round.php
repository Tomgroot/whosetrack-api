<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Round extends Model {
    use HasFactory;

    public const STATUS_JOINING = 'joining';
    public const STATUS_PICK_TRACK = 'pick_track';
    public const STATUS_GUESS_WHOSE = 'guess_whose';
    public const STATUS_FINISHED = 'finished';

    protected $fillable = [
        'competition_id',
        'created_by',
        'currently_playing_track',
        'status',
        'gamemode',
    ];

    public static $rules = [
        'competition_id' => 'required|integer|exists:competitions,id',
        'currently_playing_track' => 'integer',
    ];

    public $with = [
        'users',
        'tracks',
    ];

    public $appends = [
        'results',
    ];

    protected $casts = [
        'competition_id' => 'integer',
        'created_by' => 'integer',
        'currently_playing_track' => 'integer',
    ];

    public static function rules($id) {
        return [
            'currently_playing_track' => 'integer',
        ];
    }

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function competition() {
        return $this->belongsTo(Competition::class);
    }

    public function tracks() {
        return $this->hasMany(Track::class)->orderBy('spotify_url');
    }

    public function users() {
        return $this->belongsToMany(User::class);
    }

    public function isDemo() {
        return $this->id === config('demo_constants.demo_round_id_1') || $this->id === config('demo_constants.demo_round_id_2');
    }

    public function handleDemoGuessWhose() {
        if (!$this->isDemo()) {
            return;
        }

        $dummy_demo_users = $this->users->where('id', '!=', config('demo_constants.demo_user_id'));
        $demo_user_track = $this->tracks->where('user_id', config('demo_constants.demo_user_id'))->first();

        foreach($dummy_demo_users as $guess_user){
            Guess::create([
                'user_id' => $guess_user->id,
                'track_id' => $demo_user_track->id,
                'guessed_user_id' => $guess_user->id,
                'ready' => true,
            ]);
        }
    }

    public function handleDemoFinished() {
        if (!$this->isDemo()) {
            return;
        }

        $newRoundId = ($this->id === config('demo_constants.demo_round_id_1')) ? config('demo_constants.demo_round_id_2') : config('demo_constants.demo_round_id_1');
        $cycle_round = Round::find($newRoundId);

        // Remove demo user track and guesses so round can be played again.
        $demo_user_1_track = $cycle_round->tracks->where('user_id', config('demo_constants.demo_user_id'))->first();
        $demo_user_1_track->delete();
        $other_user_tracks = $cycle_round->tracks->where('user_id', '!=', config('demo_constants.demo_user_id'));
        foreach($other_user_tracks as $track){
            $demo_user_1_guess = $track->guesses->where('user_id', config('demo_constants.demo_user_id'))->first();
            $demo_user_1_guess->delete();
        }

        $cycle_round->reset();

        // Update creation times to put `most_recent_round` correctly. Done via SQL since eloquent doesn't handle creation time
        DB::table('rounds')->where('id', $cycle_round->id)->update(['created_at' => date('Y-m-d H:i:s', time())]);
        DB::table('rounds')->where('id', $this->id)->update(['created_at' => date('Y-m-d H:i:s', time() - 1000000)]);
    }

    public function reset() {
        $this->status = self::STATUS_JOINING;
        $this->currently_playing_track = 0;
        $this->save();
    }

    public function updateStatus() {
        if ($this->status === self::STATUS_JOINING){
            return;
        }

        $this->load('tracks');

        // Users should not start guessing when they are alone or with 2 in the competition.
        if ($this->tracks()->count() <= 2 || $this->tracks()->pluck('ready')->contains(0)) {
            return;
        }

        if ($this->status == self::STATUS_PICK_TRACK){
            $this->status = self::STATUS_GUESS_WHOSE;
            $this->handleDemoGuessWhose();

        } elseif ($this->status == self::STATUS_GUESS_WHOSE){
            $nr_users = $this->users->count();
            foreach($this->tracks as $track){
                if($track->guesses()->count() < $nr_users || $track->guesses()->pluck('ready')->contains(0)){
                    return;
                }
            }
            $this->currently_playing_track = 0;
            $this->status = self::STATUS_FINISHED;
            $this->handleDemoFinished();
        }

        $this->save();
    }

    public function getResultsAttribute() {
        if($this->status == 'finished'){
            return $this->results();
        } else {
            return [];
        }
    }

    public function results() {
        $users = $this->users->sortBy('id');
        $scores = [];

        foreach($users as $user){
            $scores[] = [
                'position' => 0,
                'score' => 0,
                'user' => $user,
                /** deprecated: TODO remove after update */
                'user_id' => $user->id,
                /** deprecated: TODO remove after update */
                'nickname' => $user->nickname
            ];
        }

        foreach($this->tracks as $track){
            foreach($track->guesses->sortBy('user_id')->values() as $index => $guess){
                $extra = (int)($guess->guessed_user_id === $track->user_id || $guess->user_id === $track->user_id);
                $scores[$index]['score'] += $extra;
            }
        }

        // Sort the array based on the scores, but keep the user ids as keys.
        usort($scores, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        $position = 0;
        $previous_score = -1;
        foreach ($scores as &$score) {
            if($score['score'] == $previous_score){
                $score['position'] = $scores[$position - 1]['position'];
            } else {
                $score['position'] = $position;
            }
            $position += 1;
            $previous_score = $score['score'];
        }

        return $scores;
    }
}
