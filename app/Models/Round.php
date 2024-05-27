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
