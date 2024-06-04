<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Track extends Model {
    use HasFactory;

    protected $fillable = [
        'user_id',
        'round_id',
        'spotify_url',
        'ready',
    ];

    public static $rules = [
        'user_id' => 'required|integer|exists:users,id',
        'round_id' => 'required|integer|exists:rounds,id',
        'spotify_url' => 'url|starts_with:https://open.spotify.com/',
        'ready' => 'boolean',
    ];

    public static function rules($id) {
        $rules = self::$rules;
        $rules['user_id'] = str_replace('required|', '', $rules['user_id']);
        $rules['round_id'] = str_replace('required|', '', $rules['round_id']);
        return $rules;
    }

    public $appends = [
        'nickname',
    ];

    public $with = [
        'guesses',
    ];

    public function getMissingGuessUsersAttribute() {
        $guessUserIds = $this->guesses->pluck('user_id')->unique();

        return $this->round()->first()->users->whereNotIn('id', $guessUserIds);
    }

    public function round() {
        return $this->belongsTo(Round::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function guesses() {
        return $this->hasMany(Guess::class);
    }

    public function getNicknameAttribute() {
        $user = $this->user()->first();
        return !is_null($user) ? $user->nickname : null;
    }
}
