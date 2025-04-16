<?php

namespace App\Http\Controllers\API;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;

// use Illuminate\Routing\Controller;

class Beneficiary2Controller extends Controller
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

    public function registerBeneficiary(Request $request)
    {
        try {
            $validated = $request->validate([
                'mobile' => 'required|string', 
                'benename' => 'required|string',
                'bankid' => 'required|string',
                'accno' => 'required|string',
                'ifsccode' => 'required|string',
                'verified' => 'required|string',
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Token' => $jwtToken,
                'accept' => 'application/json',
                'Content-Type' => 'application/json',
                'User-Agent' => $this->partnerId
            ])->post('https://api.paysprint.in/api/v1/service/dmt-v2/beneficiary/registerbeneficiary', [
                'mobile' => $request->mobile,
                'benename' => $request->benename,
                'bankid' => $request->bankid,
                'accno' => $request->accno,
                'ifsccode' => $request->ifsccode,
                'verified' => $request->verified,
            ]);

            $responseData = $response->json();

            if ($response->successful() && isset($responseData['data'])) {
                return response()->json([
                    'success' => true,
                    'data' => $responseData,
                ], 200);
            }

            Log::error('API returned unsuccessful response', [
                'response' => $responseData,
                'status' => $response->status()
            ]);

            return response()->json([
                'success' => false,
                'error' => $responseData['message'] ?? 'Failed to register beneficiary',
            ], $response->status());
        } catch (\Exception $e) {
            Log::error('Error registering beneficiary: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to register beneficiary: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function fetchBeneficiary(Request $request)
    {
        try {
            $validated = $request->validate([
                'mobile' => 'required|string|size:10',
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Token' => $jwtToken,
                'accept' => 'application/json',
            ])->post('https://api.paysprint.in/api/v1/service/dmt-v2/beneficiary/registerbeneficiary/fetchbeneficiary', [
                'mobile' => $validated['mobile'],
            ]);

            $responseData = $response->json();

            if ($response->successful() && $responseData['status'] === true && !empty($responseData['data'])) {
                return response()->json([
                    'success' => true,
                    'data' => $responseData['data'],
                ], 200);
            }

            return response()->json([
                'success' => false,
                'error' => $responseData['message'] ?? 'No beneficiaries found',
            ], $response->status());
        } catch (\Exception $e) {
            Log::error('Error fetching beneficiary: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch beneficiary: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroyBeneficiary(Request $request)
    {
        try {
            $validated = $request->validate([
                'mobile' => 'required|digits:10',
                'bene_id' => 'required|string',
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Token' => $jwtToken,
            ])->post('https://api.paysprint.in/api/v1/service/dmt-v2/beneficiary/registerbeneficiary/deletebeneficiary', [
                'mobile' => $validated['mobile'],
                'bene_id' => $validated['bene_id'],
            ]);

            $responseData = $response->json();

            return response()->json([
                'success' => $responseData['status'] ?? false,
                'data' => $responseData,
            ], $response->status());
        } catch (\Exception $e) {
            Log::error('Error deleting beneficiary: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete beneficiary: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function fetchBeneficiaryData(Request $request)
    {
        try {
            $validated = $request->validate([
                'mobile' => 'required|string|size:10',
                'beneid' => 'required|string',
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Token' => $jwtToken,
                'accept' => 'application/json',
            ])->post('https://api.paysprint.in/api/v1/service/dmt-v2/beneficiary/registerbeneficiary/fetchbeneficiarybybeneid', [
                'mobile' => $validated['mobile'],
                'beneid' => $validated['beneid'],
            ]);

            $responseData = $response->json();

            return response()->json([
                'success' => true,
                'data' => $responseData,
            ], $response->status());
        } catch (\Exception $e) {
            Log::error('Error fetching beneficiary data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch beneficiary data: ' . $e->getMessage(),
            ], 500);
        }
    }
}