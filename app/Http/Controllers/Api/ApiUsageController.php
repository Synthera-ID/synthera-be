<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiUsageLog;
use Illuminate\Http\Request;

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