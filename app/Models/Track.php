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

    protected $hidden = [
        'user',
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

    protected $casts = [
        'user_id' => 'integer',
        'round_id' => 'integer',
        'ready' => 'boolean',
    ];

    public $appends = [
        'nickname',
    ];

    public $with = [
        'guesses',
        'user',
    ];

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
        $user = $this->user;
        return !is_null($user) ? $user->nickname : null;
    }
}
