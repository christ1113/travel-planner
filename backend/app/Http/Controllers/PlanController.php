<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\User;

class PlanController extends Controller
{
    //抓此使用者所有計畫(GET /plan/{user_id})
    public function index($user_id)
    {
        $user = User::find($user_id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $plans = Plan::where('user_id', $user_id)
        ->orderBy('updated_at', 'desc')
        ->select('plan_id','plan_title', 'created_at', 'updated_at')
        ->get();

        return response()->json($plans);
    }
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
    //更新計畫 (PUT /plan/{plan_id})
    public function update(Request $request, $plan_id){
        $plan = Plan::find($plan_id);
        if (!$plan) {
            return response()->json(['message' => 'Plan not found'], 404);
        }

        $validated = $request->validate([
            'plan_title' => 'required|string'
        ]);

        $plan->update([
            'plan_title' => $validated['plan_title']
        ]);

        return response()->json($plan, 200);
    }

}
