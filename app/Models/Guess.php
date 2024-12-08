<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guess extends Model {
    use HasFactory;

    public static $rules = [
        'user_id' => 'required|integer|exists:users,id',
        'track_id' => 'required|integer|exists:tracks,id',
        'guessed_user_id' => 'required|integer|exists:users,id',
        'ready' => 'boolean',
    ];

    protected $fillable = [
        'user_id',
        'track_id',
        'guessed_user_id',
        'ready',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'track_id' => 'integer',
        'guessed_user_id' => 'integer',
        'ready' => 'boolean',
    ];

    public $appends = [
        /** deprecated use guessedNickname */
        'nickname',
        'guessed_nickname',
    ];

    public static function rules($id) {
        return self::$rules;
    }

    public function track() {
        return $this->belongsTo(Track::class);
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function guessedUser() {
        return $this->belongsTo(User::class, 'guessed_user_id');
    }

    /**
     * deprecated: use guessedNickname instead TODO: remove
     */
    public function getNicknameAttribute() {
        return $this->guessedUser()->first()->nickname;
    }

    public function getGuessedNicknameAttribute() {
        return $this->guessedUser()->first()->nickname;
    }
}
