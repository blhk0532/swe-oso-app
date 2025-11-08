<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRatsitDataRequest;
use App\Http\Requests\UpdateRatsitDataRequest;
use App\Http\Resources\RatsitDataResource;
use App\Models\RatsitData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RatsitDataController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = RatsitData::query();

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
            $query->where('personnamn', 'like', "%{$request->personnamn}%");
        }

        $sortField = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $perPage = min($request->get('per_page', 25), 100);
        $records = $query->paginate($perPage);

        return RatsitDataResource::collection($records);
    }

    public function store(StoreRatsitDataRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Upsert by personnummer when provided
        if (! empty($data['personnummer'])) {
            $existing = RatsitData::query()
                ->where('personnummer', $data['personnummer'])
                ->first();

            if ($existing) {
                $existing->update($data);

                return (new RatsitDataResource($existing))
                    ->response()
                    ->setStatusCode(200);
            }
        }

        $record = RatsitData::create($data);

        return (new RatsitDataResource($record))
            ->response()
            ->setStatusCode(201);
    }

    public function show(RatsitData $ratsitData): RatsitDataResource
    {
        return new RatsitDataResource($ratsitData);
    }

    public function update(UpdateRatsitDataRequest $request, RatsitData $ratsitData): RatsitDataResource
    {
        $ratsitData->update($request->validated());

        return new RatsitDataResource($ratsitData);
    }

    public function destroy(RatsitData $ratsitData): JsonResponse
    {
        $ratsitData->delete();

        return response()->json(['message' => 'Record deleted successfully'], 200);
    }
}
