<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;
use Illuminate\Routing\Controller;

class DMTBank1Controller extends Controller
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

    public function fetchQueryRemitter(Request $request)
    {
        try {
            $validated = $request->validate([
                'mobile' => 'required|digits:10'
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Authorisedkey' => base64_decode($this->secretKey),
                'Content-Type' => 'application/json',
                'Token' => $jwtToken
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/dmt/kyc/remitter/queryremitter', [
                'mobile' => $validated['mobile']
            ]);

            $responseData = $response->json();

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ], 200);
            }

            throw new \Exception($responseData['message'] ?? 'Failed to fetch data');
        } catch (\Exception $e) {
            Log::error('Failed to fetch remitter query: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch remitter query: ' . $e->getMessage()
            ], 500);
        }
    }

    public function ekycRemitter(Request $request)
    {
        try {
            $validated = $request->validate([
                'mobile' => 'required|string',
                'aadhaar_number' => 'required|string'
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Token' => $jwtToken,
                'Authorisedkey' => base64_decode($this->secretKey),
                'accept' => 'application/json',
                'content-type' => 'application/json'
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/dmt/kyc/remitter/queryremitter/kyc', [
                'mobile' => $validated['mobile'],
                'lat' => '28.123456',
                'long' => '78.123456',
                'aadhaar_number' => $validated['aadhaar_number'],
                'data' => 'encrypted value of pid data',
                'is_iris' => 2
            ]);

            $responseData = $response->json();

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ], 200);
            }

            throw new \Exception($responseData['message'] ?? 'Failed to process eKYC');
        } catch (\Exception $e) {
            Log::error('Failed to process eKYC: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process eKYC: ' . $e->getMessage()
            ], 500);
        }
    }

    public function registerRemitter(Request $request)
    {
        try {
            $validated = $request->validate([
                'mobile' => 'required|string',
                'otp' => 'required|string',
                'stateresp' => 'required|string',
                'ekyc_id' => 'required|string'
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Authorisedkey' => base64_decode($this->secretKey),
                'Content-Type' => 'application/json',
                'Token' => $jwtToken,
                'accept' => 'application/json'
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/dmt/kyc/remitter/registerremitter', [
                'mobile' => $validated['mobile'],
                'otp' => $validated['otp'],
                'stateresp' => $validated['stateresp'],
                'ekyc_id' => $validated['ekyc_id']
            ]);

            $responseData = $response->json();

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ], 200);
            }

            throw new \Exception($responseData['message'] ?? 'Failed to register remitter');
        } catch (\Exception $e) {
            Log::error('Failed to register remitter: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to register remitter: ' . $e->getMessage()
            ], 500);
        }
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
                'gst_state' => 'nullable|string',
                'dob' => 'nullable|string',
                'address' => 'nullable|string',
                'pincode' => 'nullable|string'
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Authorisedkey' => base64_decode($this->secretKey),
                'Token' => $jwtToken,
                'Content-Type' => 'application/json',
                'accept' => 'application/json'
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/dmt/kyc/beneficiary/registerbeneficiary', [
                'mobile' => $validated['mobile'],
                'benename' => $validated['benename'],
                'bankid' => $validated['bankid'],
                'accno' => $validated['accno'],
                'ifsccode' => $validated['ifsccode'],
                'verified' => $validated['verified'],
                'gst_state' => $validated['gst_state'],
                'dob' => $validated['dob'],
                'address' => $validated['address'],
                'pincode' => $validated['pincode']
            ]);

            $responseData = $response->json();

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ], 200);
            }

            throw new \Exception($responseData['message'] ?? 'Failed to register beneficiary');
        } catch (\Exception $e) {
            Log::error('Failed to register beneficiary: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to register beneficiary: ' . $e->getMessage()
            ], 500);
        }
    }

    public function fetchBeneficiary(Request $request)
    {
        try {
            $validated = $request->validate([
                'mobile' => 'required|string'
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Authorisedkey' => base64_decode($this->secretKey),
                'Token' => $jwtToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/dmt/kyc/beneficiary/registerbeneficiary/fetchbeneficiary', [
                'mobile' => $validated['mobile']
            ]);

            $responseData = $response->json();

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ], 200);
            }

            throw new \Exception($responseData['message'] ?? 'Failed to fetch beneficiary');
        } catch (\Exception $e) {
            Log::error('Failed to fetch beneficiary: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch beneficiary: ' . $e->getMessage()
            ], 500);
        }
    }

    public function fetchBeneficiaryByBeneId(Request $request)
    {
        try {
            $validated = $request->validate([
                'mobile' => 'required|string',
                'beneid' => 'required|string'
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Authorisedkey' => base64_decode($this->secretKey),
                'Token' => $jwtToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/dmt/kyc/beneficiary/registerbeneficiary/fetchbeneficiarybybeneid', [
                'mobile' => $validated['mobile'],
                'beneid' => $validated['beneid']
            ]);

            $responseData = $response->json();

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ], 200);
            }

            throw new \Exception($responseData['message'] ?? 'Failed to fetch beneficiary by bene_id');
        } catch (\Exception $e) {
            Log::error('Failed to fetch beneficiary by bene_id: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch beneficiary by bene_id: ' . $e->getMessage()
            ], 500);
        }
    }

    public function processPennyDrop(Request $request)
    {
        try {
            $validated = $request->validate([
                'mobile' => 'required|string',
                'accno' => 'required|string',
                'bankid' => 'required|integer',
                'benename' => 'required|string',
                'referenceid' => 'required|string',
                'pincode' => 'required|integer',
                'address' => 'required|string',
                'dob' => 'required|string',
                'gst_state' => 'required|string',
                'bene_id' => 'required|integer'
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Authorisedkey' => base64_decode($this->secretKey),
                'Token' => $jwtToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/dmt/kyc/beneficiary/registerbeneficiary/benenameverify', $validated);

            $responseData = $response->json();

            if ($response->successful() && isset($responseData['status']) && $responseData['status']) {
                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ], 200);
            }

            throw new \Exception($responseData['message'] ?? 'Transaction failed');
        } catch (\Exception $e) {
            Log::error('Failed to process penny drop: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process penny drop: ' . $e->getMessage()
            ], 500);
        }
    }

    public function processTransaction(Request $request)
    {
        try {
            $validated = $request->validate([
                'mobile' => 'required|string',
                'referenceid' => 'required|string',
                'bene_id' => 'required|string',
                'txntype' => 'required|string|in:IMPS,NEFT',
                'dob' => 'required|date',
                'amount' => 'required|string',
                'pincode' => 'nullable|string',
                'address' => 'nullable|string',
                'gst_state' => 'nullable|string',
                'lat' => 'nullable|string',
                'long' => 'nullable|string',
                'otp' => 'required|string',
                'stateresp' => 'required|string'
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Authorisedkey' => base64_decode($this->secretKey),
                'Token' => $jwtToken,
                'Content-Type' => 'application/json'
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/dmt/kyc/transact/transact', [
                'mobile' => $validated['mobile'],
                'referenceid' => $validated['referenceid'],
                'bene_id' => $validated['bene_id'],
                'txntype' => $validated['txntype'],
                'dob' => $validated['dob'],
                'amount' => $validated['amount'],
                'pincode' => $validated['pincode'] ?? '',
                'address' => $validated['address'] ?? '',
                'gst_state' => $validated['gst_state'] ?? '',
                'lat' => $validated['lat'] ?? '',
                'long' => $validated['long'] ?? '',
                'otp' => $validated['otp'],
                'stateresp' => $validated['stateresp']
            ]);

            $responseData = $response->json();

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ], 200);
            }

            throw new \Exception($responseData['message'] ?? 'Transaction failed');
        } catch (\Exception $e) {
            Log::error('Failed to process transaction: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process transaction: ' . $e->getMessage()
            ], 500);
        }
    }

    public function processTransactionOtp(Request $request)
    {
        try {
            $validated = $request->validate([
                'mobile' => 'required|string',
                'referenceid' => 'required|string',
                'bene_id' => 'required|string',
                'txntype' => 'required|string|in:IMPS,NEFT',
                'amount' => 'required|string',
                'pincode' => 'nullable|string',
                'address' => 'nullable|string',
                'dob' => 'required|date',
                'gst_state' => 'nullable|string',
                'lat' => 'nullable|string',
                'long' => 'nullable|string'
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Authorisedkey' => base64_decode($this->secretKey),
                'Token' => $jwtToken,
                'Content-Type' => 'application/json'
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/dmt/kyc/transact/transact/send_otp', [
                'mobile' => $validated['mobile'],
                'referenceid' => $validated['referenceid'],
                'bene_id' => $validated['bene_id'],
                'txntype' => $validated['txntype'],
                'amount' => $validated['amount'],
                'pincode' => $validated['pincode'] ?? '',
                'address' => $validated['address'] ?? '',
                'dob' => $validated['dob'],
                'gst_state' => $validated['gst_state'] ?? '',
                'lat' => $validated['lat'] ?? '',
                'long' => $validated['long'] ?? ''
            ]);

            $responseData = $response->json();

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ], 200);
            }

            throw new \Exception($responseData['message'] ?? 'Failed to process transaction OTP');
        } catch (\Exception $e) {
            Log::error('Failed to process transaction OTP: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process transaction OTP: ' . $e->getMessage()
            ], 500);
        }
    }

    public function processTransactionStatus(Request $request)
    {
        try {
            $validated = $request->validate([
                'referenceid' => 'required|string'
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Token' => $jwtToken,
                'Authorisedkey' => base64_decode($this->secretKey),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/dmt/kyc/transact/transact/querytransact', [
                'referenceid' => $validated['referenceid']
            ]);

            $responseData = $response->json();

            if ($response->successful() && isset($responseData['status']) && $responseData['status']) {
                return response()->json([
                    'success' => true,
                    'data' => $responseData
                ], 200);
            }

            throw new \Exception($responseData['message'] ?? 'Failed to fetch transaction status');
        } catch (\Exception $e) {
            Log::error('Failed to fetch transaction status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch transaction status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function processRefundOtp(Request $request)
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
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/dmt/kyc/refund/refund/resendotp', [
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

    public function processClaimRefund(Request $request)
    {
        try {
            $validated = $request->validate([
                'referenceid' => 'required|string',
                'ackno' => 'required|string',
                'otp' => 'required|string'
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Authorisedkey' => base64_decode($this->secretKey),
                'Token' => $jwtToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/dmt/kycrefund/refund', [
                'referenceid' => $validated['referenceid'],
                'ackno' => $validated['ackno'],
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