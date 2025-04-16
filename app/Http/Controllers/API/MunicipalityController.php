<?php

namespace App\Http\Controllers\API;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;
use Illuminate\Routing\Controller;

class MunicipalityController extends Controller
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

    public function fetchMunicipalityOperator(Request $request)
    {
        try {
            $validated = $request->validate([
                'mode' => 'nullable|string'
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Token' => $jwtToken,
                'Authorisedkey' => base64_decode($this->secretKey),
                'accept' => 'application/json'
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/bill-payment/municipality/getoperator', [
                'mode' => $validated['mode'] ?? null
            ]);

            $responseData = $response->json();

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ], 200);
            }

            throw new \Exception($responseData['message'] ?? 'Failed to fetch municipality operators');
        } catch (\Exception $e) {
            Log::error('Failed to fetch municipality operators: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch municipality operators: ' . $e->getMessage()
            ], 500);
        }
    }

    public function fetchBillDetails(Request $request)
    {
        try {
            $validated = $request->validate([
                'canumber' => 'required|string',
                'operator' => 'required|string'
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorisedkey' => base64_decode($this->secretKey),
                'Token' => $jwtToken,
                'accept' => 'application/json'
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/bill-payment/municipality/fetchbill', [
                'canumber' => $validated['canumber'],
                'operator' => $validated['operator']
            ]);

            $responseData = $response->json();

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ], 200);
            }

            throw new \Exception($responseData['message'] ?? 'Failed to fetch bill details');
        } catch (\Exception $e) {
            Log::error('Failed to fetch bill details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch bill details: ' . $e->getMessage()
            ], 500);
        }
    }

    public function payMunicipalityBill(Request $request)
    {
        try {
            $validated = $request->validate([
                'canumber' => 'required|numeric',
                'operator' => 'required|integer',
                'amount' => 'required|numeric|min:1',
                'ad1' => 'required|integer',
                'ad2' => 'required|integer',
                'ad3' => 'required|numeric',
                'referenceid' => 'required|integer',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric'
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Token' => $jwtToken,
                'accept' => 'application/json',
                'Authorisedkey' => base64_decode($this->secretKey)
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/bill-payment/municipality/paybill', $validated);

            $responseData = $response->json();

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ], 200);
            }

            throw new \Exception($responseData['message'] ?? 'Failed to process payment');
        } catch (\Exception $e) {
            Log::error('Failed to process payment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function fetchMunicipalityStatus(Request $request)
    {
        try {
            $validated = $request->validate([
                'referenceid' => 'required|string'
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Token' => $jwtToken,
                'accept' => 'application/json',
                'Authorisedkey' => base64_decode($this->secretKey)
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/bill-payment/municipality/status', [
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