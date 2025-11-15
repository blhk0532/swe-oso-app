<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RatsitForetagQueue;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RatsitForetagQueueController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = RatsitForetagQueue::query();

        if ($request->has('post_nummer')) {
            $needle = str_replace(' ', '', $request->get('post_nummer'));
            $query->whereRaw('REPLACE(post_nummer, " ", "") LIKE ?', ["%{$needle}%"]);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $perPage = min((int) $request->get('per_page', 25), 100);

        return response()->json($query->orderBy('created_at', 'desc')->paginate($perPage));
    }

    public function show(int $id): JsonResponse
    {
        $record = RatsitForetagQueue::findOrFail($id);

        return response()->json(['data' => $record]);
    }

    public function runForetag(Request $request): JsonResponse
    {
        $record = RatsitForetagQueue::query()
            ->where('foretag_queued', 1)
            ->where('foretag_scraped', 0)
            ->orderBy('created_at', 'asc')
            ->first();

        if (! $record) {
            return response()->json([
                'message' => 'No records found with foretag_queued = 1 and foretag_scraped = 0',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'message' => 'Found record for foretag processing',
            'data' => [
                'id' => $record->id,
                'post_nummer' => str_replace(' ', '', $record->post_nummer),
                'post_ort' => $record->post_ort,
                'post_lan' => $record->post_lan,
                'foretag_total' => $record->foretag_total,
                'foretag_phone' => $record->foretag_phone,
                'foretag_saved' => $record->foretag_saved,
                'foretag_queued' => $record->foretag_queued,
                'foretag_scraped' => $record->foretag_scraped,
                'is_active' => $record->is_active,
                'created_at' => $record->created_at,
                'updated_at' => $record->updated_at,
            ],
        ]);
    }

    public function bulkUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'records' => 'required|array|min:1|max:50',
            'records.*.post_nummer' => 'required|string|max:10',
            'records.*.post_ort' => 'nullable|string',
            'records.*.post_lan' => 'nullable|string',
            'records.*.foretag_total' => 'sometimes|integer|min:0',
            'records.*.foretag_phone' => 'sometimes|integer|min:0',
            'records.*.foretag_saved' => 'sometimes|integer|min:0',
            'records.*.foretag_queued' => 'sometimes|boolean',
            'records.*.foretag_scraped' => 'sometimes|boolean',
            'records.*.is_active' => 'sometimes|boolean',
        ]);

        $updated = 0;
        $failed = 0;
        $errors = [];

        foreach ($validated['records'] as $index => $recordData) {
            try {
                $digitsOnly = preg_replace('/[^0-9]/', '', $recordData['post_nummer']);

                if (strlen($digitsOnly) >= 5) {
                    $normalizedPostNummer = substr($digitsOnly, 0, 3) . ' ' . substr($digitsOnly, 3, 2);
                } else {
                    $normalizedPostNummer = $digitsOnly;
                }

                $record = RatsitForetagQueue::where('post_nummer', $normalizedPostNummer)->first();

                if ($record) {
                    $updateData = $recordData;
                    unset($updateData['post_nummer']);

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

    public function updateByPostNummer(Request $request, string $postNummer): JsonResponse
    {
        $digitsOnly = preg_replace('/[^0-9]/', '', $postNummer);

        if (strlen($digitsOnly) >= 5) {
            $normalizedPostNummer = substr($digitsOnly, 0, 3) . ' ' . substr($digitsOnly, 3, 2);
        } else {
            $normalizedPostNummer = $digitsOnly;
        }

        $validated = $request->validate([
            'post_ort' => 'nullable|string',
            'post_lan' => 'nullable|string',
            'foretag_total' => 'sometimes|integer|min:0',
            'foretag_phone' => 'sometimes|integer|min:0',
            'foretag_saved' => 'sometimes|integer|min:0',
            'foretag_queued' => 'sometimes|boolean',
            'foretag_scraped' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ]);

        $record = RatsitForetagQueue::where('post_nummer', $normalizedPostNummer)->firstOrFail();

        $record->update($validated);

        return response()->json([
            'message' => 'RatsitForetagQueue record updated successfully',
            'data' => $record,
        ]);
    }
}
