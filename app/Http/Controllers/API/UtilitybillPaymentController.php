<?php

namespace App\Http\Controllers\API;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;
use Illuminate\Routing\Controller;

class UtilitybillPaymentController extends Controller
{
    private $partnerId = 'PS005962';
    private $secretKey = 'UFMwMDU5NjJjYzE5Y2JlYWY1OGRiZjE2ZGI3NThhN2FjNDFiNTI3YTE3NDA2NDkxMzM=';

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
            $validated = $request->validate([
                'mode' => 'required|in:online,offline'
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Authorisedkey' => base64_decode($this->secretKey),
                'Token' => $jwtToken,
                'accept' => 'application/json',
                'content-type' => 'application/json'
            ])->post('https://api.paysprint.in/api/v1/service/bill-payment/bill/getoperator', [
                'mode' => $validated['mode']
            ]);

            $responseData = $response->json();

            if ($response->successful()) {
                Log::info('Operator list fetched successfully', [
                    'request_id' => $requestId,
                    'response_status' => $responseData['status'] ?? 'unknown'
                ]);

                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ], 200);
            }

            throw new \Exception($responseData['message'] ?? 'Failed to fetch operator list');
        } catch (\Exception $e) {
            Log::error('Operator list error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch operator list: ' . $e->getMessage()
            ], 500);
        }
    }

    public function fetchBillDetails(Request $request)
    {
        try {
            $validated = $request->validate([
                'operator' => 'required|numeric',
                'canumber' => 'required|numeric',
                'mode' => 'required|in:online,offline'
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Authorisedkey' => base64_decode($this->secretKey),
                'Token' => $jwtToken,
                'accept' => 'application/json',
                'content-type' => 'application/json'
            ])->post('https://api.paysprint.in/api/v1/service/bill-payment/bill/fetchbill', [
                'operator' => $validated['operator'],
                'canumber' => $validated['canumber'],
                'mode' => $validated['mode']
            ]);

            $responseData = $response->json();

            if ($response->successful()) {
                Log::info('Bill details fetched successfully', [
                    'request_id' => $requestId,
                    'response_status' => $responseData['status'] ?? 'unknown'
                ]);

                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ], 200);
            }

            throw new \Exception($responseData['message'] ?? 'Failed to fetch bill details');
        } catch (\Exception $e) {
            Log::error('Bill details error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch bill details: ' . $e->getMessage()
            ], 500);
        }
    }

    public function processBillPayment(Request $request)
    {
        try {
            $validated = $request->validate([
                'canumber' => 'required|string|min:5',
                'amount' => 'required|numeric|min:1',
                'operator' => 'required|string'
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);
            $referenceId = 'REF' . time() . rand(1000, 9999);
            $formattedAmount = number_format($validated['amount'], 2, '.', '');

            $payload = [
                'operator' => $validated['operator'],
                'canumber' => $validated['canumber'],
                'amount' => $formattedAmount,
                'referenceid' => $referenceId,
                'latitude' => '27.2232',
                'longitude' => '78.26535',
                'mode' => 'online',
                'bill_fetch' => [
                    'billAmount' => $formattedAmount,
                    'billnetamount' => $formattedAmount,
                    'billdate' => date('d-M-Y'),
                    'dueDate' => date('Y-m-d', strtotime('+7 days')),
                    'acceptPayment' => true,
                    'acceptPartPay' => false,
                    'cellNumber' => $validated['canumber'],
                    'userName' => 'SALMAN'
                ]
            ];

            $response = Http::withHeaders([
                'Authorisedkey' => base64_decode($this->secretKey),
                'Token' => $jwtToken,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->post('https://api.paysprint.in/api/v1/service/bill-payment/bill/paybill', $payload);

            $responseData = $response->json();

            if ($response->successful()) {
                Log::info('Bill payment processed successfully', [
                    'request_id' => $requestId,
                    'reference_id' => $referenceId,
                    'response_status' => $responseData['status'] ?? 'unknown'
                ]);

                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ], 200);
            }

            throw new \Exception($responseData['message'] ?? 'Failed to process bill payment');
        } catch (\Exception $e) {
            Log::error('Bill payment error: ' . $e->getMessage(), [
                'consumer_number' => $request->input('canumber') ?? null,
                'amount' => $request->input('amount') ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process bill payment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function fetchUtilityStatus(Request $request)
    {
        try {
            $validated = $request->validate([
                'referenceid' => 'required|string'
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Authorisedkey' => base64_decode($this->secretKey),
                'Token' => $jwtToken,
                'accept' => 'application/json',
                'content-type' => 'application/json'
            ])->post('https://api.paysprint.in/api/v1/service/bill-payment/bill/status', [
                'referenceid' => $validated['referenceid']
            ]);

            $responseData = $response->json();

            if ($response->successful()) {
                Log::info('Utility status fetched successfully', [
                    'referenceid' => $validated['referenceid'],
                    'response_status' => $responseData['status'] ?? 'unknown'
                ]);

                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ], 200);
            }

            throw new \Exception($responseData['message'] ?? 'Failed to fetch utility status');
        } catch (\Exception $e) {
            Log::error('Utility status error: ' . $e->getMessage(), [
                'referenceid' => $request->input('referenceid') ?? 'unknown'
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch utility status: ' . $e->getMessage()
            ], 500);
        }
    }
}