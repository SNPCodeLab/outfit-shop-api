<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Return admin traffic monitoring & stats dashboard metrics.
     *
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        $today = Carbon::today();
        $sevenDaysAgo = Carbon::now()->subDays(7);

        // Requests Today
        $requestsToday = ApiLog::where('created_at', '>=', $today)->count();

        // Requests last 7 days by date
        $requestsLast7Days = ApiLog::where('created_at', '>=', $sevenDaysAgo)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        // Top 10 Endpoints
        $topEndpoints = ApiLog::select('path', 'method', DB::raw('COUNT(*) as count'))
            ->groupBy('path', 'method')
            ->orderBy('count', 'DESC')
            ->limit(10)
            ->get();

        // Requests by User
        $requestsByUser = ApiLog::select('user_id', DB::raw('COUNT(*) as count'))
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderBy('count', 'DESC')
            ->limit(10)
            ->get();

        // Error Count (HTTP status >= 400)
        $errorCount = ApiLog::where('status', '>=', 400)->count();

        // 20 Recent Requests
        $recentRequests = ApiLog::orderBy('id', 'DESC')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => [
                'requests_today'       => $requestsToday,
                'requests_last_7_days' => $requestsLast7Days,
                'top_endpoints'        => $topEndpoints,
                'requests_by_user'     => $requestsByUser,
                'error_count'          => $errorCount,
                'recent_requests'      => $recentRequests,
            ],
        ]);
    }
}
