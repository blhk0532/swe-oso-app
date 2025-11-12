<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MerinfoData;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MerinfoDataController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = MerinfoData::query();

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
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

        $perPage = min($request->get('per_page', 25), 100);
        $records = $query->paginate($perPage);

        return response()->json($records);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'personnamn' => 'nullable|string',
            'alder' => 'nullable|string',
            'kon' => 'nullable|string',
            'gatuadress' => 'nullable|string',
            'postnummer' => 'nullable|string',
            'postort' => 'nullable|string',
            'telefon' => 'nullable|array',
            'karta' => 'nullable|string',
            'link' => 'nullable|string',
            'bostadstyp' => 'nullable|string',
            'bostadspris' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'is_telefon' => 'nullable|boolean',
            'is_ratsit' => 'nullable|boolean',
            'is_hus' => 'nullable|boolean',
        ]);

        // Create new record (don't upsert since personnamn can be empty)
        $record = MerinfoData::create($validated);

        return response()->json([
            'message' => 'Record created successfully',
            'data' => $record,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $record = MerinfoData::findOrFail($id);

        return response()->json(['data' => $record]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $record = MerinfoData::findOrFail($id);

        $validated = $request->validate([
            'personnamn' => 'sometimes|string',
            'alder' => 'nullable|string',
            'kon' => 'nullable|string',
            'gatuadress' => 'nullable|string',
            'postnummer' => 'nullable|string',
            'postort' => 'nullable|string',
            'telefon' => 'nullable|array',
            'karta' => 'nullable|string',
            'link' => 'nullable|string',
            'bostadstyp' => 'nullable|string',
            'bostadspris' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'is_telefon' => 'nullable|boolean',
            'is_ratsit' => 'nullable|boolean',
            'is_hus' => 'nullable|boolean',
        ]);

        $record->update($validated);

        return response()->json([
            'message' => 'Record updated successfully',
            'data' => $record,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $record = MerinfoData::findOrFail($id);
        $record->delete();

        return response()->json(['message' => 'Record deleted successfully']);
    }

    /**
     * Bulk insert/update records.
     */
    public function bulkStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'records' => 'required|array|min:1|max:100',
            'records.*.personnamn' => 'nullable|string',
            'records.*.name' => 'nullable|string', // Alternative field name
            'records.*.alder' => 'nullable|string',
            'records.*.dob' => 'nullable|string', // Alternative field name
            'records.*.kon' => 'nullable|string',
            'records.*.gatuadress' => 'nullable|string',
            'records.*.address' => 'nullable|string', // Alternative field name
            'records.*.postnummer' => 'nullable|string',
            'records.*.zipCode' => 'nullable|string', // Alternative field name
            'records.*.postort' => 'nullable|string',
            'records.*.city' => 'nullable|string', // Alternative field name
            'records.*.telefon' => 'nullable|array',
            'records.*.phoneNumber' => 'nullable|string', // Alternative field name
            'records.*.karta' => 'nullable|string',
            'records.*.link' => 'nullable|string',
            'records.*.bostadstyp' => 'nullable|string',
            'records.*.bostadspris' => 'nullable|string',
            'records.*.is_active' => 'nullable|boolean',
            'records.*.is_telefon' => 'nullable|boolean',
            'records.*.is_ratsit' => 'nullable|boolean',
            'records.*.is_hus' => 'nullable|boolean',
        ]);

        $created = 0;
        $updated = 0;
        $failed = 0;
        $errors = [];

        foreach ($validated['records'] as $index => $recordData) {
            try {
                // Map alternative field names to standard ones
                $mappedData = $this->mapRecordFields($recordData);

                // Create records even with empty personnamn (due to privacy blocking)
                $record = MerinfoData::create($mappedData);
                $created++;

            } catch (Exception $e) {
                $failed++;
                $errors[] = [
                    'index' => $index,
                    'personnamn' => $recordData['personnamn'] ?? $recordData['name'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'message' => 'Bulk operation completed',
            'summary' => [
                'total' => count($validated['records']),
                'created' => $created,
                'failed' => $failed,
            ],
            'errors' => $errors,
        ]);
    }

    /**
     * Bulk update merinfo totals for merinfo data records.
     */
    public function bulkUpdateTotals(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'records' => 'required|array|min:1|max:100',
            'records.*.id' => 'required|integer|exists:merinfo_data,id',
            'records.*.merinfo_personer_total' => 'nullable|integer',
            'records.*.merinfo_foretag_total' => 'nullable|integer',
        ]);

        $updated = 0;
        $failed = 0;
        $errors = [];

        foreach ($validated['records'] as $index => $recordData) {
            $id = $recordData['id'];
            unset($recordData['id']); // Remove id from update data

            try {
                $record = MerinfoData::findOrFail($id);
                $record->update($recordData);
                $updated++;
            } catch (Exception $e) {
                $failed++;
                $errors[] = [
                    'index' => $index,
                    'id' => $id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'message' => 'Bulk update totals completed',
            'summary' => [
                'total' => count($validated['records']),
                'updated' => $updated,
                'failed' => $failed,
            ],
            'errors' => $errors,
        ]);
    }

    /**
     * Map alternative field names to standard database field names
     */
    private function mapRecordFields(array $recordData): array
    {
        $mapping = [
            'name' => 'personnamn',
            'dob' => 'alder',
            'address' => 'gatuadress',
            'zipCode' => 'postnummer',
            'city' => 'postort',
            'phoneNumber' => 'telefon',
        ];

        $mapped = [];
        foreach ($recordData as $key => $value) {
            $mappedKey = $mapping[$key] ?? $key;
            $mapped[$mappedKey] = $value;
        }

        return $mapped;
    }
}
