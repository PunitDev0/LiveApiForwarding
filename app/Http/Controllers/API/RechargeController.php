<?php

namespace App\Http\Controllers\API;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\RechargeTransaction;
use App\Models\RechargeOperator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;
use Illuminate\Routing\Controller;

class RechargeController extends Controller
{
    private $partnerId = 'PS005962'; 
    private $secretKey = 'UFMwMDU5NjJjYzE5Y2JlYWY1OGRiZjE2ZGI3NThhN2FjNDFiNTI3YTE3NDA2NDkxMzM=';

    // Method to generate JWT token
    private function generateJwtToken($requestId)
    {
        $timestamp = time();
        $payload = [
            'timestamp' => $timestamp,
            'partnerId' => $this->partnerId,
            'reqid' => $requestId
        ];

        return Jwt::encode(
            $payload,
            $this->secretKey,
            'HS256' // Using HMAC SHA-256 algorithm
        );
    }

    // Process recharge request
    public function processRecharge(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'operator' => 'required|numeric',
                'canumber' => 'required|string',
                'amount' => 'required|numeric|min:1'
            ]);

            if ($validator->fails()) {
                Log::error('Validation failed:', $validator->errors()->toArray());
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Generate unique reference ID and JWT token
            $referenceId = 'RECH' . time() . rand(1000, 9999);
            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            // Make API call
            $response = Http::withHeaders([
                'Token' => $jwtToken,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'User-Agent' => $this->partnerId
            ])->post('https://api.paysprint.in/api/v1/service/recharge/recharge/dorecharge', [
                'operator' => (int)$request->operator,
                'canumber' => $request->canumber,
                'amount' => (int)$request->amount,
                'referenceid' => $referenceId
            ]);

            $responseData = $response->json();

            // Log the API response
            Log::info('Recharge processed:', [
                'referenceid' => $referenceId,
                'jwt_token' => $jwtToken,
                'response' => $responseData
            ]);

           

            // Return API response
            return response()->json($responseData);

        } catch (\Exception $e) {
            Log::error('Recharge processing failed: ' . $e->getMessage(), [
                'stack_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to process recharge: ' . $e->getMessage()
            ], 500);
        }
    }

    // Fetch recharge status
    public function fetchRechargeStatus(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'referenceid' => 'required|string'
            ]);

            if ($validator->fails()) {
                Log::error('Validation failed:', $validator->errors()->toArray());
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Generate unique request ID and JWT token
            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            // Make API call
            $response = Http::withHeaders([
                'Token' => $jwtToken,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'User-Agent' => $this->partnerId
            ])->post('https://api.paysprint.in/api/v1/service/recharge/recharge/status', [
                'referenceid' => $request->referenceid
            ]);

            $responseData = $response->json();

            // Log the status check
            Log::info('Recharge status checked:', [
                'referenceid' => $request->referenceid,
                'jwt_token' => $jwtToken,
                'response' => $responseData
            ]);

            return response()->json($responseData);

        } catch (\Exception $e) {
            Log::error('Failed to fetch recharge status: ' . $e->getMessage(), [
                'stack_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch recharge status: ' . $e->getMessage()
            ], 500);
        }
    }

    // Fetch operators
    public function getOperators()
    {
        try {
            // Generate unique request ID and JWT token
            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            // Make API call
            $response = Http::withHeaders([
                'Token' => $jwtToken,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'User-Agent' => $this->partnerId
            ])->post('https://api.paysprint.in/api/v1/service/recharge/recharge/getoperator');

            $responseData = $response->json();

            // Save to database if the API call is successful
            if (isset($responseData['status']) && $responseData['status'] === true) {
                $this->saveOperatorsToDatabase($responseData['data'] ?? []);
            }

            // Log the operator fetch
            Log::info('Operators fetched successfully:', [
                'jwt_token' => $jwtToken,
                'response_status' => $responseData['status'] ?? 'unknown',
                'response_data' => $responseData
            ]);

            return response()->json($responseData);

        } catch (\Exception $e) {
            Log::error('Failed to fetch operators: ' . $e->getMessage(), [
                'stack_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch operators: ' . $e->getMessage()
            ], 500);
        }
    }

}