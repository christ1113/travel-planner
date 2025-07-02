<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plan;

class PlanController extends Controller
{
    //新增計畫(POST /plan)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'plan_title' => 'required|string'
        ]);

        $plan = Plan::create([
            'user_id' => auth()->id(),
            'plan_title' => $validated['plan_title']
        ]);

        return response()->json($plan,201);
    }
}
