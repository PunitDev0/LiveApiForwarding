<?php

namespace App\Http\Controllers\SERVICE_API;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\LpgOperator;
use App\Models\LpgBillDetail;
use App\Models\LPGSTatus;
use App\Models\LPGPayBillResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;  
use Illuminate\Support\Facades\Http;

class Service_LPGController extends Controller
{
    public function LPGOperator(){
        return Inertia::render('Admin/LPG/LPGOperator');
    }
    public function fetchLPGOperator(Request $request)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorisedkey' => 'Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=',
            'Token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3Mzk3OTc1MzUsInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5Nzk3NTM1In0.d-5zd_d8YTFYC0pF68wG6qqlyrfNUIBEuvxZ77Rxc0M'
        ])->post('https://sit.paysprint.in/service-api/api/v1/service/bill-payment/lpg/getoperator', [
            'mode' => $request->mode
        ]);

        $data = $response->json();

        if (isset($data['data'])) {
            // foreach ($data['data'] as $item) {
            //     LPGOperator::updateOrCreate(
            //         ['id' => $item['id']],
            //         [
            //             'name' => $item['name'],
            //             'category' => $item['category'],
            //             'viewbill' => $item['viewbill'],
            //             'regex' => $item['regex'] ?? null,
            //             'displayname' => $item['displayname'] ?? null,
            //             'ad1_d_name' => $item['ad1_d_name'] ?? null,
            //             'ad1_name' => $item['ad1_name'] ?? null,
            //             'ad1_regex' => $item['ad1_regex'] ?? null,
            //             'ad2_d_name' => $item['ad2_d_name'] ?? null,
            //             'ad2_name' => $item['ad2_name'] ?? null,
            //             'ad2_regex' => $item['ad2_regex'] ?? null,
            //             'ad3_d_name' => $item['ad3_d_name'] ?? null,
            //             'ad3_name' => $item['ad3_name'] ?? null,
            //             'ad3_regex' => $item['ad3_regex'] ?? null,
            //         ]
            //     );
            // }
            return response()->json([
                'message' => 'LPG Operators data fetched and stored successfully',
                'data' => $data
            ]);
        }

    }
    

    
    public function FetchLPGDetails(Request $request)
    {
        // If no input, just load the page
        if (!$request->has('operator')) {
            return Inertia::render('Admin/LPG/FetchLPGDetails', ['lpgData' => null]);
        }

        $apiUrl = "https://sit.paysprint.in/service-api/api/v1/service/bill-payment/lpg/fetchbill";

        // API Headers
        $headers = [
            'Content-Type'  => 'application/json',
            'Token'         => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3Mzk3OTc1MzUsInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5Nzk3NTM1In0.d-5zd_d8YTFYC0pF68wG6qqlyrfNUIBEuvxZ77Rxc0M',
            'Authorisedkey' => 'Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=',
            'accept'        => 'application/json'
        ];

        // API Request Body (Using User Input)
        $body = [
            "operator"    => $request->input('operator'),
            "canumber"    => "XXXXX",
            "referenceid" => "XXXXX",
            "latitude"    => "28.65521",
            "longitude"   => "77.14343",
            "ad1"         => "1",
            "ad2"         => "8",
            "ad3"         => "170",
            "ad4"         => "41013435"
        ];

        // Make API Request
        $response = Http::withHeaders($headers)->post($apiUrl, $body);

        // Decode JSON response
        $apiResponse = $response->json();

        // Store response data into the database
        // LpgBillDetail::create([
        //     'operator'     => $request->input('operator'),
        //     'canumber'     => $body['canumber'],
        //     'referenceid'  => $body['referenceid'],
        //     'latitude'     => $body['latitude'],
        //     'longitude'    => $body['longitude'],
        //     'ad1'          => $body['ad1'],
        //     'ad2'          => $body['ad2'],
        //     'ad3'          => $body['ad3'],
        //     'ad4'          => $body['ad4'],
        //     'response_code'=> $apiResponse['response_code'] ?? null,
        //     'status'       => $apiResponse['status'] ?? 'Failed',
        //     'amount'       => $apiResponse['amount'] ?? 0.00,
        //     'name'         => $apiResponse['name'] ?? 'N/A',
        //     'message'      => $apiResponse['message'] ?? 'No message'
        // ]);

        // Pass the API response to the frontend
        return response()->json([
            'lpgData' => $apiResponse
        ]);
    }

    public function LPGBill(){
        return Inertia::render('Admin/LPG/LPGPayBill');
    }
    public function payLpgBill(Request $request)
    {
        // Validate request data
        $request->validate([
            'canumber'    => 'required|string',
            'referenceid' => 'required|string',
            'amount'      => 'required|numeric',
            'operator'    => 'required|string',
        ]);
    
        // Make API request
        $response = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'Authorisedkey' => 'Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=',
            'Token'         => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3Mzk3OTc1MzUsInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5Nzk3NTM1In0.d-5zd_d8YTFYC0pF68wG6qqlyrfNUIBEuvxZ77Rxc0M',
        ])->post('https://sit.paysprint.in/service-api/api/v1/service/bill-payment/lpg/paybill', [
            "canumber"    => $request->canumber,
            "referenceid" => $request->referenceid,
            "amount"      => $request->amount,
            "operator"    => $request->operator,
            "ad1"         => 22,
            "ad2"         => 458,
            "ad3"         => 16336200,
            "latitude"    => 27.2232,
            "longitude"   => 78.26535
        ]);
    
        $responseData = $response->json();
    
        // Store response data in the database
        // $billResponse = LPGPayBillResponse::create([
        //     'canumber'    => $request->canumber,
        //     'operator'    => $request->operator,
        //     'amount'      => $request->amount,
        //     'ad1'         => 22,
        //     'ad2'         => 458,
        //     'ad3'         => 16336200,
        //     'referenceid' => $request->referenceid,
        //     'latitude'    => 27.2232,
        //     'longitude'   => 78.26535,
        //     'operatorid'  => $responseData['operatorid'] ?? null,
        //     'status'      => $responseData['status'] ?? 'Failed',
        //     'ackno'       => $responseData['ackno'] ?? null,
        //     'message'     => $responseData['message'] ?? 'No response message',
        // ]);
    
        // Return stored data as JSON
        return response()->json($responseData);
    }
// In LPGController.php
public function getLpgBillHistory()
{
    $transactions = LPGPayBillResponse::orderBy('created_at', 'desc')->get();
    return response()->json($transactions);
}


    public function LPGStatus() {
        return Inertia::render('Admin/LPG/LPGStatus');
    }
    public function getLPGStatus(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'referenceid' => 'required|string|max:255'
            ]);

            $referenceId = $validated['referenceid'];

            // API configuration
            $apiUrl = "https://sit.paysprint.in/service-api/api/v1/service/bill-payment/lpg/status";
            $token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3Mzk3OTc1MzUsInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5Nzk3NTM1In0.d-5zd_d8YTFYC0pF68wG6qqlyrfNUIBEuvxZ77Rxc0M";
            $authKey = "Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=";

            // Make API request
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Token' => $token,
                'Authorisedkey' => $authKey,
                'Accept' => 'application/json',
            ])->post($apiUrl, [
                'referenceid' => $referenceId
            ]);

            // Log the API response for debugging
            Log::info('LPG API Response', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            $responseData = $response->json();

            // Check if we have a valid response
            if (!$response->successful()) {
                throw new \Exception('API request failed: ' . ($responseData['message'] ?? 'Unknown error'));
            }

            // // Process and store the data
            // $statusData = [
            //     'reference_id' => $referenceId,
            //     'txnid' => $responseData['data']['txnid'] ?? null,
            //     'operator_name' => $responseData['data']['operatorname'] ?? null,
            //     'customer_number' => $responseData['data']['canumber'] ?? null,
            //     'amount' => $responseData['data']['amount'] ?? 0,
            //     'tds' => $responseData['data']['tds'] ?? 0,
            //     'operator_id' => $responseData['data']['operatorid'] ?? null,
            //     'refid' => $responseData['data']['refid'] ?? null,
            //     'date_added' => $responseData['data']['dateadded'] ?? now(),
            //     'refunded' => isset($responseData['data']['refunded']) ? $responseData['data']['refunded'] === "1" : false,
            //     'date_refunded' => $responseData['data']['daterefunded'] ?? null,
            //     'message' => $responseData['message'] ?? null
            // ];

            // // Update or create the record
            // LpgStatus::updateOrCreate(
            //     ['reference_id' => $referenceId],
            //     $statusData
            // );
            return response()->json($responseData);
            dd("HI");

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('LPG Status Validation Error', ['errors' => $e->errors()]);
            return response()->json([
                'status' => false,
                'message' => 'Invalid input provided',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('LPG Status Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while processing your request. Please try again.'
            ], 500);
        }
    }
}
