<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PostNummerForetagQueue;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostNummerForetagQueueController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PostNummerForetagQueue::query();

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
        $record = PostNummerForetagQueue::findOrFail($id);

        return response()->json(['data' => $record]);
    }

    public function runForetag(Request $request): JsonResponse
    {
        $record = PostNummerForetagQueue::query()
            ->where('merinfo_foretag_total', '>', 0)
            ->orderBy('created_at', 'asc')
            ->first();

        if (! $record) {
            return response()->json([
                'message' => 'No records found for postnummer foretag processing',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'message' => 'Found record for postnummer foretag processing',
            'data' => [
                'id' => $record->id,
                'post_nummer' => str_replace(' ', '', $record->post_nummer),
                'post_ort' => $record->post_ort,
                'post_lan' => $record->post_lan,
                'merinfo_foretag_total' => $record->merinfo_foretag_total,
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
            'records.*.merinfo_foretag_total' => 'sometimes|integer|min:0',
            'records.*.merinfo_foretag_saved' => 'sometimes|integer|min:0',
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

                $record = PostNummerForetagQueue::where('post_nummer', $normalizedPostNummer)->first();

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
            'merinfo_foretag_total' => 'sometimes|integer|min:0',
            'merinfo_foretag_saved' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $record = PostNummerForetagQueue::where('post_nummer', $normalizedPostNummer)->firstOrFail();

        $record->update($validated);

        return response()->json([
            'message' => 'PostNummerForetagQueue record updated successfully',
            'data' => $record,
        ]);
    }
}
