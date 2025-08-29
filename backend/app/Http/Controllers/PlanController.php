<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\User;
use App\Models\Journey;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;


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
    //刪除計畫 (DELETE /plan/{plan})
    public function destroy(Plan $plan): JsonResponse
    {
        $plan->delete();
        return response()->json(['ok' => true]);
    }
    //刪除不在前端清單內的行程
    public function keepJourneys(Request $request, Plan $plan)
    {
        $data = $request->validate([
            'keep_ids'   => 'array|required',
            'keep_ids.*' => 'integer',
        ]);

        DB::transaction(function () use ($plan, $data) {
            $ids = $data['keep_ids'];
            Journey::where('plan_id', $plan->plan_id)
                ->when(!empty($ids), fn($q) => $q->whereNotIn('journey_id', $ids))
                ->delete();

            // 當 keep_ids 為空陣列時，刪掉這個 plan 的所有 journeys
            if (empty($ids)) {
                Journey::where('plan_id', $plan->plan_id)->delete();
            }
        });

        return response()->json(['ok' => true]);
    }
}
