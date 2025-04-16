<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;

class VerifyApiKeyAndIP
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-KEY');
        $requestIp = $request->ip(); // gets client IP
        if (!$apiKey) {
            return response()->json(['error' => 'API key is missing'], 401);
        }

        $record = DB::table('api_credentials')->where('api_key', $apiKey)->first();
        $recordIP = DB::table('api_whitelisted_ips')->where('ip', $requestIp)->first();

        if (!$record) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        // if (!$recordIP) {
        //     return response()->json(['error' => 'IP address not allowed'], 403);
        // }

        return $next($request);
    }
}
