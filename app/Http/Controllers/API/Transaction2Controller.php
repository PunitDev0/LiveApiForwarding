<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;
use Illuminate\Routing\Controller;

class Transaction2Controller extends Controller
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

    public function pennyDrop(Request $request)
    {
        try {
            $validated = $request->validate([
                'mobile' => 'required|digits:10',
                'accno' => 'required|string',
                'bankid' => 'required|integer',
                'benename' => 'required|string',
                'referenceid' => 'required|string',
                'pincode' => 'required|digits:6',
                'address' => 'required|string',
                'dob' => 'required|date_format:d-m-Y',
                'gst_state' => 'required|string|max:2',
                'bene_id' => 'required|integer'
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'Authorisedkey' => base64_decode($this->secretKey),
                'Token' => $jwtToken
            ])->post('https://api.paysprint.in/api/v1/service/dmt-v2/beneficiary/registerbeneficiary/benenameverify', [
                'mobile' => $validated['mobile'],
                'accno' => $validated['accno'],
                'bankid' => $validated['bankid'],
                'benename' => $validated['benename'],
                'referenceid' => $validated['referenceid'],
                'pincode' => $validated['pincode'],
                'address' => $validated['address'],
                'dob' => $validated['dob'],
                'gst_state' => $validated['gst_state'],
                'bene_id' => $validated['bene_id']
            ]);

            $responseData = $response->json();

            if ($response->successful()) {
                Log::info('Penny drop successful', [
                    'request_data' => $validated,
                    'request_id' => $requestId,
                    'response_status' => $responseData['status'] ?? 'unknown'
                ]);

                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ], 200);
            }

            throw new \Exception($responseData['message'] ?? 'Failed to process penny drop');
        } catch (\Exception $e) {
            Log::error('Penny drop error: ' . $e->getMessage(), [
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to process penny drop: ' . $e->getMessage()
            ], 500);
        }
    }

    public function transactionSentOtp(Request $request)
    {
        try {
            $validated = $request->validate([
                'mobile' => 'required|digits:10',
                'referenceid' => 'required|string',
                'bene_id' => 'required|string',
                'txntype' => 'required|string',
                'amount' => 'required|numeric|min:1',
                'pincode' => 'required|string',
                'address' => 'required|string',
                'gst_state' => 'required|string',
                'dob' => 'required|date_format:d-m-Y',
                'lat' => 'nullable|string',
                'long' => 'nullable|string'
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'accept' => 'application/json',
                'Token' => $jwtToken,
                'Authorisedkey' => base64_decode($this->secretKey)
            ])->post('https://api.paysprint.in/api/v1/service/dmt-v2/transact/transact/send_otp', [
                'mobile' => $validated['mobile'],
                'referenceid' => $validated['referenceid'],
                'bene_id' => $validated['bene_id'],
                'txntype' => $validated['txntype'],
                'amount' => $validated['amount'],
                'pincode' => $validated['pincode'],
                'address' => $validated['address'],
                'gst_state' => $validated['gst_state'],
                'dob' => \Carbon\Carbon::createFromFormat('d-m-Y', $validated['dob'])->format('Y-m-d'),
                'lat' => $validated['lat'] ?? null,
                'long' => $validated['long'] ?? null
            ]);

            $responseData = $response->json();

            if ($response->successful()) {
                Log::info('Transaction OTP sent successfully', [
                    'request_data' => $validated,
                    'response_status' => $responseData['status'] ?? 'unknown'
                ]);

                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ], 200);
            }

            throw new \Exception($responseData['message'] ?? 'Failed to send transaction OTP');
        } catch (\Exception $e) {
            Log::error('Transaction OTP error: ' . $e->getMessage(), [
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to send transaction OTP: ' . $e->getMessage()
            ], 500);
        }
    }

    public function transact(Request $request)
    {
        try {
            $validated = $request->validate([
                'mobile' => 'required|string',
                'referenceid' => 'required|string',
                'pincode' => 'required|string',
                'address' => 'required|string',
                'amount' => 'required|numeric|min:1',
                'txntype' => 'required|string|in:imps,neft',
                'dob' => 'required|date_format:d-m-Y',
                'gst_state' => 'required|string',
                'bene_id' => 'required|string',
                'otp' => 'required|string',
                'stateresp' => 'required|string',
                'lat' => 'required|string',
                'long' => 'required|string'
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Token' => $jwtToken,
                'Authorisedkey' => base64_decode($this->secretKey),
                'accept' => 'application/json',
                'content-type' => 'application/json'
            ])->post('https://api.paysprint.in/api/v1/service/dmt-v2/transact/transact', [
                'mobile' => $validated['mobile'],
                'referenceid' => $validated['referenceid'],
                'pincode' => $validated['pincode'],
                'address' => $validated['address'],
                'amount' => $validated['amount'],
                'txntype' => $validated['txntype'],
                'dob' => \Carbon\Carbon::createFromFormat('d-m-Y', $validated['dob'])->format('Y-m-d'),
                'gst_state' => $validated['gst_state'],
                'bene_id' => $validated['bene_id'],
                'otp' => $validated['otp'],
                'stateresp' => $validated['stateresp'],
                'lat' => $validated['lat'],
                'long' => $validated['long']
            ]);

            $responseData = $response->json();

            if ($response->successful()) {
                Log::info('Transaction successful', [
                    'request_data' => $validated,
                    'response_status' => $responseData['status'] ?? 'unknown'
                ]);

                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ], 200);
            }

            throw new \Exception($responseData['message'] ?? 'Failed to process transaction');
        } catch (\Exception $e) {
            Log::error('Transaction error: ' . $e->getMessage(), [
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to process transaction: ' . $e->getMessage()
            ], 500);
        }
    }

    public function transactionStatus(Request $request)
    {
        try {
            $validated = $request->validate([
                'referenceid' => 'required|string'
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Authorisedkey' => base64_decode($this->secretKey),
                'Content-Type' => 'application/json',
                'Token' => $jwtToken,
                'accept' => 'application/json'
            ])->post('https://api.paysprint.in/api/v1/service/dmt-v2/transact/transact/querytransact', [
                'referenceid' => $validated['referenceid']
            ]);

            $responseData = $response->json();

            if ($response->successful()) {
                Log::info('Transaction status query successful', [
                    'referenceid' => $validated['referenceid'],
                    'response_status' => $responseData['status'] ?? 'unknown'
                ]);

                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ], 200);
            }

            throw new \Exception($responseData['message'] ?? 'Failed to fetch transaction status');
        } catch (\Exception $e) {
            Log::error('Transaction status error: ' . $e->getMessage(), [
                'referenceid' => $request->input('referenceid') ?? 'unknown'
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch transaction status: ' . $e->getMessage()
            ], 500);
        }
    }
}