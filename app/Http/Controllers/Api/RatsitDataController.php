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

        if ($request->has('bo_postnummer')) {
            $query->where('bo_postnummer', 'like', "%{$request->bo_postnummer}%");
        }

        if ($request->has('bo_postort')) {
            $query->where('bo_postort', 'like', "%{$request->bo_postort}%");
        }

        if ($request->has('bo_kommun')) {
            $query->where('bo_kommun', 'like', "%{$request->bo_kommun}%");
        }

        if ($request->has('bo_lan')) {
            $query->where('bo_lan', 'like', "%{$request->bo_lan}%");
        }

        if ($request->has('ps_personnummer')) {
            $query->where('ps_personnummer', 'like', "%{$request->ps_personnummer}%");
        }

        if ($request->has('ps_personnamn')) {
            $query->where('ps_personnamn', 'like', "%{$request->ps_personnamn}%");
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

        // Upsert by ps_personnummer when provided
        if (! empty($data['ps_personnummer'])) {
            $existing = RatsitData::query()
                ->where('ps_personnummer', $data['ps_personnummer'])
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
