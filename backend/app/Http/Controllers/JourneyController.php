<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Journey;

class JourneyController extends Controller
{
    //新增行程(POST /journey)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'plan_id'        => 'required|integer|exists:plans,id',
            'date'           => 'required|date',
            'time'           => 'required|date_format:H:i:s',
            'journey_title'  => 'required|string|max:255',
            'links'          => 'nullable|array',
            'image'          => 'nullable|string|max:255',
            'notes'          => 'nullable|string',
        ]);

        // 如果 links 是 null，存空陣列
        $validated['links'] = $validated['links'] ?? [];

        $journey = Journey::create([
            'plan_id'        => $validated['plan_id'],
            'date'           => $validated['date'],
            'time'           => $validated['time'],
            'journey_title'  => $validated['journey_title'],
            'links'          => $validated['links'],
            'image'          => $validated['image'] ?? null,
            'notes'          => $validated['notes'] ?? null,
        ]);

        return response()->json($journey,201);
    }
}
