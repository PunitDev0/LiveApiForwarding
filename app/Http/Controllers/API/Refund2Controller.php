<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;
use Illuminate\Routing\Controller;

class Refund2Controller extends Controller
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

    public function refundOtp(Request $request)
    {
        try {
            $validated = $request->validate([
                'referenceid' => 'required|string',
                'ackno' => 'required|string'
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Authorisedkey' => base64_decode($this->secretKey),
                'Token' => $jwtToken,
                'accept' => 'application/json',
                'content-type' => 'application/json'
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/dmt-v2/refund/refund/resendotp', [
                'referenceid' => $validated['referenceid'],
                'ackno' => $validated['ackno']
            ]);

            $responseData = $response->json();

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ], 200);
            }

            throw new \Exception($responseData['message'] ?? 'Failed to process refund OTP');
        } catch (\Exception $e) {
            Log::error('Failed to process refund OTP: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process refund OTP: ' . $e->getMessage()
            ], 500);
        }
    }

    public function processRefund(Request $request)
    {
        try {
            $validated = $request->validate([
                'ackno' => 'required|string',
                'referenceid' => 'required|string',
                'otp' => 'required|string'
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Authorisedkey' => base64_decode($this->secretKey),
                'Content-Type' => 'application/json',
                'Token' => $jwtToken,
                'accept' => 'application/json'
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/dmt-v2/refund/refund', [
                'ackno' => $validated['ackno'],
                'referenceid' => $validated['referenceid'],
                'otp' => $validated['otp']
            ]);

            $responseData = $response->json();

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ], 200);
            }

            throw new \Exception($responseData['message'] ?? 'Failed to process refund');
        } catch (\Exception $e) {
            Log::error('Failed to process refund: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process refund: ' . $e->getMessage()
            ], 500);
        }
    }
}