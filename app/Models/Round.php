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

    protected $appends = [
        'missing_track_users',
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
        return $this->competition->users()->wherePivot('created_at', '<', $this->created_at);
    }

    public function getMissingTrackUsersAttribute() {
        $trackUserIds = $this->tracks->pluck('user_id')->unique();

        return $this->users->whereNotIn('id', $trackUserIds);
    }

    public function updateStatus() {
        if ($this->status !== self::STATUS_PICK_TRACK
            || $this->getMissingTrackUsersAttribute()->count() == 0) {
            return;
        }

        $this->status = self::STATUS_GUESS_WHOSE;
        $this->save();
    }
}
