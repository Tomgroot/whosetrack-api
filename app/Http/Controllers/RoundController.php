<?php

namespace App\Http\Controllers;

use App\Models\Round;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoundController extends Controller {

    public function __construct() {
        self::$entity = Round::class;
    }

    public function updateCurrentTrack($round_id, Request $request): JsonResponse {
        $round = Round::findOrFail($round_id);

        $round->update(["current_track" => $request->current_track]);

        return response()->json($round);
    }

    public function store(Request $request): JsonResponse {
        $competition_id = $request->route('competition_id') ?? $request->get('competition_id');

        $validator = Validator::make([
            'competition_id' => $competition_id,
        ], Round::$rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $entity = Round::create([
            'competition_id' => $competition_id,
            'current_track' => 0,
            'status' => 'pick_track',
        ]);

        return response()->json($entity, 201);
    }

    public function getResults($round_id): JsonResponse {
        $round = Round::findOrFail($round_id);
        return response()->json($round->results());
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
