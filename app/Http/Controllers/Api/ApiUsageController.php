<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiUsageLog;
use Illuminate\Http\Request;

// This controller handles API usage logs. It allows authenticated users to view their API usage history, including details such as the endpoint accessed, HTTP method used, status code returned, and the time of the request. This information can help users monitor their API usage and identify any potential issues or patterns in their interactions with the API. The logs are stored in the database and can be retrieved through this controller for analysis and tracking purposes.
class ApiUsageController extends Controller
{
    public function index(Request $request)
    {
        $logs = ApiUsageLog::where(
            'user_id',
            $request->user()->id
        )
        ->latest()
        ->get();

        return response()->json([
            'success' => true,
            'message' => 'API usage fetched successfully',
            'data' => $logs
        ]);
    }
}