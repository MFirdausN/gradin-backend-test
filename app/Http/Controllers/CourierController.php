<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexCourierRequest;
use App\Http\Requests\StoreCourierRequest;
use App\Http\Requests\UpdateCourierRequest;
use App\Http\Resources\CourierResource;
use App\Models\Courier;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CourierController extends Controller
{
    public function index(IndexCourierRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();
        $query = Courier::query();

        foreach (preg_split('/\s+/u', trim($filters['search'] ?? ''), -1, PREG_SPLIT_NO_EMPTY) as $term) {
            $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($term)).'%';
            $query->whereRaw("LOWER(name) LIKE ? ESCAPE '!'", [$pattern]);
        }

        if (isset($filters['level'])) {
            $query->whereIn('level', $filters['level']);
        }

        return CourierResource::collection(
            $query->orderBy($filters['sort'] ?? 'name', $filters['direction'] ?? 'asc')
                ->orderBy('id')
                ->paginate($filters['per_page'] ?? 15)
                ->withQueryString()
        );
    }

    public function store(StoreCourierRequest $request): CourierResource
    {
        return new CourierResource(Courier::create($request->validated())->refresh());
    }

    public function show(Courier $courier): CourierResource
    {
        return new CourierResource($courier);
    }

    public function update(UpdateCourierRequest $request, Courier $courier): CourierResource
    {
        $courier->update($request->validated());

        return new CourierResource($courier->refresh());
    }

    public function destroy(Courier $courier): Response
    {
        $courier->delete();

        return response()->noContent();
    }
}
