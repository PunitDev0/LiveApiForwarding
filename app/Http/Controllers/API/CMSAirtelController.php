<?php

namespace App\Http\Controllers\API;

use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Routing\Controller;

class CMSAirtelController extends Controller
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

        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    public function generateUrl(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'refid' => 'required|string|max:50',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Token' => $jwtToken,
                'accept' => 'application/json',
                'Content-Type' => 'application/json',
                'User-Agent' => $this->partnerId
            ])->post('https://api.paysprint.in/api/v1/service/airtelcms/V2/airtel/index', [
                'refid' => $request->refid,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);

            $responseData = $response->json();

            Log::info('Generate URL API Call', [
                'url' => 'https://api.paysprint.in/api/v1/service/airtelcms/V2/airtel/index',
                'payload' => [
                    'refid' => $request->refid,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                ],
                'response' => $responseData
            ]);

            return response()->json($responseData);
        } catch (\Exception $e) {
            Log::error('Generate URL Failed: ' . $e->getMessage(), [
                'stack_trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch API response: ' . $e->getMessage()
            ], 500);
        }
    }

    public function airtelTransactionEnquiry(Request $request)
    {
        if ($request->isMethod('post')) {
            $validator = Validator::make($request->all(), [
                'refid' => 'required|string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            try {
                $requestId = time() . rand(1000, 9999);
                $jwtToken = $this->generateJwtToken($requestId);

                $response = Http::withHeaders([
                    'Token' => $jwtToken,
                    'accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'User-Agent' => $this->partnerId
                ])->post('https://api.paysprint.in/api/v1/service/airtelcms/airtel/status', [
                    'refid' => $request->refid,
                ]);

                $responseData = $response->json();

                Log::info('Transaction Enquiry API Call', [
                    'url' => 'https://api.paysprint.in/api/v1/service/airtelcms/airtel/status',
                    'payload' => [
                        'refid' => $request->refid,
                    ],
                    'response' => $responseData
                ]);

                return response()->json($responseData);
            } catch (\Exception $e) {
                Log::error('Transaction Enquiry Failed: ' . $e->getMessage(), [
                    'stack_trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to fetch transaction status: ' . $e->getMessage()
                ], 500);
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'Method not allowed'
        ], 405);
    }
}