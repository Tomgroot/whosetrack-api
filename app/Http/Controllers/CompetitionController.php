<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\Round;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CompetitionController extends Controller {

    public function __construct() {
        self::$entity = Competition::class;
    }

    public function addUserToRecentRound($competition, $user) {
        $round = $competition->mostRecentRound();
        if ($round->users()->where('users.id', $user->id)->exists()) {
            return false;
        } else {
            $round->users()->attach($user);
        }
    }

    public function addUserToCompetitionAndRound($competition, $user) {
        $competition->users()->attach($user);

        $this->addUserToRecentRound($competition, $user);
    }

    public function generateRandomName(): string {
        $adjectives = config('default_competition_names.adjectives', []);
        $animals = config('default_competition_names.animals', []);

        return $adjectives[array_rand($adjectives)] . ' ' . $animals[array_rand($animals)];

    }

    public function store(Request $request): JsonResponse {
        $request->merge([
            'name' => $request->get('name') ?? $this->generateRandomName(),
        ]);

        $validated = $request->validate(Competition::$rules);

        if (is_null($user = User::find($validated['created_by']))) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $competition = Competition::create([
            'name' => $validated['name'],
            'join_code' => self::generateRandomJoinCode(),
            'joinable' => true,
            'created_by' => intval($validated['created_by']),
        ]);

        $competition->users()->attach($user);

        // At creation of a competition, users do not have to call.
        Round::create([
            'competition_id' => $competition->id,
            'current_track' => 0,
            'status' => 'joining',
            'created_by' => intval($validated['created_by']),
        ]);
        $this->addUserToRecentRound($competition, $user);

        return response()->json($competition, 201);
    }

    public function join($join_code, $user_id): JsonResponse {
        $validator = Validator::make([
            'join_code' => $join_code,
            'user_id' => $user_id,
        ], [
            'join_code' => 'required|string',
            'user_id' => 'required|integer|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (is_null($user = User::find($user_id))) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $competition = Competition::where('join_code', $join_code)->first();

        if (is_null($competition)) {
            return response()->json(['error' => 'Competition not found'], 404);
        }

        if (!$competition->joinable && $competition->id != config('demo_constants.demo_competition_id')){
                return response()->json(['error' => 'Competition is not joinable'], 422);
        }

        if ($competition->users()->where('users.id', $user->id)->exists()) {
            $this->addUserToRecentRound($competition, $user);
        } else {
            $this->addUserToCompetitionAndRound($competition, $user);
        }


        return response()->json([
            'competition_id' => $competition->id,
            'most_recent_round' => $competition->most_recent_round,
            'success' => true
        ], 201);
    }

    static function generateRandomJoinCode($length = 6) {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        return substr(
            str_shuffle(str_repeat($characters, ceil($length/strlen($characters)))),
            1,
            $length
        );
    }

    public function getRelation($competition_id, $relation) {
        if (!in_array($relation, ['users', 'rounds'])) {
            return response()->json(['message' => 'Invalid data type requested'], 400);
        }

        if (is_null($competition = Competition::find($competition_id))) {
            return response()->json(['message' => 'Competition not found'], 404);
        }

        return response()->json($competition->{$relation});
    }
}
