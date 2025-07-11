<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rating;

class RatingController extends Controller
{
    public function store(Request $request)
    {
        $this->validate($request, [
            'report_id' => 'required|integer',
            'rating'    => 'required|integer|min:1|max:5',
            'comment'   => 'nullable|string',
        ]);

        $supervisorId = $request->get('user_id');

        // Cek apakah supervisor sudah memberi rating untuk report ini
        $existing = Rating::where('report_id', $request->report_id)
                          ->where('supervisor_id', $supervisorId)
                          ->first();

        if ($existing) {
            return response()->json(['message' => 'Rating already exists'], 409);
        }

        $rating = Rating::create([
            'report_id'     => $request->report_id,
            'supervisor_id' => $supervisorId,
            'rating'        => $request->rating,
            'comment'       => $request->comment,
        ]);

        return response()->json([
            'message' => 'Rating created',
            'data'    => $rating,
        ], 201);
    }

    public function index(Request $request)
    {
        $supervisorId = $request->get('user_id');

        $ratings = Rating::where('supervisor_id', $supervisorId)->get();

        return response()->json([
            'data' => $ratings
        ]);
    }

    public function show($report_id)
    {
        $request = request();
        $supervisorId = $request->get('user_id');

        $rating = Rating::where('report_id', $report_id)
                        ->where('supervisor_id', $supervisorId)
                        ->first();

        if (! $rating) {
            return response()->json(['message' => 'Rating not found'], 404);
        }

        return response()->json(['data' => $rating]);
    }

    public function update($report_id)
    {
        $request = request();
        $supervisorId = $request->get('user_id');

        $this->validate($request, [
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $rating = Rating::where('report_id', $report_id)
                        ->where('supervisor_id', $supervisorId)
                        ->first();

        if (! $rating) {
            return response()->json(['message' => 'Rating not found'], 404);
        }

        $rating->rating  = $request->rating;
        $rating->comment = $request->comment;
        $rating->save();

        return response()->json([
            'message' => 'Rating updated',
            'data'    => $rating,
        ]);
    }

    public function destroy($report_id)
    {
        $request = request();
        $supervisorId = $request->get('user_id');

        $rating = Rating::where('report_id', $report_id)
                        ->where('supervisor_id', $supervisorId)
                        ->first();

        if (! $rating) {
            return response()->json(['message' => 'Rating not found'], 404);
        }

        $rating->delete();

        return response()->json(['message' => 'Rating deleted']);
    }
}
