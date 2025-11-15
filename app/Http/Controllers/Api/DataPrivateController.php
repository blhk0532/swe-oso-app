<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDataPrivateRequest;
use App\Http\Requests\UpdateDataPrivateRequest;
use App\Http\Resources\DataPrivateResource;
use App\Models\DataPrivate;
use Exception;
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

        // Accept both modern and legacy (bo_/ps_) filter keys used in tests/clients
        if ($request->has('postnummer') || $request->has('bo_postnummer')) {
            $val = $request->get('postnummer', $request->get('bo_postnummer'));
            $query->where('postnummer', 'like', "%{$val}%")->orWhere('bo_postnummer', 'like', "%{$val}%");
        }

        if ($request->has('postort') || $request->has('bo_postort')) {
            $val = $request->get('postort', $request->get('bo_postort'));
            $query->where('postort', 'like', "%{$val}%")->orWhere('bo_postort', 'like', "%{$val}%");
        }

        if ($request->has('kommun') || $request->has('bo_kommun')) {
            $val = $request->get('kommun', $request->get('bo_kommun'));
            $query->where('kommun', 'like', "%{$val}%")->orWhere('bo_kommun', 'like', "%{$val}%");
        }

        if ($request->has('lan') || $request->has('bo_lan')) {
            $val = $request->get('lan', $request->get('bo_lan'));
            $query->where('lan', 'like', "%{$val}%")->orWhere('bo_lan', 'like', "%{$val}%");
        }

        if ($request->has('personnummer') || $request->has('ps_personnummer')) {
            $val = $request->get('personnummer', $request->get('ps_personnummer'));
            $query->where('personnummer', 'like', "%{$val}%")->orWhere('ps_personnummer', 'like', "%{$val}%");
        }

        if ($request->has('personnamn') || $request->has('ps_personnamn')) {
            $val = $request->get('personnamn', $request->get('ps_personnamn'));
            $query->searchByName($val);
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

    /**
     * Bulk insert/update records.
     */
    public function bulkStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'records' => 'required|array|min:1|max:100',
            'records.*.personnummer' => 'nullable|string',
            'records.*.personnamn' => 'nullable|string',
            'records.*.gatuadress' => 'nullable|string',
            'records.*.postnummer' => 'nullable|string',
            'records.*.postort' => 'nullable|string',
            'records.*.kommun' => 'nullable|string',
            'records.*.lan' => 'nullable|string',
            'records.*.is_active' => 'nullable|boolean',
        ]);

        $created = 0;
        $updated = 0;
        $failed = 0;
        $errors = [];

        foreach ($validated['records'] as $index => $recordData) {
            try {
                if (! empty($recordData['personnummer'])) {
                    $record = DataPrivate::updateOrCreate(
                        ['personnummer' => $recordData['personnummer']],
                        $recordData
                    );

                    if ($record->wasRecentlyCreated) {
                        $created++;
                    } else {
                        $updated++;
                    }
                } else {
                    $record = DataPrivate::create($recordData);
                    $created++;
                }
            } catch (Exception $e) {
                $failed++;
                $errors[] = [
                    'index' => $index,
                    'personnummer' => $recordData['personnummer'] ?? 'unknown',
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
