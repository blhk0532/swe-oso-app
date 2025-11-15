<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostNummerApiRequest;
use App\Http\Requests\UpdatePostNummerApiRequest;
use App\Http\Resources\PostNummerApiResource;
use App\Models\PostNummerApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PostNummerApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = PostNummerApi::query();

        // Filter by post_nummer
        if ($request->has('post_nummer')) {
            $query->where('post_nummer', 'like', "%{$request->post_nummer}%");
        }

        // Filter by post_ort
        if ($request->has('post_ort')) {
            $query->where('post_ort', 'like', "%{$request->post_ort}%");
        }

        // Filter by post_lan
        if ($request->has('post_lan')) {
            $query->where('post_lan', 'like', "%{$request->post_lan}%");
        }

        $perPage = min($request->get('per_page', 25), 100);
        $records = $query->paginate($perPage);

        return PostNummerApiResource::collection($records);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostNummerApiRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Upsert by post_nummer when provided
        if (! empty($data['post_nummer'])) {
            $existing = PostNummerApi::query()
                ->where('post_nummer', $data['post_nummer'])
                ->first();

            if ($existing) {
                $existing->update($data);

                return (new PostNummerApiResource($existing))
                    ->response()
                    ->setStatusCode(200);
            }
        }

        $record = PostNummerApi::create($data);

        return (new PostNummerApiResource($record))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(PostNummerApi $postNummerApi): PostNummerApiResource
    {
        return new PostNummerApiResource($postNummerApi);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostNummerApiRequest $request, PostNummerApi $postNummerApi): PostNummerApiResource
    {
        $postNummerApi->update($request->validated());

        return new PostNummerApiResource($postNummerApi);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PostNummerApi $postNummerApi): JsonResponse
    {
        $postNummerApi->delete();

        return response()->json(['message' => 'Record deleted successfully'], 200);
    }

    /**
     * Update post nummer record by postal code.
     * Handles formats: "555 55", "55555", "555%2055"
     */
    public function updateByPostnummer(Request $request, string $postnummer): JsonResponse
    {
        $validated = $request->validate([
            'post_ort' => 'nullable|string',
            'post_lan' => 'nullable|string',
            'total_count' => 'nullable|integer',
            'count' => 'nullable|integer',
            'phone' => 'nullable|integer',
            'house' => 'nullable|integer',
            'bolag' => 'nullable|integer',
            'foretag' => 'nullable|integer',
            'personer' => 'nullable|integer',
            'merinfo_personer' => 'nullable|integer',
            'merinfo_foretag' => 'nullable|integer',
            'platser' => 'nullable|integer',
            'status' => 'nullable|string',
            'progress' => 'nullable|integer',
            'is_pending' => 'nullable|boolean',
            'is_complete' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'last_processed_page' => 'nullable|integer',
            'processed_count' => 'nullable|integer',
        ]);

        // Normalize postal code: decode URL encoding and handle different formats
        $normalizedPostnummer = urldecode($postnummer);

        // Try to find with the exact format first (with space if provided)
        $record = PostNummerApi::where('post_nummer', $normalizedPostnummer)->first();

        // If not found and doesn't contain space, try with space (555 55 format)
        if (! $record && ! str_contains($normalizedPostnummer, ' ') && strlen($normalizedPostnummer) === 5) {
            $withSpace = substr($normalizedPostnummer, 0, 3) . ' ' . substr($normalizedPostnummer, 3);
            $record = PostNummerApi::where('post_nummer', $withSpace)->first();
        }

        // If not found and contains space, try without space (55555 format)
        if (! $record && str_contains($normalizedPostnummer, ' ')) {
            $withoutSpace = str_replace(' ', '', $normalizedPostnummer);
            $record = PostNummerApi::where('post_nummer', $withoutSpace)->first();
        }

        if (! $record) {
            return response()->json([
                'message' => 'Post nummer not found',
                'post_nummer' => $postnummer,
                'searched_formats' => [
                    'original' => $postnummer,
                    'normalized' => $normalizedPostnummer,
                ],
            ], 404);
        }

        $record->update($validated);

        return response()->json([
            'message' => 'Post nummer updated successfully',
            'data' => new PostNummerApiResource($record),
        ], 200);
    }

    /**
     * Bulk update post nummer records by postal codes.
     * Expects array of objects with postnummer and fields to update.
     */
    public function bulkUpdateByPostnummer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'records' => 'required|array|min:1|max:100',
            'records.*.postnummer' => 'required|string',
            'records.*.post_ort' => 'nullable|string',
            'records.*.post_lan' => 'nullable|string',
            'records.*.total_count' => 'nullable|integer',
            'records.*.count' => 'nullable|integer',
            'records.*.phone' => 'nullable|integer',
            'records.*.house' => 'nullable|integer',
            'records.*.bolag' => 'nullable|integer',
            'records.*.foretag' => 'nullable|integer',
            'records.*.personer' => 'nullable|integer',
            'records.*.merinfo_personer' => 'nullable|integer',
            'records.*.merinfo_foretag' => 'nullable|integer',
            'records.*.platser' => 'nullable|integer',
            'records.*.status' => 'nullable|string',
            'records.*.progress' => 'nullable|integer',
            'records.*.is_pending' => 'nullable|boolean',
            'records.*.is_complete' => 'nullable|boolean',
            'records.*.is_active' => 'nullable|boolean',
            'records.*.last_processed_page' => 'nullable|integer',
            'records.*.processed_count' => 'nullable|integer',
        ]);

        $updated = 0;
        $failed = 0;
        $errors = [];

        foreach ($validated['records'] as $index => $recordData) {
            $postnummer = $recordData['postnummer'];
            unset($recordData['postnummer']); // Remove postnummer from update data

            // Normalize postal code: decode URL encoding and handle different formats
            $normalizedPostnummer = urldecode($postnummer);

            // Try to find with the exact format first (with space if provided)
            $record = PostNummerApi::where('post_nummer', $normalizedPostnummer)->first();

            // If not found and doesn't contain space, try with space (555 55 format)
            if (! $record && ! str_contains($normalizedPostnummer, ' ') && strlen($normalizedPostnummer) === 5) {
                $withSpace = substr($normalizedPostnummer, 0, 3) . ' ' . substr($normalizedPostnummer, 3);
                $record = PostNummerApi::where('post_nummer', $withSpace)->first();
            }

            // If not found and contains space, try without space (55555 format)
            if (! $record && str_contains($normalizedPostnummer, ' ')) {
                $withoutSpace = str_replace(' ', '', $normalizedPostnummer);
                $record = PostNummerApi::where('post_nummer', $withoutSpace)->first();
            }

            if ($record) {
                $record->update($recordData);
                $updated++;
            } else {
                $failed++;
                $errors[] = [
                    'index' => $index,
                    'postnummer' => $postnummer,
                    'error' => 'Post nummer not found',
                ];
            }
        }

        return response()->json([
            'message' => 'Bulk update completed',
            'summary' => [
                'total' => count($validated['records']),
                'updated' => $updated,
                'failed' => $failed,
            ],
            'errors' => $errors,
        ], 200);
    }

    /**
     * Bulk update merinfo totals for post nummer records.
     */
    public function bulkUpdateTotals(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'records' => 'required|array|min:1|max:100',
            'records.*.postnummer' => 'required|string',
            'records.*.merinfo_personer_total' => 'nullable|integer',
            'records.*.merinfo_foretag_total' => 'nullable|integer',
        ]);

        $updated = 0;
        $failed = 0;
        $errors = [];

        foreach ($validated['records'] as $index => $recordData) {
            $postnummer = $recordData['postnummer'];
            unset($recordData['postnummer']); // Remove postnummer from update data

            // Normalize postal code: decode URL encoding and handle different formats
            $normalizedPostnummer = urldecode($postnummer);

            // Try to find with the exact format first (with space if provided)
            $record = PostNummerApi::where('post_nummer', $normalizedPostnummer)->first();

            // If not found and doesn't contain space, try with space (555 55 format)
            if (! $record && ! str_contains($normalizedPostnummer, ' ') && strlen($normalizedPostnummer) === 5) {
                $withSpace = substr($normalizedPostnummer, 0, 3) . ' ' . substr($normalizedPostnummer, 3);
                $record = PostNummerApi::where('post_nummer', $withSpace)->first();
            }

            // If not found and contains space, try without space (55555 format)
            if (! $record && str_contains($normalizedPostnummer, ' ')) {
                $withoutSpace = str_replace(' ', '', $normalizedPostnummer);
                $record = PostNummerApi::where('post_nummer', $withoutSpace)->first();
            }

            if ($record) {
                $record->update($recordData);
                $updated++;
            } else {
                $failed++;
                $errors[] = [
                    'index' => $index,
                    'postnummer' => $postnummer,
                    'error' => 'Post nummer not found',
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
        ], 200);
    }

    /**
     * Safely increment counters for a post nummer (handles script interruptions)
     */
    public function incrementCounters(Request $request, string $postnummer): JsonResponse
    {
        $validated = $request->validate([
            'counters' => 'required|array',
            'counters.count' => 'nullable|integer|min:0',
            'counters.phone' => 'nullable|integer|min:0',
            'counters.house' => 'nullable|integer|min:0',
            'counters.bolag' => 'nullable|integer|min:0',
            'counters.foretag' => 'nullable|integer|min:0',
            'counters.personer' => 'nullable|integer|min:0',
            'counters.personer_house' => 'nullable|integer|min:0',
            'counters.platser' => 'nullable|integer|min:0',
            'counters.processed_count' => 'nullable|integer|min:0',
            'counters.merinfo_personer' => 'nullable|integer|min:0',
            'counters.merinfo_foretag' => 'nullable|integer|min:0',
            'last_processed_page' => 'nullable|integer|min:0',
            'progress' => 'nullable|integer|min:0|max:100',
            'status' => 'nullable|string',
            'is_pending' => 'nullable|boolean',
            'is_complete' => 'nullable|boolean',
        ]);

        // Find the record
        $record = $this->findPostNummerRecord($postnummer);
        if (! $record) {
            return response()->json([
                'message' => 'Post nummer not found',
                'post_nummer' => $postnummer,
            ], 404);
        }

        // Increment counters atomically
        if (isset($validated['counters'])) {
            foreach ($validated['counters'] as $field => $amount) {
                if ($amount > 0) {
                    $record->incrementCounter($field, $amount);
                }
            }
        }

        // Update other fields
        $updateData = array_filter([
            'last_processed_page' => $validated['last_processed_page'] ?? null,
            'progress' => $validated['progress'] ?? null,
            'status' => $validated['status'] ?? null,
            'is_pending' => $validated['is_pending'] ?? null,
            'is_complete' => $validated['is_complete'] ?? null,
        ], fn ($value) => $value !== null);

        if (! empty($updateData)) {
            $record->update($updateData);
        }

        return response()->json([
            'message' => 'Counters updated successfully',
            'data' => new PostNummerApiResource($record->fresh()),
        ], 200);
    }

    /**
     * Get resume information for a post nummer
     */
    public function getResumeInfo(Request $request, string $postnummer): JsonResponse
    {
        $record = $this->findPostNummerRecord($postnummer);
        if (! $record) {
            return response()->json([
                'message' => 'Post nummer not found',
                'post_nummer' => $postnummer,
            ], 404);
        }

        return response()->json([
            'message' => 'Resume info retrieved',
            'data' => $record->getResumeInfo(),
        ], 200);
    }

    /**
     * Reset counters for a fresh start
     */
    public function resetCounters(Request $request, string $postnummer): JsonResponse
    {
        $record = $this->findPostNummerRecord($postnummer);
        if (! $record) {
            return response()->json([
                'message' => 'Post nummer not found',
                'post_nummer' => $postnummer,
            ], 404);
        }

        $record->resetCounters();

        return response()->json([
            'message' => 'Counters reset successfully',
            'data' => new PostNummerApiResource($record->fresh()),
        ], 200);
    }

    /**
     * Helper method to find post nummer record with flexible matching
     */
    private function findPostNummerRecord(string $postnummer): ?PostNummerApi
    {
        // Normalize the post_nummer to Swedish postal code format (XXX XX)
        $digitsOnly = preg_replace('/[^0-9]/', '', $postnummer);

        // Try to find with the exact format first (with space if provided)
        $record = PostNummerApi::where('post_nummer', urldecode($postnummer))->first();

        // If not found and doesn't contain space, try with space (555 55 format)
        if (! $record && ! str_contains(urldecode($postnummer), ' ') && strlen($digitsOnly) === 5) {
            $withSpace = substr($digitsOnly, 0, 3) . ' ' . substr($digitsOnly, 3);
            $record = PostNummerApi::where('post_nummer', $withSpace)->first();
        }

        // If not found and contains space, try without space (55555 format)
        if (! $record && str_contains(urldecode($postnummer), ' ')) {
            $withoutSpace = str_replace(' ', '', urldecode($postnummer));
            $record = PostNummerApi::where('post_nummer', $withoutSpace)->first();
        }

        return $record;
    }
}
