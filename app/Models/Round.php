<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Round extends Model {
    use HasFactory;

    public const STATUS_PICK_TRACK = 'pick_track';
    public const STATUS_GUESS_WHOSE = 'guess_whose';
    public const STATUS_FINISHED = 'finished';

    protected $fillable = [
        'competition_id',
        'currently_playing_track',
        'status',
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
        'creator',
    ];

    public static function rules($id) {
        return [
            'currently_playing_track' => 'integer',
        ];
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

    public function getCreatorAttribute() {
        return $this->competition()->first()->creator()->first();
    }

    public function updateStatus() {
        $this->load('tracks');

        // Users should not start guessing when they are alone or with 2 in the competition.
        if ($this->tracks()->count() <= 2 || $this->tracks()->pluck('ready')->contains(0)) {
            return;
        }

        if ($this->status == self::STATUS_PICK_TRACK){
            $this->status = self::STATUS_GUESS_WHOSE;
        } elseif ($this->status == self::STATUS_GUESS_WHOSE){
            $nr_users = $this->users->count();
            foreach($this->tracks as $track){
                if($track->guesses()->count() < $nr_users || $track->guesses()->pluck('ready')->contains(0)){
                    return;
                }
            }
            $this->currently_playing_track = 0;
            $this->status = self::STATUS_FINISHED;
        }

        $this->save();
    }

    public function results() {
        $users = $this->users->sortBy('id');
        $scores = [];

        foreach($users as $user){
            $scores[] = [
                'position' => 0,
                'score' => 0,
                'user_id' => $user->id,
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
                $position = $scores[$position - 1]['position'];
            } else {
                $score['position'] = $position;
            }
            $position += 1;
            $previous_score = $score['score'];
        }

        return $scores;
    }
}
