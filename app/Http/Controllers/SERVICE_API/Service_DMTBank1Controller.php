<?php

namespace App\Http\Controllers\SERVICE_API;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Remitter1Query;
use App\Models\Remitter1Registration;
use App\Models\Remitter1Ekyc;
use App\Models\Beneficiary1Register;
use App\Models\DeletedBeneficiary1;
use App\Models\PennyDropDmtBank1;
use App\Models\Transaction1;
use App\Models\Transaction1OTP;
use App\Models\Transaction1Status;
use App\Models\Refund1_otps;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;  
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class Service_DMTBank1Controller extends Controller
{
    public function QueryRemitter(){
        return Inertia::render('Admin/remitter1/QueryRemitter');
    }
    public function fetchQueryRemitter(Request $request)
    {
        $validated = $request->validate([
            'mobile' => 'required|digits:10'
        ]);
        // dd("hi");
    
        $apiUrl = "https://sit.paysprint.in/service-api/api/v1/service/dmt/kyc/remitter/queryremitter";
        $response = Http::withHeaders([
            'Authorisedkey' => 'Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=',
            'Content-Type' => 'application/json',
            'Token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3Mzk3OTc1MzUsInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5Nzk3NTM1In0.d-5zd_d8YTFYC0pF68wG6qqlyrfNUIBEuvxZ77Rxc0M'
        ])->post($apiUrl, ['mobile' => $validated['mobile']]);
    
        $responseData = $response->json();
    
        if ($response->successful()) {    
            return response()->json($responseData);
        }
    
        return response()->json(['error' => 'Failed to fetch data'], 500);
    }
    
    public function Remitter1EKYC(){
        return Inertia::render('Admin/remitter1/RemitterE-KYC');
    }
    public function ekyc_remitter1(Request $request)
    {
        $response = Http::withHeaders([
            'Token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3Mzk3OTc1MzUsInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5Nzk3NTM1In0.d-5zd_d8YTFYC0pF68wG6qqlyrfNUIBEuvxZ77Rxc0M',
            'Authorisedkey' => 'Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=',
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ])->post('https://sit.paysprint.in/service-api/api/v1/service/dmt/kyc/remitter/queryremitter/kyc', [
            "mobile" => $request->mobile,
            "lat" => "28.123456",
            "long" => "78.123456",
            "aadhaar_number" => $request->aadhaar_number,
            "data" => "encrypted value of pid data",
            "is_iris" => 2
        ]);
    
        $result = $response->json();    
        return response()->json($result);
    }
    //Register Remitter

    public function RegisterRemitter(){
        return Inertia::render('Admin/remitter1/RegisterRemitter');
    }
    public function processRegisterRemitter(Request $request)
{
    // Validate the input
    $validated = $request->validate([
        'mobile' => 'required',
        'otp' => 'required',
        'stateresp' => 'required',
        'ekyc_id' => 'required',
    ]);
    

    $client = new \GuzzleHttp\Client();
    
    try {
        $response = $client->request('POST', 'https://sit.paysprint.in/service-api/api/v1/service/dmt/kyc/remitter/registerremitter', [
            'headers' => [
                'Authorisedkey' => 'Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=',
                'Content-Type' => 'application/json',
                'Token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3Mzk3OTc1MzUsInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5Nzk3NTM1In0.d-5zd_d8YTFYC0pF68wG6qqlyrfNUIBEuvxZ77Rxc0M',
                'accept' => 'application/json',
            ],
            'json' => [
                'mobile' => $validated['mobile'],
                'otp' => $validated['otp'],
                'stateresp' => $validated['stateresp'],
                'ekyc_id' => $validated['ekyc_id'],
            ],
        ]);
        

        $responseBody = json_decode($response->getBody(), true);
        
        // Return the API response back to the frontend
        return response()->json($responseBody);
        
    } catch (\Exception $e) {
        // Handle errors
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
}
public function storeRegisterRemitter1(Request $request)
    {
        \Log::info("Received Data: ", $request->all());

        // Direct Data Save
        Remitter1Registration::create($request->all());

        return response()->json(['message' => 'Remitter Registered Successfully']);
    }

//Register Beneficiary
public function RegisterBeneficiary(){
    return Inertia::render('Admin/beneficiary1/RegisterBeneficiary');
}

public function storeBeneficiary(Request $request)
{
    
    $url = "https://sit.paysprint.in/service-api/api/v1/service/dmt/kyc/beneficiary/registerbeneficiary";

    $headers = [
        'Authorisedkey' => 'Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=',
        'Token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3Mzk3OTc1MzUsInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5Nzk3NTM1In0.d-5zd_d8YTFYC0pF68wG6qqlyrfNUIBEuvxZ77Rxc0M',
        'Content-Type' => 'application/json',
        'accept' => 'application/json',
    ];

    $data = $request->only([
        'mobile', 'benename', 'bankid', 'accno', 'ifsccode', 'verified', 
        'gst_state', 'dob', 'address', 'pincode'
    ]);

    $response = Http::withHeaders($headers)->post($url, $data);
    $responseData = $response->json();
    return $responseData;
}

public function destroyBeneficiary(Request $request)
{
    $validated = $request->validate([
        'mobile' => 'required|digits:10',
        'bene_id' => 'required|string'
    ]);
    try {
        $response = Http::withHeaders([
            'AuthorisedKey' => 'Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=',
            'Content-Type' => 'application/json',
            'Token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3Mzk5NDQ3MTksInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5OTQ0NzE5In0.1bNrePHYUe-0FodOCdAMpPhL3Ivfpi7eVTT9V7xXsGI',
        ])->post('https://sit.paysprint.in/service-api/api/v1/service/dmt/kyc/beneficiary/registerbeneficiary/fetchbeneficiary', [
            'mobile' => $validated['mobile'],
            'bene_id' => $validated['bene_id']
        ]);


        $responseData = $response->json();
        return response()->json([
            'status' => true,
            'response' => $responseData,
        ]);
        // dd("HI");
    } catch (\Exception $e) {
        Log::error('Error in deleteBeneficiary', ['error' => $e->getMessage()]);

        return response()->json([
            'status' => false,
            'error' => 'Error Occured'
        ]);
    }
}




    public function deleteBeneficiaryAccount(Request $request)
    {
        $data = $request->validate([
            'mobile' => 'required|string',
            'bene_id' => 'required|string',
        ]);

        // Delete the beneficiary from the main table
        DB::table('beneficiary1_registers')
            ->where('mobile', $data['mobile'])
            ->where('bene_id', $data['bene_id'])
            ->delete();

        // Response Data
        $response = [
            'status' => true,
            'response_code' => 1,
            'message' => 'Beneficiary record deleted successfully.',
        ];

        // Save to deleted_beneficiaries table
        DeletedBeneficiary1::create([
            'mobile' => $data['mobile'],
            'bene_id' => $data['bene_id'],
            'response' => $response,
        ]);

        return redirect()->route('admin.beneficiary.delete')->with('success', $response);
    }


public function fetchBeneficiary(Request $request)
{
    // dd("HI");
    // Check if the request contains a mobile number
    if (!$request->has('mobile')) {
        return Inertia::render('Admin/beneficiary1/FetchBeneficiary');
    }

    // Define API URL and headers
    $apiUrl = "https://sit.paysprint.in/service-api/api/v1/service/dmt/kyc/beneficiary/registerbeneficiary/fetchbeneficiary";
    $authorisedKey = "Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=";
    $token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3Mzk3OTc1MzUsInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5Nzk3NTM1In0.d-5zd_d8YTFYC0pF68wG6qqlyrfNUIBEuvxZ77Rxc0M";

    // Fetch the mobile number from request
    $mobile = $request->input('mobile');

    // Make the API request
    $response = Http::withHeaders([
        'Authorisedkey' => $authorisedKey,
        'Token' => $token,
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ])->post($apiUrl, [
        'mobile' => $mobile,
    ]);

    // Convert response to JSON
    $responseData = $response->json();

    // Pass the response data to the frontend using Inertia
    return Inertia::render('Admin/beneficiary1/FetchBeneficiary', [
        'beneficiaryData' => $responseData,
        'enteredMobile' => $mobile
    ]);
}


public function fetchBeneficiaryByBenied()
{
    return Inertia::render('Admin/beneficiary1/FetchBeneficiaryByBenied');
}
public function fetchBeneficiaryByBeneIdshow(Request $request)
{
    // Check if the request contains a mobile number
    if (!$request->has('mobile')) {
        return Inertia::render('Admin/beneficiary1/FetchBeneficiaryByBenied');
    }

    // Define API URL and headers
    $apiUrl = "https://sit.paysprint.in/service-api/api/v1/service/dmt/kyc/beneficiary/registerbeneficiary/fetchbeneficiarybybeneid";
    $authorisedKey = "Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=";
    $token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3Mzk3OTc1MzUsInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5Nzk3NTM1In0.d-5zd_d8YTFYC0pF68wG6qqlyrfNUIBEuvxZ77Rxc0M";

    // Fetch the mobile number from request
    $mobile = $request->input('mobile');
    $beneid = $request->input('beneid'); // Ensure it's 'bene_id', not 'beneid'

    $response = Http::withHeaders([
        'Authorisedkey' => $authorisedKey,
        'Token' => $token,
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ])->post($apiUrl, [
        'mobile' => $mobile,
        'beneid' => $beneid, // Use 'bene_id' instead of 'beneid'
    ]);
    

    // Convert response to JSON
    $responseData = $response->json();

    // Pass the response data to the frontend using Inertia
    return Inertia::render('Admin/beneficiary1/FetchBeneficiaryByBenied', [
        'beneficiaryData' => $responseData,
        'enteredMobile' => $mobile,
        'enteredBeneId' => $beneid
    ]);
}
public function pennyDrop(){
    return Inertia::render('Admin/transaction1/PennyDrop');
}

    public function processPennyDrop(Request $request)
    {
        // Define API URL and headers
        $apiUrl = "https://sit.paysprint.in/service-api/api/v1/service/dmt/kyc/beneficiary/registerbeneficiary/benenameverify";
        $headers = [
            'Authorisedkey' => 'Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=',
            'Token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3Mzk3OTc1MzUsInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5Nzk3NTM1In0.d-5zd_d8YTFYC0pF68wG6qqlyrfNUIBEuvxZ77Rxc0M',
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        // Validate input
        $validatedData = $request->validate([
            'mobile' => 'required|string',
            'accno' => 'required|string',
            'bankid' => 'required|integer',
            'benename' => 'required|string',
            'referenceid' => 'required|string',
            'pincode' => 'required|integer',
            'address' => 'required|string',
            'dob' => 'required|string',
            'gst_state' => 'required|string',
            'bene_id' => 'required|integer',
        ]);

        // Send request to API
        $response = Http::withHeaders($headers)->post($apiUrl, $validatedData);
        $responseData = $response->json();

        // Check API response
        if ($response->successful() && isset($responseData['status']) && $responseData['status']) {

            return response()->json([
                "success" => true, 
                "message" => "Transaction Successful", 
                "data" => $responseData
            ]);
        }

        return response()->json([
            "success" => false, 
            "message" => $responseData['message'] ?? 'Transaction Failed'
        ], 400);
    }
    public function transaction1(){
        return Inertia::render('Admin/transaction1/transaction');
    }
    
    public function processtransaction1(Request $request)
    {
        try {
            // Validate request data - updated with all required fields
            $validatedData = $request->validate([
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
                'stateresp' => 'required|string',
            ]);
    
            // Define API URL and headers - using direct values
            $apiUrl = "https://sit.paysprint.in/service-api/api/v1/service/dmt/kyc/transact/transact";
            $authorisedKey = "Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=";
            $token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3Mzk3OTc1MzUsInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5Nzk3NTM1In0.d-5zd_d8YTFYC0pF68wG6qqlyrfNUIBEuvxZ77Rxc0M";
    
            // Send API request with all required parameters
            $apiResponse = Http::withHeaders([
                'Authorisedkey' => $authorisedKey,
                'Token' => $token,
                'Content-Type' => 'application/json',
            ])->post($apiUrl, [
                'mobile' => $validatedData['mobile'],
                'referenceid' => $validatedData['referenceid'],
                'bene_id' => $validatedData['bene_id'],
                'txntype' => $validatedData['txntype'],
                'dob' => $validatedData['dob'],
                'amount' => $validatedData['amount'],
                'pincode' => $validatedData['pincode'] ?? '',
                'address' => $validatedData['address'] ?? '',
                'gst_state' => $validatedData['gst_state'] ?? '',
                'lat' => $validatedData['lat'] ?? '',
                'long' => $validatedData['long'] ?? '',
                'otp' => $validatedData['otp'],
                'stateresp' => $validatedData['stateresp'],
            ]);
    
            // Check if the request was successful
            if (!$apiResponse->successful()) {
                Log::error('API request failed', [
                    'status' => $apiResponse->status(),
                    'response' => $apiResponse->body()
                ]);
                
                return response()->json([
                    'error' => 'API request failed: ' . $apiResponse->status(),
                    'formData' => $validatedData
                ])->with('error', 'API request failed');
            }
    
            $responseData = $apiResponse->json();
    
            // Validate API response
            if (!$responseData || !isset($responseData['status'])) {
                Log::error('Invalid API response format', [
                    'response' => $responseData
                ]);
                
                return response()->json([
                    'error' => 'Invalid API response format',
                    'formData' => $validatedData
                ])->with('error', 'Invalid API response format');
            }
    
            // Prepare transaction data with proper error handling
            // $transactionData = [
            //     'mobile' => $validatedData['mobile'],
            //     'referenceid' => $validatedData['referenceid'],
            //     'bene_id' => $validatedData['bene_id'],
            //     'txntype' => $validatedData['txntype'],
            //     'dob' => $validatedData['dob'],
            //     'amount' => $validatedData['amount'], 
            //     'otp' => $validatedData['otp'], 
            //     'pincode' => $validatedData['pincode'] ?? null,  // ✅ Added
            //     'address' => $validatedData['address'] ?? null,  // ✅ Added
            //     'gst_state' => $validatedData['gst_state'] ?? null,  // ✅ Added
            //     'lat' => $validatedData['lat'] ?? null,  // ✅ Added
            //     'long' => $validatedData['long'] ?? null,  // ✅ Added
            //     'stateresp' => $validatedData['stateresp'] ?? null,  // ✅ Added
            //     'status' => $responseData['status'] ?? false,
            //     'response_code' => $responseData['response_code'] ?? 0,
            //     'ackno' => $responseData['ackno'] ?? '',
            //     'utr' => $responseData['utr'] ?? '',
            //     'txn_status' => $responseData['txn_status'] ?? 0,
            //     'benename' => $responseData['benename'] ?? '',
            //     'remarks' => $responseData['remarks'] ?? '',
            //     'message' => $responseData['message'] ?? '',
            //     'customercharge' => $responseData['customercharge'] ?? 0.00,
            //     'gst' => $responseData['gst'] ?? 0.00,
            //     'tds' => $responseData['tds'] ?? 0.00,
            //     'netcommission' => $responseData['netcommission'] ?? 0.00,
            //     'remitter' => $responseData['remitter'] ?? '',
            //     'account_number' => $responseData['account_number'] ?? '',
            //     'paysprint_share' => $responseData['paysprint_share'] ?? 0.00,
            //     'txn_amount' => $responseData['txn_amount'] ?? 0.00,
            //     'balance' => $responseData['balance'] ?? 0.00,
            // ];
            
            
    
            // Save response data to DB
            // $transaction = Transaction1::create($transactionData);
    
            // Return a successful Inertia response
            return response()->json([
                'success' => 'Transaction saved successfully',
                'transaction' => $responseData,
                'formData' => $validatedData
            ])->with('success', 'Transaction saved successfully');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return Inertia::render('Admin/transaction1/transaction', [
                'errors' => $e->errors(),
                'formData' => $request->all()
            ]);
            
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Transaction processing error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return an error response
            return response()->json([
                'error' => 'An error occurred while processing the transaction',
                'formData' => $request->all()
            ])->with('error', 'Transaction processing failed');
        }
    }
public function transactionOtp()
{
    return Inertia::render('Admin/transaction1/transactionOtp');
}
public function processtransaction1Otp(Request $request)
{
    try {
        // Validate request data - updated with all required fields
        $validatedData = $request->validate([
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
                'long' => 'nullable|string',
            ]);
            
            // Define API URL and headers - using direct values
            $apiUrl = "https://sit.paysprint.in/service-api/api/v1/service/dmt/kyc/transact/transact/send_otp";
            $authorisedKey = "Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=";
            $token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3Mzk3OTc1MzUsInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5Nzk3NTM1In0.d-5zd_d8YTFYC0pF68wG6qqlyrfNUIBEuvxZ77Rxc0M";
            
            // Send API request with all required parameters
            $apiResponse = Http::withHeaders([
                'Authorisedkey' => $authorisedKey,
                'Token' => $token,
                'Content-Type' => 'application/json',
                ])->post($apiUrl, [
                    'mobile' => $validatedData['mobile'],
                'referenceid' => $validatedData['referenceid'],
                'bene_id' => $validatedData['bene_id'],
                'txntype' => $validatedData['txntype'],
                'amount' => $validatedData['amount'],
                'pincode' => $validatedData['pincode'] ?? '',
                'address' => $validatedData['address'] ?? '',
                'dob' => $validatedData['dob'],
                'gst_state' => $validatedData['gst_state'] ?? '',
                'lat' => $validatedData['lat'] ?? '',
                'long' => $validatedData['long'] ?? '',
            ]);
            
            // Check if the request was successful
            if (!$apiResponse->successful()) {
                Log::error('API request failed', [
                    'status' => $apiResponse->status(),
                    'response' => $apiResponse->body()
                ]);
                
                return response()->json([
                    'error' => 'API request failed: ' . $apiResponse->status(),
                    'formData' => $validatedData
                    ])->with('error', 'API request failed');
                }
    
            $responseData = $apiResponse->json();
            
            // Validate API response
            if (!$responseData || !isset($responseData['status'])) {
                Log::error('Invalid API response format', [
                    'response' => $responseData
                ]);
                
                return response()->json([
                    'error' => 'Invalid API response format',
                    'formData' => $validatedData
                    ])->with('error', 'Invalid API response format');
            }
            
            // Prepare transaction data with proper error handling
            // $transactionData = [
            //     'mobile' => $validatedData['mobile'],
            //     'referenceid' => $validatedData['referenceid'],
            //     'bene_id' => $validatedData['bene_id'],
            //     'txntype' => $validatedData['txntype'],
            //     'dob' => $validatedData['dob'],
            //     'amount' => $validatedData['amount'], 
            //     'pincode' => $validatedData['pincode'] ?? null,  // ✅ Added
            //     'address' => $validatedData['address'] ?? null,  // ✅ Added
            //     'gst_state' => $validatedData['gst_state'] ?? null,  // ✅ Added
            //     'lat' => $validatedData['lat'] ?? null,  // ✅ Added
            //     'long' => $validatedData['long'] ?? null,  // ✅ Added
                
            //     'status' => $responseData['status'] ?? false,
            //     'response_code' => $responseData['response_code'] ?? 0,
            //     'message' => $responseData['message'] ?? '',
            //     'stateresp' => $responseData['stateresp'] ?? '',
              
            // ];
            
            
            
            // Save response data to DB
            // $transaction = Transaction1OTP::create($transactionData);
            
            // Return a successful Inertia response
            return response()->json([
                'success' => 'Transaction saved successfully',
                'transaction' => $responseData,
                'formData' => $validatedData
                ])->with('success', 'Transaction saved successfully');
                
            } catch (\Illuminate\Validation\ValidationException $e) {
                // Handle validation errors
                return response()->json([
                    'errors' => $e->errors(),
                    'formData' => $request->all()
                ]);
                
            } catch (\Exception $e) {
                // Log the exception
                Log::error('Transaction processing error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return an error response
            return response()->json([
                'error' => 'An error occurred while processing the transaction',
                'formData' => $request->all()
                ])->with('error', 'Transaction processing failed');
            }
        }
        
        public function transactionStatus()
        {
            return Inertia::render('Admin/transaction1/transactionStatus');
        }
   
        
        public function processTransaction1Status(Request $request)
        {
            $request->validate([
                'referenceid' => 'required|string',
            ]);
        
            $referenceId = $request->input('referenceid');
        
            $response = Http::withHeaders([
                'Token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3Mzk3OTc1MzUsInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5Nzk3NTM1In0.d-5zd_d8YTFYC0pF68wG6qqlyrfNUIBEuvxZ77Rxc0M',
                'Authorisedkey' => 'Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/dmt/kyc/transact/transact/querytransact', [
                'referenceid' => $referenceId,
            ]);
        
            $data = $response->json();
        
            if (isset($data['status']) && $data['status'] === true) {
                // $transaction = Transaction1Status::updateOrCreate(
                //     ['referenceid' => $data['referenceid']],
                //     [
                //         'status' => $data['status'],
                //         'response_code' => $data['response_code'] ?? null,
                //         'utr' => $data['utr'] ?? null,
                //         'amount' => $data['amount'] ?? null,
                //         'ackno' => $data['ackno'] ?? null,
                //         'account' => $data['account'] ?? null,
                //         'txn_status' => $data['txn_status'] ?? null,
                //         'message' => $data['message'] ?? null,
                //         'customercharge' => $data['customercharge'] ?? null,
                //         'gst' => $data['gst'] ?? null,
                //         'discount' => $data['discount'] ?? null,
                //         'tds' => $data['tds'] ?? null,
                //         'netcommission' => $data['netcommission'] ?? null,
                //         'daterefunded' => $data['daterefunded'] ?? null,
                //         'refundtxnid' => $data['refundtxnid'] ?? null,
                //     ]
                // );
        
                return response()->json([
                    'transactionData' => $data,
                ]);
            }
        
            return response()->json([
                'transactionData' => null,
                'error' => 'Transaction failed!',
            ]);
        }
        
        public function refundOTP()
        {
            return Inertia::render('Admin/refund1/refundOtp');
        }

        public function processrefundOTP(Request $request) {
            $request->validate([
                'referenceid' => 'required|string',
                'ackno' => 'required|string',
            ]);
            $referenceid = $request->input('referenceid');
            $ackno = $request->input('ackno');

        
            try {
        
            // Define API URL and headers
            $apiUrl = "https://sit.paysprint.in/service-api/api/v1/service/dmt/kyc/refund/refund/resendotp";
            $authorisedKey = "Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=";
            $token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3Mzk3OTc1MzUsInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5Nzk3NTM1In0.d-5zd_d8YTFYC0pF68wG6qqlyrfNUIBEuvxZ77Rxc0M";
        
            $response = Http::withHeaders([
                'Authorisedkey' => $authorisedKey,
                'Token' => $token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($apiUrl, [
                'referenceid' => $referenceid,
                'ackno' => $ackno, 
            ]);
            
        
            // Convert response to JSON
            $responseData = $response->json();
        
            // Pass the api forwarding response to next party
            return response()->json([
                'apiResponse' => $responseData, // Change refundData to apiResponse
                'enteredreferenceid' => $referenceid,
                'enteredacknownumber' => $ackno
            ]);
                   }
            catch (\Exception $e) {
                return response()->json([
                    'apiResponse' => ['error' => 'Failed to connect to API', 'message' => $e->getMessage()],
                ]);
            }
        }
        public function claimRefund()
        {
            return Inertia::render('Admin/refund1/claimRefund');
        }
        public function processclaimRefund(Request $request)
    {
        $request->validate([
            'referenceid' => 'required|string',
            'ackno' => 'required|string',
            'otp' => 'required|string'
        ]);

        $referenceid = $request->input('referenceid');
        $ackno = $request->input('ackno');
        $otp = $request->input('otp');

        try {
            $apiUrl = "https://sit.paysprint.in/service-api/api/v1/service/dmt/kycrefund/refund";
            $authorisedKey = "Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=";
            $token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3Mzk3OTc1MzUsInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5Nzk3NTM1In0.d-5zd_d8YTFYC0pF68wG6qqlyrfNUIBEuvxZ77Rxc0M";

            $response = Http::withHeaders([
                'Authorisedkey' => $authorisedKey,
                'Token' => $token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($apiUrl, [
                'referenceid' => $referenceid,
                'ackno' => $ackno,
                'otp' => $otp
            ]);

            $responseData = $response->json();

            // Save data in the database no need to save in database this api will consume some one esle
            // Refund1_otps ::create([
            //     'referenceid' => $referenceid,
            //     'ackno' => $ackno,
            //     'otp' => $otp,
            //     'status' => $responseData['status'] ?? false,
            //     'response_code' => $responseData['response_code'] ?? null,
            //     'message' => $responseData['message'] ?? null
            // ]);

            return response()->json([
                'success' => true,
                'data' => $responseData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to connect to API',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
