<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller {

    public function __construct() {
        self::$entity = User::class;
    }

    public function store(Request $request): JsonResponse {
        $validated = $request->validate(User::$rules);

        if ($request->get('nickname') == 'Demo user 1') {
            $user = User::find($_ENV['DEMO_USER_ID']);
        } else {
            $user = User::create($validated);
        }

        return response()->json($user, 201);
    }

    public function index(): JsonResponse {
        if (is_null($spotify_guid = request()->query('spotify_guid'))) {
            return parent::index();
        }

        return response()->json(User::where('spotify_guid', $spotify_guid)->first());
    }

    public function getCompetitions($user_id) {
        if (is_null($user = User::with('competitions')->find($user_id))) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($user->competitions);
    }
}
