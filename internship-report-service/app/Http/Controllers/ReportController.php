<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;

class ReportController extends Controller
{
    public function store(Request $request)
    {
        $this->validate($request, [
            'title'   => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $report = Report::create([
            'user_id' => $request->get('user_id'),
            ...$request->only(['title', 'content']),
            'status' => 'draft',
        ]);

        return response()->json([
            'message' => 'Report created',
            'data' => [
                'id'         => $report->id,
                'user_id'    => $report->user_id,
                'title'      => $report->title,
                'content'    => $report->content,
                'status'     => $report->status,
                'date'       => $report->created_at->toDateString(),
                'created_at' => $report->created_at,
                'updated_at' => $report->updated_at,
            ]
        ], 201);
    }

    public function index(Request $request)
    {
        $userId = $request->get('user_id');
        $status = $request->query('status');
        $sort   = $request->query('sort', 'desc'); // default: terbaru dulu
        $page   = (int) $request->query('page', 1);
        $limit  = (int) $request->query('limit', 10);

        // Validasi nilai input
        if ($status && !in_array($status, ['draft', 'submitted'])) {
            return response()->json(['message' => 'Invalid status filter'], 400);
        }

        if (!in_array($sort, ['asc', 'desc'])) {
            return response()->json(['message' => 'Invalid sort order'], 400);
        }

        $query = Report::where('user_id', $userId);

        if ($status) {
            $query->where('status', $status);
        }

        $total = $query->count();

        $reports = $query->orderBy('created_at', $sort)
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get()
            ->map(function ($report) {
                return [
                    'id'         => $report->id,
                    'user_id'    => $report->user_id,
                    'title'      => $report->title,
                    'content'    => $report->content,
                    'status'     => $report->status,
                    'date'       => $report->created_at->toDateString(),
                    'created_at' => $report->created_at,
                    'updated_at' => $report->updated_at,
                ];
            });

        return response()->json([
            'data' => $reports,
            'meta' => [
                'total'     => $total,
                'page'      => $page,
                'limit'     => $limit,
                'last_page' => ceil($total / $limit),
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'title'  => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'in:draft,submitted',
        ]);

        $userId = $request->get('user_id');

        $report = Report::where('id', $id)->where('user_id', $userId)->first();

        if (! $report) {
            return response()->json(['message' => 'Report not found'], 404);
        }

        if (!in_array($report->status, ['draft', 'revised'])) {
            return response()->json(['message' => 'Laporan tidak bisa diedit karena status-nya sudah final.'], 403);
        }

        $report->title  = $request->title;
        $report->content = $request->content ?? $report->content;
        $report->status = $request->status ?? $report->status;
        $report->save();

        return response()->json([
            'message' => 'Report updated',
            'data' => [
                'id'         => $report->id,
                'user_id'    => $report->user_id,
                'title'      => $report->title,
                'content'    => $report->content,
                'status'     => $report->status,
                'date'       => $report->created_at->toDateString(),
                'created_at' => $report->created_at,
                'updated_at' => $report->updated_at,
            ]
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $userId = $request->get('user_id');

        $report = Report::where('id', $id)->where('user_id', $userId)->first();

        if (! $report) {
            return response()->json(['message' => 'Report not found'], 404);
        }

        $report->delete();

        return response()->json(['message' => 'Report deleted']);
    }

    public function publish(Request $request, $id)
    {
        $userId = $request->get('user_id');

        $report = Report::where('id', $id)->where('user_id', $userId)->first();

        if (! $report) {
            return response()->json(['message' => 'Report not found'], 404);
        }

        if ($report->status === 'submitted') {
            return response()->json(['message' => 'Report is already submitted'], 400);
        }

        $report->status = 'submitted';
        $report->save();

        return response()->json([
            'message' => 'Report submitted',
            'data' => [
                'id'         => $report->id,
                'user_id'    => $report->user_id,
                'title'      => $report->title,
                'content'    => $report->content,
                'status'     => $report->status,
                'date'       => $report->created_at->toDateString(),
                'created_at' => $report->created_at,
                'updated_at' => $report->updated_at,
            ]
        ]);
    }

    public function all(Request $request)
    {
        $query = Report::query();

        // Jika user memilih status tertentu
        if ($request->has('status')) {
            $allowedStatus = ['submitted', 'reviewed', 'revised', 'done'];
            $status = $request->query('status');

            if (!in_array($status, $allowedStatus)) {
                return response()->json(['message' => 'Status tidak valid'], 400);
            }

            $query->where('status', $status);
        } else {
            // Kalau tidak ada filter, ambil semua status yang valid (termasuk done!)
            $query->whereIn('status', ['submitted', 'reviewed', 'revised', 'done']);
        }

        // Filter by intern_id jika dikirim
        if ($request->has('intern_id')) {
            $query->where('user_id', $request->query('intern_id'));
        }

        $reports = $query->orderBy('created_at', 'desc')->get()->map(function ($report) {
            return [
                'id'         => $report->id,
                'user_id'    => $report->user_id,
                'title'      => $report->title,
                'content'    => $report->content,
                'status'     => $report->status,
                'date'       => $report->created_at->toDateString(),
                'created_at' => $report->created_at,
                'updated_at' => $report->updated_at,
            ];
        });

        return response()->json(['data' => $reports]);
    }

    public function show(Request $request, $id)
    {
        $userId = $request->get('user_id');
        $role   = $request->get('user_role');
        $report = \App\Models\Report::find($id);

        if (! $report) {
            return response()->json(['message' => 'Report not found'], 404);
        }

        if ($report->status === 'draft') {
            if ($role !== 'intern' || (int) $report->user_id !== (int) $userId) {
                return response()->json([
                    'message'           => 'Laporan masih draft',
                    'report_user_id'    => $report->user_id,
                    'logged_in_user_id' => $userId,
                    'role'              => $role,
                ], 403);
            }
        }

        return response()->json([
            'data' => [
                'id'         => $report->id,
                'user_id'    => $report->user_id,
                'title'      => $report->title,
                'content'    => $report->content,
                'status'     => $report->status,
                'date'       => $report->created_at->toDateString(),
                'created_at' => $report->created_at,
                'updated_at' => $report->updated_at,
            ]
        ]);
    }

    public function review(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'status' => 'required|in:reviewed,revised,done',
            ]);

            $report = Report::find($id);

            if (! $report) {
                return response()->json(['message' => 'Report not found'], 404);
            }

            // Validasi: status done hanya boleh jika sebelumnya reviewed
            if ($request->status === 'done' && $report->status !== 'reviewed') {
                return response()->json(['message' => 'Laporan hanya bisa ditandai selesai setelah direview'], 400);
            }

            if (!in_array($report->status, ['submitted', 'reviewed', 'revised'])) {
                return response()->json(['message' => 'This report cannot be reviewed'], 400);
            }

            $report->status = $request->status;
            $report->save();

            return response()->json([
                'message' => 'Report status updated',
                'data' => [
                    'id'         => $report->id,
                    'user_id'    => $report->user_id,
                    'title'      => $report->title,
                    'content'    => $report->content,
                    'status'     => $report->status,
                    'date'       => $report->created_at->toDateString(),
                    'created_at' => $report->created_at,
                    'updated_at' => $report->updated_at,
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan di server.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
