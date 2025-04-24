<?php

namespace App\Http\Controllers\API;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;
use Illuminate\Routing\Controller;

class InsuranceController extends Controller
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

    public function fetchLICBill(Request $request)
    {
        try {
            $validated = $request->validate([
                'canumber' => 'required|numeric',
                'ad1' => 'required|email',
                'ad2' => 'required|date',
                'mode' => 'required|in:online'
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Token' => $jwtToken,
                'accept' => 'application/json',
                'content-type' => 'application/json'
            ])->post('https://api.paysprint.in/api/v1/service/bill-payment/bill/fetchlicbill', [
                'canumber' => $validated['canumber'],
                'ad1' => $validated['ad1'],
                'ad2' => $validated['ad2'],
                'mode' => $validated['mode']
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

    public function payInsuranceBill(Request $request)
    {
        try {
            $validated = $request->validate([
                'canumber' => 'required|string',
                'amount' => 'required|numeric|min:1',
                'referenceid' => 'required|string',
                'bill_fetch' => 'required|array',
                'bill_fetch.billNumber' => 'required|string',
                'bill_fetch.billAmount' => 'required|string',
                'bill_fetch.billnetamount' => 'required|string',
                'bill_fetch.billdate' => 'required|string',
                'bill_fetch.acceptPayment' => 'required|boolean',
                'bill_fetch.acceptPartPay' => 'required|boolean',
                'bill_fetch.cellNumber' => 'required|string',
                'bill_fetch.dueFrom' => 'required|string',
                'bill_fetch.dueTo' => 'required|string',
                'bill_fetch.validationId' => 'required|string',
                'bill_fetch.billId' => 'required|string'
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Token' => $jwtToken,
                'accept' => 'application/json',
                'content-type' => 'application/json'
            ])->post('https://api.paysprint.in/api/v1/service/bill-payment/bill/paylicbill', [
                'canumber' => $validated['canumber'],
                'mode' => 'online',
                'amount' => $validated['amount'],
                'ad1' => 'nitesh@rnfiservices.com', // Hardcoded as per original
                'ad2' => 'DD/MM/YYYY', // Hardcoded as per original
                'ad3' => 'HGAYV15E560507155', // Hardcoded as per original
                'referenceid' => $validated['referenceid'],
                'latitude' => 27.2232, // Hardcoded as per original
                'longitude' => 78.26535, // Hardcoded as per original
                'bill_fetch' => $validated['bill_fetch']
            ]);

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

    public function fetchInsuranceStatus(Request $request)
    {
        try {
            $validated = $request->validate([
                'referenceid' => 'required|string'
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Token' => $jwtToken,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->post('https://api.paysprint.in/api/v1/service/bill-payment/bill/licstatus', [
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