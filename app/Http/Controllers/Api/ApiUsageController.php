<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiUsageLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// This controller handles API usage logs. It allows authenticated users to:
// - View their full API usage history (index)
// - View a summary of their API usage (summary) including:
//   * Today's call count
//   * This month's call count
//   * Monthly aggregation for the current year (for charts)
//   * Endpoint breakdown (grouped by endpoint + method)
// The logs are stored in the database and help users monitor their API usage patterns.
class ApiUsageController extends Controller
{
    /**
     * Return all raw API usage logs for the authenticated user.
     */
    public function index(Request $request)
    {
        $logs = ApiUsageLog::where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'API usage fetched successfully',
            'data'    => $logs,
        ]);
    }

    /**
     * Return an aggregated summary of API usage for the authenticated user:
     * - today_calls: number of calls made today
     * - month_calls: number of calls made this month
     * - monthly_breakdown: calls per month for the current year (for chart)
     * - endpoint_breakdown: top endpoints grouped by endpoint + method with call counts
     * - success_rate: percentage of calls with 2xx status codes
     */
    public function summary(Request $request)
    {
        $userId = $request->user()->id;
        $now    = Carbon::now();

        // Today's calls
        $todayCalls = ApiUsageLog::where('user_id', $userId)
            ->whereDate('called_at', $now->toDateString())
            ->count();

        // This month's calls
        $monthCalls = ApiUsageLog::where('user_id', $userId)
            ->whereYear('called_at', $now->year)
            ->whereMonth('called_at', $now->month)
            ->count();

        // Monthly breakdown for the current year
        // Using EXTRACT(MONTH FROM ...) for PostgreSQL compatibility (Supabase)
        $monthlyRaw = ApiUsageLog::where('user_id', $userId)
            ->whereYear('called_at', $now->year)
            ->select(
                DB::raw('EXTRACT(MONTH FROM called_at)::integer as month'),
                DB::raw('COUNT(*) as calls')
            )
            ->groupBy(DB::raw('EXTRACT(MONTH FROM called_at)'))
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $monthlyBreakdown = collect(range(1, 12))->map(function ($m) use ($monthlyRaw, $monthLabels) {
            return [
                'month' => $monthLabels[$m - 1],
                'calls' => $monthlyRaw->has($m) ? (int) $monthlyRaw[$m]->calls : 0,
            ];
        })->values();

        // Endpoint breakdown (top 10 by call count)
        $endpointBreakdown = ApiUsageLog::where('user_id', $userId)
            ->select(
                'endpoint',
                'method',
                DB::raw('COUNT(*) as calls'),
                DB::raw('SUM(CASE WHEN status_code >= 200 AND status_code < 300 THEN 1 ELSE 0 END) as success_count')
            )
            ->groupBy('endpoint', 'method')
            ->orderByDesc('calls')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                $errorRate = $row->calls > 0
                    ? round((1 - $row->success_count / $row->calls) * 100, 1)
                    : 0;
                return [
                    'endpoint'   => $row->endpoint,
                    'method'     => $row->method,
                    'calls'      => (int) $row->calls,
                    'error_rate' => $errorRate . '%',
                ];
            });

        // Overall success rate
        $totalCalls   = ApiUsageLog::where('user_id', $userId)->count();
        $successCalls = ApiUsageLog::where('user_id', $userId)
            ->where('status_code', '>=', 200)
            ->where('status_code', '<', 300)
            ->count();
        $successRate  = $totalCalls > 0 ? round(($successCalls / $totalCalls) * 100, 1) : 100;

        return response()->json([
            'success' => true,
            'message' => 'API usage summary fetched successfully',
            'data'    => [
                'today_calls'        => $todayCalls,
                'month_calls'        => $monthCalls,
                'total_calls'        => $totalCalls,
                'success_rate'       => $successRate,
                'monthly_breakdown'  => $monthlyBreakdown,
                'endpoint_breakdown' => $endpointBreakdown,
            ],
        ]);
    }
}
