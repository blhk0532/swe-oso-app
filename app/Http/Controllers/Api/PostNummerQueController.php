<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PostNummerQue;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PostNummerQueController extends Controller
{
    /**
     * Get the first postnummer value from the queue.
     */
    public function getFirstPostNummer(Request $request): JsonResponse
    {
        // Primary: pending and active
        $record = PostNummerQue::query()
            ->where('is_pending', true)
            ->where('is_active', true)
            ->orderBy('created_at', 'asc')
            ->first();

        // Fallback 1: any pending (regardless of "is_active")
        if (! $record) {
            $record = PostNummerQue::query()
                ->where('is_pending', true)
                ->orderBy('created_at', 'asc')
                ->first();
        }

        // Fallback 2: any active (regardless of "is_pending")
        if (! $record) {
            $record = PostNummerQue::query()
                ->where('is_active', true)
                ->orderBy('created_at', 'asc')
                ->first();
        }

        if (! $record) {
            return response()->json([
                'post_nummer' => null,
            ], 404);
        }

        // Return only the postnummer as JSON (minimal payload)
        return response()->json([
            'post_nummer' => $record->post_nummer,
        ]);
    }

    /**
     * Update a postnummer que record.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $record = PostNummerQue::findOrFail($id);

        $validated = $request->validate([
            'post_nummer' => 'sometimes|string|size:5',
            'post_ort' => 'nullable|string',
            'post_lan' => 'nullable|string',
            'total_count' => 'sometimes|integer|min:0',
            'count' => 'sometimes|integer|min:0',
            'phone' => 'sometimes|integer|min:0',
            'house' => 'sometimes|integer|min:0',
            'bolag' => 'sometimes|integer|min:0',
            'foretag' => 'sometimes|integer|min:0',
            'personer' => 'sometimes|integer|min:0',
            'platser' => 'sometimes|integer|min:0',
            'status' => 'nullable|string',
            'progress' => 'sometimes|integer|min:0|max:100',
            'is_pending' => 'sometimes|boolean',
            'is_complete' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'last_processed_page' => 'nullable|integer|min:0',
            'processed_count' => 'nullable|integer|min:0',
        ]);

        $record->update($validated);

        return response()->json([
            'message' => 'PostNummerQue record updated successfully',
            'data' => $record,
        ]);
    }

    /**
     * Update postnummer que by post_nummer code.
     * If status is "success" or "complete", mark as processed and return next in queue.
     */
    public function updateByPostNummer(Request $request, string $postNummer): JsonResponse
    {
        $record = PostNummerQue::where('post_nummer', $postNummer)->firstOrFail();

        $validated = $request->validate([
            'post_ort' => 'nullable|string',
            'post_lan' => 'nullable|string',
            'total_count' => 'sometimes|integer|min:0',
            'count' => 'sometimes|integer|min:0',
            'phone' => 'sometimes|integer|min:0',
            'house' => 'sometimes|integer|min:0',
            'bolag' => 'sometimes|integer|min:0',
            'foretag' => 'sometimes|integer|min:0',
            'personer' => 'sometimes|integer|min:0',
            'platser' => 'sometimes|integer|min:0',
            'status' => 'nullable|string',
            'progress' => 'sometimes|integer|min:0|max:100',
            'is_pending' => 'sometimes|boolean',
            'is_complete' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'last_processed_page' => 'nullable|integer|min:0',
            'processed_count' => 'nullable|integer|min:0',
        ]);

        $record->update($validated);

        $response = [
            'message' => 'PostNummerQue record updated successfully',
            'data' => $record,
        ];

        // If status is success or complete, mark as processed and return next in queue
        if (isset($validated['status']) && in_array(strtolower($validated['status']), ['success', 'complete'])) {
            // Mark current record as completed and inactive
            $record->update([
                'is_pending' => false,
                'is_complete' => true,
                'is_active' => false, // Mark as inactive so it doesn't show up in future queries
                'status' => $validated['status'],
            ]);

            // Get next pending postnummer in queue
            $nextRecord = PostNummerQue::query()
                ->where('is_pending', true)
                ->where('is_active', true)
                ->where('id', '!=', $record->id) // Exclude current record
                ->orderBy('created_at', 'asc')
                ->first();

            $response['next_in_queue'] = $nextRecord ? [
                'post_nummer' => $nextRecord->post_nummer,
                'post_ort' => $nextRecord->post_ort,
                'post_lan' => $nextRecord->post_lan,
                'id' => $nextRecord->id,
                'created_at' => $nextRecord->created_at,
            ] : null;

            $response['message'] = 'PostNummerQue record marked as completed. ' .
                ($nextRecord ? 'Next record in queue retrieved.' : 'No more records in queue.');
        }

        return response()->json($response);
    }

    /**
     * Bulk update postnummer que records.
     * If status is "success" or "complete", mark records as processed.
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'records' => 'required|array|min:1|max:50',
            'records.*.post_nummer' => 'required|string|size:5',
            'records.*.post_ort' => 'nullable|string',
            'records.*.post_lan' => 'nullable|string',
            'records.*.total_count' => 'sometimes|integer|min:0',
            'records.*.count' => 'sometimes|integer|min:0',
            'records.*.phone' => 'sometimes|integer|min:0',
            'records.*.house' => 'sometimes|integer|min:0',
            'records.*.bolag' => 'sometimes|integer|min:0',
            'records.*.foretag' => 'sometimes|integer|min:0',
            'records.*.personer' => 'sometimes|integer|min:0',
            'records.*.platser' => 'sometimes|integer|min:0',
            'records.*.status' => 'nullable|string',
            'records.*.progress' => 'sometimes|integer|min:0|max:100',
            'records.*.is_pending' => 'sometimes|boolean',
            'records.*.is_complete' => 'sometimes|boolean',
            'records.*.is_active' => 'sometimes|boolean',
            'records.*.last_processed_page' => 'nullable|integer|min:0',
            'records.*.processed_count' => 'nullable|integer|min:0',
        ]);

        $updated = 0;
        $completed = 0;
        $failed = 0;
        $errors = [];

        foreach ($validated['records'] as $index => $recordData) {
            try {
                $record = PostNummerQue::where('post_nummer', $recordData['post_nummer'])->first();

                if ($record) {
                    $updateData = $recordData;
                    unset($updateData['post_nummer']); // Don't update the key field

                    // If status is success or complete, mark as processed
                    if (isset($updateData['status']) && in_array(strtolower($updateData['status']), ['success', 'complete'])) {
                        $updateData['is_pending'] = false;
                        $updateData['is_complete'] = true;
                        $updateData['is_active'] = false; // Mark as inactive
                        $completed++;
                    }

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
                'completed' => $completed,
                'failed' => $failed,
            ],
            'errors' => $errors,
        ]);
    }

    /**
     * Process (update) the next postnummer in queue without providing an id or code.
     *
     * This will find the first queued record (pending+active, then fallbacks),
     * apply the provided updates, mark it processed when status is success/complete,
     * and return the updated record plus the next_in_queue if available.
     */
    public function processNext(Request $request): JsonResponse
    {
        // Find head of queue (same logic as getFirstPostNummer)
        $record = PostNummerQue::query()
            ->where('is_pending', true)
            ->where('is_active', true)
            ->orderBy('created_at', 'asc')
            ->first();

        if (! $record) {
            $record = PostNummerQue::query()
                ->where('is_pending', true)
                ->orderBy('created_at', 'asc')
                ->first();
        }

        if (! $record) {
            $record = PostNummerQue::query()
                ->where('is_active', true)
                ->orderBy('created_at', 'asc')
                ->first();
        }

        if (! $record) {
            return response()->json([
                'message' => 'No postnummer found in queue',
                'data' => null,
                'next_in_queue' => null,
            ], 404);
        }

        // Build validation rules and include post_nummer safely (unique, ignore current)
        $table = (new PostNummerQue)->getTable();

        $rules = [
            'post_ort' => 'nullable|string',
            'post_lan' => 'nullable|string',
            'total_count' => 'sometimes|integer|min:0',
            'count' => 'sometimes|integer|min:0',
            'phone' => 'sometimes|integer|min:0',
            'house' => 'sometimes|integer|min:0',
            'bolag' => 'sometimes|integer|min:0',
            'foretag' => 'sometimes|integer|min:0',
            'personer' => 'sometimes|integer|min:0',
            'platser' => 'sometimes|integer|min:0',
            'status' => 'nullable|string',
            'progress' => 'sometimes|integer|min:0|max:100',
            'is_pending' => 'sometimes|boolean',
            'is_complete' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'last_processed_page' => 'nullable|integer|min:0',
            'processed_count' => 'nullable|integer|min:0',
        ];

        // Allow clients to change post_nummer, but enforce uniqueness and format.
        $rules['post_nummer'] = [
            'sometimes',
            'string',
            'size:5',
            Rule::unique($table, 'post_nummer')->ignore($record->id),
        ];

        $validated = $request->validate($rules);

        $response = [];

        // Perform an atomic update inside a transaction and lock the row to avoid races
        DB::transaction(function () use ($record, $validated, &$response) {
            // Reload with a lock for update where supported
            $fresh = PostNummerQue::where('id', $record->id)->lockForUpdate()->first();

            $fresh->update($validated);

            $response = [
                'message' => 'PostNummerQue record updated successfully',
                'data' => $fresh,
            ];

            // If status indicates completion, mark processed and fetch next
            if (isset($validated['status']) && in_array(strtolower($validated['status']), ['success', 'complete'])) {
                $fresh->update([
                    'is_pending' => false,
                    'is_complete' => true,
                    'is_active' => false,
                    'status' => $validated['status'],
                ]);

                $nextRecord = PostNummerQue::query()
                    ->where('is_pending', true)
                    ->where('is_active', true)
                    ->where('id', '!=', $fresh->id)
                    ->orderBy('created_at', 'asc')
                    ->first();

                $response['next_in_queue'] = $nextRecord ? [
                    'post_nummer' => $nextRecord->post_nummer,
                    'post_ort' => $nextRecord->post_ort,
                    'post_lan' => $nextRecord->post_lan,
                    'id' => $nextRecord->id,
                    'created_at' => $nextRecord->created_at,
                ] : null;

                $response['message'] = 'PostNummerQue record marked as completed. ' .
                    ($nextRecord ? 'Next record in queue retrieved.' : 'No more records in queue.');
            }
        });

        return response()->json($response);
    }

    /**
     * Mark the current first postnummer as processed and return the next in queue.
     *
     * This is a convenience endpoint that finds the head-of-queue, marks it as
     * completed (is_pending=false, is_complete=true, is_active=false) and
     * then returns the next available queued record (if any).
     */
    public function firstNext(Request $request): JsonResponse
    {
        // Find head of queue (same logic as getFirstPostNummer)
        $record = PostNummerQue::query()
            ->where('is_pending', true)
            ->where('is_active', true)
            ->orderBy('created_at', 'asc')
            ->first();

        if (! $record) {
            $record = PostNummerQue::query()
                ->where('is_pending', true)
                ->orderBy('created_at', 'asc')
                ->first();
        }

        if (! $record) {
            $record = PostNummerQue::query()
                ->where('is_active', true)
                ->orderBy('created_at', 'asc')
                ->first();
        }

        if (! $record) {
            return response()->json([
                'message' => 'No postnummer found in queue',
                'processed' => null,
                'next_in_queue' => null,
            ], 404);
        }

        $validated = $request->validate([
            'status' => 'nullable|string',
        ]);

        $response = [];

        DB::transaction(function () use ($record, $validated, &$response) {
            // Reload with a lock for update where supported
            $fresh = PostNummerQue::where('id', $record->id)->lockForUpdate()->first();

            $status = $validated['status'] ?? 'success';

            $fresh->update([
                'is_pending' => false,
                'is_complete' => true,
                'is_active' => false,
                'status' => $status,
            ]);

            $nextRecord = PostNummerQue::query()
                ->where('is_pending', true)
                ->where('is_active', true)
                ->where('id', '!=', $fresh->id)
                ->orderBy('created_at', 'asc')
                ->first();

            $response = [
                'message' => 'First record marked as completed',
                'processed' => [
                    'post_nummer' => $fresh->post_nummer,
                    'id' => $fresh->id,
                    'status' => $fresh->status,
                ],
                'next_in_queue' => $nextRecord ? [
                    'post_nummer' => $nextRecord->post_nummer,
                    'post_ort' => $nextRecord->post_ort,
                    'post_lan' => $nextRecord->post_lan,
                    'id' => $nextRecord->id,
                    'created_at' => $nextRecord->created_at,
                ] : null,
            ];
        });

        return response()->json($response);
    }
}
