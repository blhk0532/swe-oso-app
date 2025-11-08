<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDataPrivateRequest;
use App\Http\Requests\UpdateDataPrivateRequest;
use App\Http\Resources\DataPrivateResource;
use App\Models\DataPrivate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DataPrivateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = DataPrivate::query();

        // Apply filters
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('postnummer')) {
            $query->where('postnummer', 'like', "%{$request->postnummer}%");
        }

        if ($request->has('postort')) {
            $query->where('postort', 'like', "%{$request->postort}%");
        }

        if ($request->has('kommun')) {
            $query->where('kommun', 'like', "%{$request->kommun}%");
        }

        if ($request->has('lan')) {
            $query->where('lan', 'like', "%{$request->lan}%");
        }

        if ($request->has('personnummer')) {
            $query->where('personnummer', 'like', "%{$request->personnummer}%");
        }

        if ($request->has('personnamn')) {
            $query->searchByName($request->personnamn);
        }

        // Apply sorting
        $sortField = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Pagination
        $perPage = min($request->get('per_page', 25), 100);
        $records = $query->paginate($perPage);

        return DataPrivateResource::collection($records);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDataPrivateRequest $request): JsonResponse
    {
        $dataPrivate = DataPrivate::create($request->validated());

        return (new DataPrivateResource($dataPrivate))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(DataPrivate $dataPrivate): DataPrivateResource
    {
        return new DataPrivateResource($dataPrivate);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDataPrivateRequest $request, DataPrivate $dataPrivate): DataPrivateResource
    {
        $dataPrivate->update($request->validated());

        return new DataPrivateResource($dataPrivate);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DataPrivate $dataPrivate): JsonResponse
    {
        $dataPrivate->delete();

        return response()->json(['message' => 'Record deleted successfully'], 200);
    }
}
