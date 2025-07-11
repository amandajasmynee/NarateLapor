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
            'status' => 'in:draft,submitted',
        ]);

        $userId = $request->get('user_id');

        $report = Report::where('id', $id)->where('user_id', $userId)->first();

        if (! $report) {
            return response()->json(['message' => 'Report not found'], 404);
        }

        $report->title  = $request->title;
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
                'created_at' => $report->created_at,
                'updated_at' => $report->updated_at,
            ]
        ]);
    }

    public function all(Request $request)
    {
        $this->validate($request, [
            'intern_id' => 'required|integer',
            'status'    => 'nullable|in:draft,submitted,reviewed,revised',
        ]);

        $query = Report::where('user_id', $request->query('intern_id'));

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        $reports = $query->get()->map(function ($report) {
            return [
                'id'         => $report->id,
                'user_id'    => $report->user_id,
                'title'      => $report->title,
                'content'    => $report->content,
                'status'     => $report->status,
                'created_at' => $report->created_at,
                'updated_at' => $report->updated_at,
            ];
        });

        return response()->json(['data' => $reports]);
    }

    public function show(Request $request, $id)
    {
        $userId = $request->get('user_id');

        $report = Report::where('id', $id)->where('user_id', $userId)->first();

        if (! $report) {
            return response()->json(['message' => 'Report not found'], 404);
        }

        return response()->json([
            'data' => [
                'id'         => $report->id,
                'user_id'    => $report->user_id,
                'title'      => $report->title,
                'content'    => $report->content,
                'status'     => $report->status,
                'created_at' => $report->created_at,
                'updated_at' => $report->updated_at,
            ]
        ]);
    }

    public function review(Request $request, $id)
    {
        $this->validate($request, [
            'status' => 'required|in:reviewed,revised',
        ]);

        $report = Report::find($id);

        if (! $report) {
            return response()->json(['message' => 'Report not found'], 404);
        }

        // Optional: batasi hanya yang status saat ini masuk akal untuk direview ulang
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
                'created_at' => $report->created_at,
                'updated_at' => $report->updated_at,
            ]
        ]);
    }
}
