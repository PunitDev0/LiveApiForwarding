<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;
use Illuminate\Routing\Controller;

class LPGController extends Controller
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

    public function fetchLPGOperator(Request $request)
    {
        try {
            $validated = $request->validate([
                'mode' => 'nullable|string'
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorisedkey' => base64_decode($this->secretKey),
                'Token' => $jwtToken,
                'accept' => 'application/json'
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/bill-payment/lpg/getoperator', [
                'mode' => $validated['mode'] ?? null
            ]);

            $responseData = $response->json();

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ], 200);
            }

            throw new \Exception($responseData['message'] ?? 'Failed to fetch LPG operators');
        } catch (\Exception $e) {
            Log::error('Failed to fetch LPG operators: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch LPG operators: ' . $e->getMessage()
            ], 500);
        }
    }

    public function fetchLPGDetails(Request $request)
    {
        try {
            $validated = $request->validate([
                'operator' => 'required|string',
                'canumber' => 'required|string',
                'referenceid' => 'required|string',
                'ad1' => 'nullable|string',
                'ad2' => 'nullable|string',
                'ad3' => 'nullable|string',
                'ad4' => 'nullable|string'
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Token' => $jwtToken,
                'Authorisedkey' => base64_decode($this->secretKey),
                'accept' => 'application/json'
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/bill-payment/lpg/fetchbill', [
                'operator' => $validated['operator'],
                'canumber' => $validated['canumber'],
                'referenceid' => $validated['referenceid'],
                'latitude' => '28.65521', // Hardcoded as per original
                'longitude' => '77.14343', // Hardcoded as per original
                'ad1' => $validated['ad1'] ?? null,
                'ad2' => $validated['ad2'] ?? null,
                'ad3' => $validated['ad3'] ?? null,
                'ad4' => $validated['ad4'] ?? null
            ]);

            $responseData = $response->json();

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ], 200);
            }

            throw new \Exception($responseData['message'] ?? 'Failed to fetch LPG bill details');
        } catch (\Exception $e) {
            Log::error('Failed to fetch LPG bill details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch LPG bill details: ' . $e->getMessage()
            ], 500);
        }
    }

    public function payLpgBill(Request $request)
    {
        try {
            $validated = $request->validate([
                'canumber' => 'required|string',
                'referenceid' => 'required|string',
                'amount' => 'required|numeric|min:1',
                'operator' => 'required|string',
                'ad1' => 'nullable|numeric',
                'ad2' => 'nullable|numeric',
                'ad3' => 'nullable|numeric'
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorisedkey' => base64_decode($this->secretKey),
                'Token' => $jwtToken
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/bill-payment/lpg/paybill', [
                'canumber' => $validated['canumber'],
                'referenceid' => $validated['referenceid'],
                'amount' => $validated['amount'],
                'operator' => $validated['operator'],
                'ad1' => $validated['ad1'] ?? 22, // Default as per original
                'ad2' => $validated['ad2'] ?? 458, // Default as per original
                'ad3' => $validated['ad3'] ?? 16336200, // Default as per original
                'latitude' => 27.2232, // Hardcoded as per original
                'longitude' => 78.26535 // Hardcoded as per original
            ]);

            $responseData = $response->json();

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ], 200);
            }

            throw new \Exception($responseData['message'] ?? 'Failed to process LPG bill payment');
        } catch (\Exception $e) {
            Log::error('Failed to process LPG bill payment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process LPG bill payment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getLPGStatus(Request $request)
    {
        try {
            $validated = $request->validate([
                'referenceid' => 'required|string|max:255'
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Token' => $jwtToken,
                'Authorisedkey' => base64_decode($this->secretKey),
                'Accept' => 'application/json'
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/bill-payment/lpg/status', [
                'referenceid' => $validated['referenceid']
            ]);

            $responseData = $response->json();

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ], 200);
            }

            throw new \Exception($responseData['message'] ?? 'Failed to fetch LPG status');
        } catch (\Exception $e) {
            Log::error('Failed to fetch LPG status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch LPG status: ' . $e->getMessage()
            ], 500);
        }
    }
}