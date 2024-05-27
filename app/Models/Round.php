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
        'status',
    ];

    public static $rules = [
        'competition_id' => 'required|integer|exists:competitions,id',
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
        return $this->belongsTo(Competition::class, 'competition_id', 'id');
    }

    public function tracks() {
        return $this->hasMany(Track::class)->orderBy('spotify_url');
    }

    public function users() {
        if ($this->status == self::STATUS_PICK_TRACK) {
            return $this->competition()->first()->users();
        }

        $trackUserIds = $this->tracks()->pluck('user_id')->unique();
        return $this->competition()->first()->users();

    }

    public function results() {
        $debug = [];
        $users = $this->users->sortBy('id');

        foreach($users as &$user){
            $user->score = 0;
        }

        foreach($this->tracks as $track){

            foreach($track->guesses->sortBy('user_id')->values() as $key => $guess){
                $extra = (int)($guess->guessed_user_id == $track->user_id || $users[$key]->user_id == $track->user_id);
                $users[$key]->score += $extra;
            }
        }

        // Not super happy with this, but otherwise a foreach counter is needed because keys are messed up
        $users = array_values($users->sortBy('score', SORT_REGULAR, true)->values()->all());

        $previous_score = count($users) + 1;
        
        foreach($users as $key => &$user){
            if($user->score < $previous_score){
                $user->position = $key + 1;
                $previous_score = $user->score;
            } else {
                $user->position = $users[$key - 1]->position;
            }
        }

        return $users;
    }

    public function getCreatorAttribute() {
        return $this->competition()->first()->creator()->first();
    }

    public function updateStatus() {
        // Users should not start guessing when they are alone or with 2 in the competition.
        if ($this->tracks->count() <= 2 || $this->tracks->pluck('ready')->contains(0)) {
            return;
        }

        // TODO: there must be a better way to do this...
        foreach($this->tracks as $track){
            $track->ready = 0;
            $track->save();
        };

        if ($this->status == self::STATUS_PICK_TRACK){
            $this->status = self::STATUS_GUESS_WHOSE;
        } elseif ($this->status == self::STATUS_GUESS_WHOSE){
            $this->status = self::STATUS_FINISHED;
        }

        $this->save();
    }
}
