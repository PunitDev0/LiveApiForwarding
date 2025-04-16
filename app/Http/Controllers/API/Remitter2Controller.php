<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;
use Illuminate\Routing\Controller;

class Remitter2Controller extends Controller
{
    private $partnerId;
    private $secretKey;

    public function __construct()
    {
        $this->partnerId = env('PAYSPRINT_PARTNER_ID', 'PS005962');
        $this->secretKey = env('PAYSPRINT_SECRET_KEY', 'UFMwMDU5NjJjYzE5Y2JlYWY1OGRiZjE2ZGI3NThhN2FjNDFiNTI3YTE3NDA2NDkxMzM=');
    }

    private function generateJwtToken($requestId)
    {
        $timestamp = time();
        $payload = [
            'timestamp' => $timestamp,
            'partnerId' => $this->partnerId,
            'reqid' => $requestId
        ];

        return JWT::encode($payload, base64_decode($this->secretKey), 'HS256');
    }

    public function queryRemitter(Request $request)
    {
        try {

            // Log incoming request data for debugging
            Log::info('Incoming request data', [
                'all' => $request->all(),
                'input' => $request->input(),
                'mobile' => $request->input('mobile'),
                'headers' => $request->headers->all(),
                'content_type' => $request->header('Content-Type')
            ]);

            // Check if the request is JSON
            if (!$request->isJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Content-Type. Expected application/json'
                ], 400);
            }

            // Validate the mobile number
            $validated = $request->validate([
                'mobile' => 'required|digits:10'
            ]);

            // Generate request ID and JWT token
            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            // Log JWT token
            Log::info('Generated JWT Token', ['token' => $jwtToken]);

            // Make HTTP request to the external API
            $response = Http::withHeaders([
                'Token' => $jwtToken,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                // 'Authorisedkey' => base64_decode($this->secretKey)
            ])->post('https://api.paysprint.in/api/v1/service/dmt-v2/remitter/queryremitter', [
                'mobile' => $validated['mobile']
            ]);

            // Log raw API response
            Log::info('External API Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            // Parse the response
            $responseData = $response->json() ?? ['message' => 'Invalid response from API'];

            // Check if the response is successful
            if ($response->successful()) {
                Log::info('Remitter query successful', [
                    'mobile' => $validated['mobile'],
                    'jwt_token' => $jwtToken,
                    'response_status' => $responseData['status'] ?? 'unknown'
                ]);

                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ], 200);
            }

            // Throw an exception if the response is not successful
            throw new \Exception($responseData['message'] ?? 'Failed to query remitter');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error: ' . $e->getMessage(), [
                'errors' => $e->errors()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Remitter query error: ' . $e->getMessage(), [
                'mobile' => $request->input('mobile') ?? 'unknown'
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to query remitter: ' . $e->getMessage(),
                'ip' => $request->ip(),
                'request' => $request->all(),
                'responseData' => $response->json()
            ], 500);
        }
    }

    public function verifyAadhaar(Request $request)
    {
        try {
            // Log incoming request data for debugging
            Log::info('Incoming request data', [
                'all' => $request->all(),
                'input' => $request->input(),
                'mobile' => $request->input('mobile'),
                'aadhaar_no' => $request->input('aadhaar_no'),
                'headers' => $request->headers->all(),
                'content_type' => $request->header('Content-Type')
            ]);

            // Check if the request is JSON
            if (!$request->isJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Content-Type. Expected application/json'
                ], 400);
            }

            // Validate the request
            $validated = $request->validate([
                'mobile' => 'required|digits:10',
                'aadhaar_no' => 'required|digits:12'
            ]);

            // Generate request ID and JWT token
            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            // Log JWT token
            Log::info('Generated JWT Token', ['token' => $jwtToken]);

            // Make HTTP request to the external API
            $response = Http::withHeaders([
                'Token' => $jwtToken,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorisedkey' => base64_decode($this->secretKey)
            ])->post('https://api.paysprint.in/api/v1/service/dmt-v2/remitter/queryremitter/aadhar_verify', [
                'mobile' => $validated['mobile'],
                'aadhaar_no' => $validated['aadhaar_no']
            ]);

            // Log raw API response
            Log::info('External API Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            // Parse the response
            $responseData = $response->json() ?? ['message' => 'Invalid response from API'];

            // Check if the response is successful
            if ($response->successful()) {
                Log::info('Aadhaar verification successful', [
                    'mobile' => $validated['mobile'],
                    'aadhaar_no' => $validated['aadhaar_no'],
                    'jwt_token' => $jwtToken,
                    'response_status' => $responseData['status'] ?? 'unknown'
                ]);

                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ], 200);
            }

            // Throw an exception if the response is not successful
            throw new \Exception($responseData['message'] ?? 'Failed to verify Aadhaar');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error: ' . $e->getMessage(), [
                'errors' => $e->errors()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Aadhaar verification error: ' . $e->getMessage(), [
                'mobile' => $request->input('mobile') ?? 'unknown',
                'aadhaar_no' => $request->input('aadhaar_no') ?? 'unknown'
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify Aadhaar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function registerRemitter(Request $request)
    {
        try {
            // Log incoming request data for debugging
            Log::info('Incoming request data', [
                'all' => $request->all(),
                'input' => $request->input(),
                'mobile' => $request->input('mobile'),
                'otp' => $request->input('otp'),
                'stateresp' => $request->input('stateresp'),
                'headers' => $request->headers->all(),
                'content_type' => $request->header('Content-Type')
            ]);

            // Check if the request is JSON
            if (!$request->isJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Content-Type. Expected application/json'
                ], 400);
            }

            // Validate the request
            $validated = $request->validate([
                'mobile' => 'required|digits:10',
                'otp' => 'required|string',
                'stateresp' => 'required|string',
                'data' => 'nullable|string',
                'accessmode' => 'nullable|string',
                'is_iris' => 'nullable|integer'
            ]);

            // Generate request ID and JWT token
            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            // Log JWT token
            Log::info('Generated JWT Token', ['token' => $jwtToken]);

            // Make HTTP request to the external API
            $response = Http::withHeaders([
                'Token' => $jwtToken,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorisedkey' => base64_decode($this->secretKey)
            ])->post('https://api.paysprint.in/api/v1/service/dmt-v2/remitter/registerremitter', [
                'mobile' => $validated['mobile'],
                'otp' => $validated['otp'],
                'stateresp' => $validated['stateresp'],
                'data' => $validated['data'] ?? null,
                'accessmode' => $validated['accessmode'] ?? null,
                'is_iris' => $validated['is_iris'] ?? null
            ]);

            // Log raw API response
            Log::info('External API Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            // Parse the response
            $responseData = $response->json() ?? ['message' => 'Invalid response from API'];

            // Check if the response is successful
            if ($response->successful()) {
                Log::info('Remitter registration successful', [
                    'mobile' => $validated['mobile'],
                    'jwt_token' => $jwtToken,
                    'response_status' => $responseData['status'] ?? 'unknown'
                ]);

                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ], 200);
            }

            // Throw an exception if the response is not successful
            throw new \Exception($responseData['message'] ?? 'Failed to register remitter');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error: ' . $e->getMessage(), [
                'errors' => $e->errors()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Remitter registration failed: ' . $e->getMessage(), [
                'mobile' => $request->input('mobile') ?? 'unknown'
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to register remitter: ' . $e->getMessage()
            ], 500);
        }
    }
}