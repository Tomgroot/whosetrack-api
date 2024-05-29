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
        'current_track',
        'status',
    ];

    public static $rules = [
        'competition_id' => 'required|integer|exists:competitions,id',
        'current_track' => 'required|integer',
    ];

    public $with = [
        'users',
        'tracks',
    ];

    public $appends = [
        'creator',
    ];

    public static function rules($id) {
        return self::$rules;
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
            $this->status = self::STATUS_FINISHED;
        }

        $this->save();
    }

    public function results() {
        $users = $this->users->sortBy('id');
        $scores = [];

        foreach($users as $user){
            $scores[$user->id] = [
                'position' => 0,
                'score' => 0,
            ];
        }

        foreach($this->tracks as $track){
            foreach($track->guesses->sortBy('user_id')->values() as $guesser_id => $guess){
                $extra = (int)($guess->guessed_user_id === $track->user_id || $guesser_id === $track->user_id);
                $scores[$guesser_id]['score'] += $extra;
            }
        }

        // Sort the array based on the scores, but keep the user ids as keys.
        uasort($scores, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        $position = 1;
        foreach ($scores as &$score) {
            $score['position'] = $position++;
        }

        return $scores;
    }
}
