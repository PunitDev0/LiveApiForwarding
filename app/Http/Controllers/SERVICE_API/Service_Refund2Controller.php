<?php

namespace App\Http\Controllers\SERVICE_API;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\refundOtp;
use App\Models\ClaimRefund;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;  
use Illuminate\Support\Facades\Http;

class Service_Refund2Controller extends Controller
{
    public function refundOtp(Request $request)
    {
        // if ($request->isMethod('get')) {
        //     return Inertia::render('Admin/refund2/refundOtp', [
        //         'apiResponse' => null
        //     ]);
        // }

        try {
            $validated = $request->validate([
                'referenceid' => 'required|string',
                'ackno' => 'required|string',
            ]);

            $response = Http::withHeaders([
                'AuthorisedKey' => 'Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=',
                'Token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3Mzk5NDQ3MTksInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5OTQ0NzE5In0.1bNrePHYUe-0FodOCdAMpPhL3Ivfpi7eVTT9V7xXsGI',
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/dmt-v2/refund/refund/resendotp', [
                'referenceid' => $validated['referenceid'],
                'ackno' => $validated['ackno'],
            ]);

            $responseData = $response->json();

            // RefundOtp::create([
            //     'referenceid' => $validated['referenceid'],
            //     'ackno' => $validated['ackno'],
            //     'status' => $responseData['status'] ?? 'unknown',
            //     'response_code' => $responseData['response_code'] ?? 'unknown',
            //     'message' => $responseData['message'] ?? 'No message',
            // ]);

            return response()->json([
                'apiResponse' => $responseData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'apiResponse' => [
                    'status' => 'error',
                    'message' => 'An error occurred',
                    'details' => $e->getMessage()
                ]
            ]);
        }
    }
    
    public function claimRefund()
    {
        return Inertia::render('Admin/refund2/claimRefund', [
            'apiResponse' => session('apiResponse') ?? null,
        ]);
    }
    
    public function processRefund(Request $request)
    {
        $request->validate([
            'ackno'       => 'required|string',
            'referenceid' => 'required|string',
        ]);
    
        try {
            $response = Http::withHeaders([
                'AuthorisedKey' => 'Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=',
                'Content-Type'  => 'application/json',
                'Token'         => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3Mzg5MjE3NzcsInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM4OTIxNzc3In0.6vhPb1SE1p3yvAaK_GAEz-Y0Ai1ibCbN85adKW_1Xzg',
                'accept'        => 'application/json',
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/dmt-v2/refund/refund/', [
                'ackno'       => $request->ackno,
                'referenceid' => $request->referenceid,
                'otp'         => '222111',
            ]);
    
            $apiResponse = $response->json();
    
            // Store response in claim_refunds table
            // ClaimRefund::create([
            //     'ackno'        => $request->ackno,
            //     'referenceid'  => $request->referenceid,
            //     'status'       => $apiResponse['status'] ?? 'failed',
            //     'response_code'=> $apiResponse['response_code'] ?? 'unknown',
            //     'message'      => $apiResponse['message'] ?? 'No message',
            // ]);
    
            return response()->json($apiResponse);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process refund.',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
