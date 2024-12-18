<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\Round;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RoundController extends Controller {

    public function __construct() {
        self::$entity = Round::class;
    }

    public function store(Request $request): JsonResponse {
        $competition_id = $request->route('competition_id') ?? $request->get('competition_id');
        $user_id = $request->get('user_id');

        if (is_null($user = User::find($user_id))) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $validator = Validator::make([
            'competition_id' => $competition_id,
        ], Round::$rules);

        $competition = Competition::findOrFail($competition_id);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($competition->isDemo()) {
            $round = $competition->mostRecentRound();
            $round->reset();
            return response()->json($round, 201);
        }

        $round = Round::create([
            'competition_id' => $competition->id,
            'status' => 'joining',
            'created_by' => $user_id,
            'currently_playing_track' => 0,
            'gamemode' => $request->get('gamemode'),
        ]);

        $round->users()->attach($user);

        $round->load(['users', 'tracks']);

        return response()->json($round, 201);
    }

    public function update(Request $request, $id): JsonResponse {
        if ($request->get('start') && !is_null($round = Round::findOrFail($id))) {
            $round->status = Round::STATUS_PICK_TRACK;
            $round->save();
            return response()->json($round);
        }

        return parent::update($request, $id);
    }

    public function leaveRound($round_id, $user_id): JsonResponse {
        $round = Round::findOrFail($round_id);

        if ($round->isDemo()) {
            return response()->json($round);
        }

        if (is_null($user = User::find($user_id))) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if ($round->status !== Round::STATUS_JOINING && $round->status !== Round::STATUS_PICK_TRACK) {
            return response()->json(['error' => 'User cannot leave ongoing competition'], 409);
        }

        $round->users()->detach($user);

        $round->tracks()->where('user_id', $user->id)->delete();

        return response()->json($round);
    }

    public function getResults($round_id): JsonResponse {
        $round = Round::findOrFail($round_id);
        return response()->json($round->results());
    }

    public function readyGuesses(Request $request): JsonResponse {
        // TODO: unsafe usage of user id, everybody can edit each others readiness
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $round = Round::findOrFail($request->route('round_id'));

        DB::table('guesses')
            ->join('tracks', 'guesses.track_id', '=', 'tracks.id')
            ->where('round_id', $round->id)
            ->where('guesses.user_id', $validated['user_id'])
            ->update(['guesses.ready' => true]);

        return response()->json($round);
    }

    public function getRelation($round_id, $relation) {
        if (!in_array($relation, ['users', 'tracks', 'competition'])) {
            return response()->json(['message' => 'Invalid data type requested'], 400);
        }

        if (is_null($round = Round::find($round_id))) {
            return response()->json(['message' => 'Relation not found'], 404);
        }

        if ($relation === 'tracks') {
            $round->load(['tracks' => function ($query) {
                $query->with(['guesses']);
            }]);
        }

        return response()->json($round->{$relation});
    }
}
