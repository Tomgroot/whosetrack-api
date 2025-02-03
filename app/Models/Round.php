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

    public function reset() {
        $this->status = self::STATUS_JOINING;
        $this->currently_playing_track = 0;
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
                if (!$users->contains('id', $guess->user_id)) {
                    continue;
                }

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
