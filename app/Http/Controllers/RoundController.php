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
            'status' => 'pick_track',
        ]);

        return response()->json($entity, 201);
    }

    public function getRelation($round_id, $relation) {
        if (!in_array($relation, ['users', 'tracks'])) {
            return response()->json(['message' => 'Invalid data type requested'], 400);
        }

        if (is_null($round = Round::find($round_id))) {
            return response()->json(['message' => 'Relation not found'], 404);
        }

        if ($relation === 'tracks') {
            $round->load(['tracks' => function ($query) {
                $query->with('guesses');
            }]);
        }

        return response()->json($round->{$relation});
    }
}
