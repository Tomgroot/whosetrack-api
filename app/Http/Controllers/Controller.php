<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class Controller {

    protected static $entity;

    public function index(): JsonResponse {
        $entities = self::$entity::paginate(10);
        return response()->json($entities);
    }

    public function store(Request $request): JsonResponse {
        $validated = $request->validate(self::$entity::$rules);

        $entity = self::$entity::create($validated);
        return response()->json($entity, 201);
    }

    public function show($id) {
        return self::$entity::findOrFail($id);
    }

    public function update(Request $request, $id): JsonResponse {
        $entity = self::$entity::findOrFail($id);
        $rules = self::$entity::rules($id);
        $validated = $request->validate($rules);

        $entity->update($validated);
        return response()->json($entity);
    }

    public function destroy($id): JsonResponse {
        $entity = self::$entity::findOrFail($id);
        $entity->delete();
        return response()->json(null, 204);
    }
}
