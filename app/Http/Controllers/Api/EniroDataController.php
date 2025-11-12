<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEniroDataRequest;
use App\Http\Requests\UpdateEniroDataRequest;
use App\Http\Resources\EniroDataResource;
use App\Models\EniroData;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EniroDataController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = EniroData::query();

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

        return EniroDataResource::collection($records);
    }

    public function store(StoreEniroDataRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (! empty($data['personnamn'])) {
            $existing = EniroData::query()
                ->where('personnamn', $data['personnamn'])
                ->first();

            if ($existing) {
                $existing->update($data);

                return (new EniroDataResource($existing))
                    ->response()
                    ->setStatusCode(200);
            }
        }

        $record = EniroData::create($data);

        return (new EniroDataResource($record))
            ->response()
            ->setStatusCode(201);
    }

    public function show(EniroData $eniroData): EniroDataResource
    {
        return new EniroDataResource($eniroData);
    }

    public function update(UpdateEniroDataRequest $request, EniroData $eniroData): EniroDataResource
    {
        $eniroData->update($request->validated());

        return new EniroDataResource($eniroData);
    }

    public function destroy(EniroData $eniroData): JsonResponse
    {
        $eniroData->delete();

        return response()->json(['message' => 'Record deleted successfully'], 200);
    }

    public function bulkStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'records' => 'required|array|min:1|max:100',
            'records.*.personnamn' => 'nullable|string',
            'records.*.alder' => 'nullable|string',
            'records.*.gatuadress' => 'nullable|string',
            'records.*.postnummer' => 'nullable|string',
            'records.*.postort' => 'nullable|string',
            'records.*.telefon' => 'nullable|string',
            'records.*.karta' => 'nullable|string',
            'records.*.link' => 'nullable|string',
            'records.*.bostadstyp' => 'nullable|string',
            'records.*.is_active' => 'nullable|boolean',
            'records.*.is_telefon' => 'nullable|boolean',
            'records.*.is_ratsit' => 'nullable|boolean',
        ]);

        $created = 0;
        $updated = 0;
        $failed = 0;
        $errors = [];

        foreach ($validated['records'] as $index => $recordData) {
            try {
                if (! empty($recordData['personnamn'])) {
                    $record = EniroData::updateOrCreate(
                        ['personnamn' => $recordData['personnamn']],
                        $recordData
                    );

                    if ($record->wasRecentlyCreated) {
                        $created++;
                    } else {
                        $updated++;
                    }
                } else {
                    $record = EniroData::create($recordData);
                    $created++;
                }
            } catch (Exception $e) {
                $failed++;
                $errors[] = [
                    'index' => $index,
                    'personnamn' => $recordData['personnamn'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'message' => 'Bulk operation completed',
            'summary' => [
                'total' => count($validated['records']),
                'created' => $created,
                'updated' => $updated,
                'failed' => $failed,
            ],
            'errors' => $errors,
        ]);
    }
}
