<?php

namespace App\Http\Controllers\API;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;
use Illuminate\Routing\Controller;

class FastagRechargeController extends Controller
{
    private $partnerId = 'PS001568';
    private $secretKey = 'Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=';

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

    public function fetchOperatorList(Request $request)
    {
        try {
            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'accept' => 'application/json',
                'Token' => $jwtToken,
                'Authorisedkey' => base64_decode($this->secretKey)
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/fastag/Fastag/operatorsList');

            $responseData = $response->json();

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ], 200);
            }

            throw new \Exception($responseData['message'] ?? 'Failed to fetch operator list');
        } catch (\Exception $e) {
            Log::error('Failed to fetch operator list: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch operator list: ' . $e->getMessage()
            ], 500);
        }
    }

    public function fetchConsumerDetails(Request $request)
    {
        try {
            $validated = $request->validate([
                'operator' => 'required|integer',
                'canumber' => 'required|string'
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorisedkey' => base64_decode($this->secretKey),
                'Token' => $jwtToken
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/fastag/Fastag/fetchConsumerDetails', [
                'operator' => $validated['operator'],
                'canumber' => $validated['canumber']
            ]);

            $responseData = $response->json();

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ], 200);
            }

            throw new \Exception($responseData['message'] ?? 'Failed to fetch consumer details');
        } catch (\Exception $e) {
            Log::error('Failed to fetch consumer details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch consumer details: ' . $e->getMessage()
            ], 500);
        }
    }

    public function recharge(Request $request)
    {
        try {
            $validated = $request->validate([
                'operator' => 'required|integer',
                'canumber' => 'required|string',
                'amount' => 'required|numeric|min:1',
                'referenceid' => 'required|string'
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorisedkey' => base64_decode($this->secretKey),
                'Token' => $jwtToken
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/fastag/Fastag/recharge', [
                'operator' => $validated['operator'],
                'canumber' => $validated['canumber'],
                'amount' => $validated['amount'],
                'referenceid' => $validated['referenceid']
            ]);

            $responseData = $response->json();

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ], 200);
            }

            throw new \Exception($responseData['message'] ?? 'Failed to process recharge');
        } catch (\Exception $e) {
            Log::error('Failed to process recharge: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process recharge: ' . $e->getMessage()
            ], 500);
        }
    }

    public function fetchStatus(Request $request)
    {
        try {
            $validated = $request->validate([
                'referenceid' => 'required|string'
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorisedkey' => base64_decode($this->secretKey),
                'Token' => $jwtToken
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/fastag/Fastag/status', [
                'referenceid' => $validated['referenceid']
            ]);

            $responseData = $response->json();

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ], 200);
            }

            throw new \Exception($responseData['message'] ?? 'Failed to fetch status');
        } catch (\Exception $e) {
            Log::error('Failed to fetch status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch status: ' . $e->getMessage()
            ], 500);
        }
    }
}