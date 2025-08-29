<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Journey;
use Illuminate\Http\JsonResponse;

class JourneyController extends Controller
{
    //新增行程(POST /journeys)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'plan_id'        => 'required|integer|exists:plans,plan_id',
            'date'           => 'required|date',
            'time'           => 'nullable|date_format:H:i',
            'journey_title'  => 'nullable|string|max:255',
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

    //抓此計畫所有行程(GET /journeys/{plan_id})
    public function index($plan_id)
    {
        $journeys = Journey::where('plan_id',$plan_id)->get();
        
        return response()->json($journeys);
    }

    //抓此使用者所有行程(GET /journeys)
    public function userJourneys(Request $request)
    {
        $user = $request->user();
        $journeys = Journey::whereHas('plan', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->get();

        return response()->json($journeys);
    }

    //更新單一行程(PUT /journeys/{journey_id})
    public function updateJourney(Request $request, $journey_id)
    {
        $journey = Journey::where('journey_id', $journey_id)->first();
        if (!$journey) {
            return response()->json(['message' => 'Journey not found.'], 404);
        }

        $validated = $request->validate([
            'date'           => 'required|date',
            'time'           => 'nullable|date_format:H:i',
            'journey_title'  => 'nullable|string|max:255',
            'links'          => 'nullable|array',
            'image'          => 'nullable|string|max:255',
            'notes'          => 'nullable|string',
        ]);

        $validated['links'] = $validated['links'] ?? [];

        $journey->update([
            'date'           => $validated['date'],
            'time'           => $validated['time'],
            'journey_title'  => $validated['journey_title'],
            'links'          => $validated['links'],
            'image'          => $validated['image'] ?? null,
            'notes'          => $validated['notes'] ?? null,
        ]);

        return response()->json($journey, 200);
    }
    //刪除單一行程 (DELETE /journeys/{journey})
    public function destroy(Journey $journey): JsonResponse
    {
        $journey->delete();
        return response()->json(['ok' => true]);
    }
}
