<?php

namespace App\Http\Controllers;

use App\Models\User;

class UserController extends Controller {

    public function __construct() {
        self::$entity = User::class;
    }

    public function getCompetitions($user_id) {
        if (is_null($user = User::with('competitions')->find($user_id))) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($user->competitions);
    }
}
