<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkUpdateMerinfoQueueRequest;
use App\Http\Requests\UpdateMerinfoQueueRequest;
use App\Http\Resources\MerinfoQueueResource;
use App\Models\MerinfoQueue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MerinfoQueueController extends Controller
{
    /**
     * List merinfo queue records with basic filtering & pagination.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = MerinfoQueue::query();

        if ($request->has('post_nummer')) {
            $needle = str_replace(' ', '', $request->get('post_nummer'));
            $query->whereRaw('REPLACE(post_nummer, " ", "") LIKE ?', ["%{$needle}%"]);
        }

        foreach ([
            'foretag_queued', 'personer_queued', 'foretag_scraped', 'personer_scraped', 'is_active',
        ] as $flag) {
            if ($request->filled($flag)) {
                $query->where($flag, $request->boolean($flag));
            }
        }

        $perPage = min((int) $request->get('per_page', 25), 100);
        $records = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return MerinfoQueueResource::collection($records);
    }

    /**
     * Show a single queue record.
     */
    public function show(int $id): JsonResponse
    {
        $record = MerinfoQueue::findOrFail($id);

        return response()->json(['data' => new MerinfoQueueResource($record)]);
    }

    /**
     * Get the first merinfo queue record where personer_queued = 1 and personer_scraped = 0
     */
    public function runPersoner(Request $request): JsonResponse
    {
        $record = MerinfoQueue::query()
            ->where('personer_queued', 1)
            ->where('personer_scraped', 0)
            ->orderBy('created_at', 'asc')
            ->first();

        if (! $record) {
            return response()->json([
                'message' => 'No records found with personer_queued = 1 and personer_scraped = 0',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'message' => 'Found record for personer processing',
            'data' => [
                'id' => $record->id,
                'post_nummer' => str_replace(' ', '', $record->post_nummer),
                'post_ort' => $record->post_ort,
                'post_lan' => $record->post_lan,
                'foretag_total' => $record->foretag_total,
                'personer_total' => $record->personer_total,
                'personer_house' => $record->personer_house,
                'foretag_phone' => $record->foretag_phone,
                'personer_phone' => $record->personer_phone,
                'foretag_saved' => $record->foretag_saved,
                'personer_saved' => $record->personer_saved,
                'foretag_queued' => $record->foretag_queued,
                'personer_queued' => $record->personer_queued,
                'foretag_scraped' => $record->foretag_scraped,
                'personer_scraped' => $record->personer_scraped,
                'is_active' => $record->is_active,
                'created_at' => $record->created_at,
                'updated_at' => $record->updated_at,
            ],
        ]);
    }

    /**
     * Bulk update merinfo queue records.
     */
    public function bulkUpdate(BulkUpdateMerinfoQueueRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $updated = 0;
        $failed = 0;
        $errors = [];

        foreach ($validated['records'] as $index => $recordData) {
            try {
                // Normalize the post_nummer to Swedish postal code format (XXX XX)
                $digitsOnly = preg_replace('/[^0-9]/', '', $recordData['post_nummer']);

                // Format as XXX XX if we have at least 5 digits
                if (strlen($digitsOnly) >= 5) {
                    $normalizedPostNummer = substr($digitsOnly, 0, 3) . ' ' . substr($digitsOnly, 3, 2);
                } else {
                    $normalizedPostNummer = $digitsOnly;
                }

                $record = MerinfoQueue::where('post_nummer', $normalizedPostNummer)->first();

                if ($record) {
                    $updateData = $recordData;
                    unset($updateData['post_nummer']); // Don't update the key field

                    $record->update($updateData);
                    $updated++;
                } else {
                    $failed++;
                    $errors[] = [
                        'index' => $index,
                        'post_nummer' => $recordData['post_nummer'],
                        'error' => 'Record not found',
                    ];
                }
            } catch (Exception $e) {
                $failed++;
                $errors[] = [
                    'index' => $index,
                    'post_nummer' => $recordData['post_nummer'] ?? 'unknown',
                    'error' => $e->getMessage(),
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
        ]);
    }

    /**
     * Update merinfo queue record by post_nummer.
     */
    public function updateByPostNummer(UpdateMerinfoQueueRequest $request, string $postNummer): JsonResponse
    {
        // Normalize the post_nummer to Swedish postal code format (XXX XX)
        $digitsOnly = preg_replace('/[^0-9]/', '', $postNummer);

        // Format as XXX XX if we have at least 5 digits
        if (strlen($digitsOnly) >= 5) {
            $normalizedPostNummer = substr($digitsOnly, 0, 3) . ' ' . substr($digitsOnly, 3, 2);
        } else {
            $normalizedPostNummer = $digitsOnly;
        }

        $validated = $request->validated();

        $record = MerinfoQueue::where('post_nummer', $normalizedPostNummer)->firstOrFail();

        $record->update($validated);

        return response()->json([
            'message' => 'MerinfoQueue record updated successfully',
            'data' => new MerinfoQueueResource($record),
        ]);
    }
}
