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
        if ($this->status === self::STATUS_PICK_TRACK) {
            return $this->competition->users();
        }

        $trackUserIds = $this->tracks->pluck('user_id')->unique();
        return $this->users->whereIn('id', $trackUserIds);
    }

    public function updateStatus() {
        // Users should not start guessing when they are alone in the competition.
        if ($this->tracks->count() <= 1 || $this->tracks->pluck('ready')->contains(false)) {
            return;
        }

        $this->status = self::STATUS_GUESS_WHOSE;
        $this->save();
    }
}
