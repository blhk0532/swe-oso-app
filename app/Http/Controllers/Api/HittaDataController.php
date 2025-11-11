<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHittaDataRequest;
use App\Http\Requests\UpdateHittaDataRequest;
use App\Http\Resources\HittaDataResource;
use App\Models\HittaData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HittaDataController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = HittaData::query();

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('is_telefon')) {
            $query->where('is_telefon', $request->boolean('is_telefon'));
        }

        if ($request->has('is_ratsit')) {
            $query->where('is_ratsit', $request->boolean('is_ratsit'));
        }

        if ($request->has('postnummer')) {
            $query->where('postnummer', 'like', "%{$request->postnummer}%");
        }

        if ($request->has('postort')) {
            $query->where('postort', 'like', "%{$request->postort}%");
        }

        if ($request->has('personnamn')) {
            $query->where('personnamn', 'like', "%{$request->personnamn}%");
        }

        if ($request->has('telefon')) {
            $query->where('telefon', 'like', "%{$request->telefon}%");
        }

        if ($request->has('bostadstyp')) {
            $query->where('bostadstyp', 'like', "%{$request->bostadstyp}%");
        }

        $sortField = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $perPage = min($request->get('per_page', 25), 100);
        $records = $query->paginate($perPage);

        return HittaDataResource::collection($records);
    }

    public function store(StoreHittaDataRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Upsert by personnamn when provided
        if (! empty($data['personnamn'])) {
            $existing = HittaData::query()
                ->where('personnamn', $data['personnamn'])
                ->first();

            if ($existing) {
                $existing->update($data);

                return (new HittaDataResource($existing))
                    ->response()
                    ->setStatusCode(200);
            }
        }

        $record = HittaData::create($data);

        return (new HittaDataResource($record))
            ->response()
            ->setStatusCode(201);
    }

    public function show(HittaData $hittaData): HittaDataResource
    {
        return new HittaDataResource($hittaData);
    }

    public function update(UpdateHittaDataRequest $request, HittaData $hittaData): HittaDataResource
    {
        $hittaData->update($request->validated());

        return new HittaDataResource($hittaData);
    }

    public function destroy(HittaData $hittaData): JsonResponse
    {
        $hittaData->delete();

        return response()->json(['message' => 'Record deleted successfully'], 200);
    }
}
