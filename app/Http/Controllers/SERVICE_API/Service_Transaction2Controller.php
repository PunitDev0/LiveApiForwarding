<?php

namespace App\Http\Controllers\SERVICE_API;

use Illuminate\Routing\Controller;;
use Illuminate\Http\Request;
use App\Models\PennyDrop;
use App\Models\TransactionSentOtp;
use App\Models\DMTTransaction2;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;

class Service_Transaction2Controller extends Controller
{
    public function pennyDrop(Request $request)
    {
        if ($request->isMethod('post')) {
            $validatedData = $request->validate([
                'mobile' => 'required|digits:10',
                'accno' => 'required|string',
                'bankid' => 'required|integer',
                'benename' => 'required|string',
                'referenceid' => 'required|string',
                'pincode' => 'required|digits:6',
                'address' => 'required|string',
                'dob' => 'required|date_format:d-m-Y',
                'gst_state' => 'required|string|max:2',
                'bene_id' => 'required|integer',
            ]);

            // Send data to Paysprint API
            $apiResponse = Http::withHeaders([
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'AuthorisedKey' => 'Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=',
                'Token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3Mzk5NDQ3MTksInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5OTQ0NzE5In0.1bNrePHYUe-0FodOCdAMpPhL3Ivfpi7eVTT9V7xXsGI',
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/dmt-v2/beneficiary/registerbeneficiary/benenameverify', $validatedData);

            $responseData = $apiResponse->json();

            // Save request and response data to the penny_drops table
            // PennyDrop::create([
            //     'mobile' => $validatedData['mobile'],
            //     'accno' => $validatedData['accno'],
            //     'bankid' => $validatedData['bankid'],
            //     'benename' => $validatedData['benename'],
            //     'referenceid' => $validatedData['referenceid'],
            //     'pincode' => $validatedData['pincode'],
            //     'address' => $validatedData['address'],
            //     'dob' => date('Y-m-d', strtotime($validatedData['dob'])),
            //     'gst_state' => $validatedData['gst_state'],
            //     'bene_id' => $validatedData['bene_id'],

            //     // Store API response
            //     'status' => $responseData['status'] ?? 0,
            //     'response_code' => $responseData['response_code'] ?? null,
            //     'utr' => $responseData['utr'] ?? null,
            //     'ackno' => $responseData['ackno'] ?? null,
            //     'txn_status' => $responseData['txn_status'] ?? null,
            //     'message' => $responseData['message'] ?? null,
            //     'balance' => $responseData['balance'] ?? null,
            // ]);

            // Return with API response
            return response()->json([
                'apiResponse' => $responseData,
            ]);
        }

        // Initial render with empty response
        return response()->json([ 
            'apiResponse' => null,
        ]);
    }



    public function transactionSentOtp(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'mobile'      => 'required|digits:10',
                'referenceid' => 'required|string',
                'bene_id'     => 'required|string',
                'txntype'     => 'required|string',
                'amount'      => 'required|numeric',
                'pincode'     => 'required|string',
                'address'     => 'required|string',
                'gst_state'   => 'required|string',
                'dob'         => 'required|date_format:d-m-Y',
                'lat'         => 'nullable|string',
                'long'        => 'nullable|string',
            ]);
    
            // Convert dob to MySQL compatible format (YYYY-MM-DD)
            $dob = \Carbon\Carbon::createFromFormat('d-m-Y', $request->dob)->format('Y-m-d');
    
            $response = Http::withHeaders([
                'Content-Type'  => 'application/json',
                'accept'        => 'application/json',
                'Token'         => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3Mzk5NDQ3MTksInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5OTQ0NzE5In0.1bNrePHYUe-0FodOCdAMpPhL3Ivfpi7eVTT9V7xXsGI',
                'AuthorisedKey' => 'Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=',
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/dmt-v2/transact/transact/send_otp', [
                'mobile'      => $request->mobile,
                'referenceid' => $request->referenceid,
                'bene_id'     => $request->bene_id,
                'txntype'     => $request->txntype,
                'amount'      => $request->amount,
                'pincode'     => $request->pincode,
                'address'     => $request->address,
                'gst_state'   => $request->gst_state,
                'dob'         => $dob,
                'lat'         => $request->lat,
                'long'        => $request->long,
            ]);
    
            $apiResponse = $response->json();
            Log::info('API Response:', ['response' => $apiResponse]);
    
            // Save response to the database
            // TransactionSentOtp::create([
            //     'mobile'        => $request->mobile,
            //     'referenceid'   => $request->referenceid,
            //     'bene_id'       => $request->bene_id,
            //     'txntype'       => $request->txntype,
            //     'amount'        => $request->amount,
            //     'pincode'       => $request->pincode,
            //     'address'       => $request->address,
            //     'gst_state'     => $request->gst_state,
            //     'dob'           => $dob,
            //     'lat'           => $request->lat,
            //     'long'          => $request->long,
            //     'status'        => $apiResponse['status'] ?? null,
            //     'response_code' => $apiResponse['response_code'] ?? null,
            //     'message'       => $apiResponse['message'] ?? null,
            //     'txn_id'        => $apiResponse['data']['txn_id'] ?? null,
            //     'ackno'         => $apiResponse['data']['ackno'] ?? null,
            //     'utr'           => $apiResponse['data']['utr'] ?? null,
            // ]);
    
            return response()->json([
                'response' => $apiResponse
            ]);
        }
    
        return response()->json([
            'status' => false,
            'msg' => 'error'
        ]);
    }



public function transaction()
{
    return Inertia::render('Admin/transaction2/transaction');
}
public function transact(Request $request)
{
    $validated = $request->validate([
        'mobile' => 'required|string',
        'referenceid' => 'required|string',
        'pincode' => 'required|string',
        'address' => 'required|string',
        'amount' => 'required|string',
        'txntype' => 'required|string|in:imps,neft',
        'dob' => 'required|date_format:d-m-Y',
        'gst_state' => 'required|string',
        'bene_id' => 'required|string',
        'otp' => 'required|string',
        'stateresp' => 'required|string',
        'lat' => 'required|string',
        'long' => 'required|string',
    ]);

    // Log request payload
    Log::info('Request Payload:', $validated);

    // Call external API
    $response = Http::withHeaders([
        'Token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3Mzk5NDQ3MTksInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5OTQ0NzE5In0.1bNrePHYUe-0FodOCdAMpPhL3Ivfpi7eVTT9V7xXsGI',
        'AuthorisedKey' => 'Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=',
        'accept' => 'application/json',
        'content-type' => 'application/json',
    ])->post('https://sit.paysprint.in/service-api/api/v1/service/dmt-v2/transact/transact', $validated);

    // Log API Response
    Log::info('API Response:', $response->json());

    // Return data to frontend
    return response()->json([
        'transactionData' => $response->json(),
    ]);
}

    public function transactionStatus(Request $request)
    {
        $referenceId = $request->input('referenceid');

        $response = Http::withHeaders([
            'AuthorisedKey' => 'Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=',
            'Content-Type' => 'application/json',
            'Token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3Mzk5NDQ3MTksInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5OTQ0NzE5In0.1bNrePHYUe-0FodOCdAMpPhL3Ivfpi7eVTT9V7xXsGI',
            'accept' => 'application/json',
        ])->post('https://sit.paysprint.in/service-api/api/v1/service/dmt-v2/transact/transact/querytransact', [
            'referenceid' => $referenceId,
        ]);

        $data = $response->json();
        if(!$response->successful()){
            return response()->json([
                'status' => false,
                'msg' => 'error'
            ]);
        }

        return response()->json([
            'transactionData' => $data,
        ]);
    }
}
