<?php

namespace App\Http\Controllers\SERVICE_API;

use Illuminate\Routing\Controller;;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;  
use Illuminate\Support\Facades\Http;
use App\Models\MunicipalityOperator;
use App\Models\MunicipalityBill;
use App\Models\MunicipalityPayBill;
use App\Models\MunicipalityTransaction;

class Service_MunicipalityController extends Controller
{
    public function store(Request $request)
        {
            $request->validate([
                'id' => 'required|string', // ✅ ID ko string format me validate kiya
                'name' => 'required|string',
                'category' => 'nullable|string',
               'displayname' => 'required|string', // If displayname is mandatory
                'regex' => 'nullable|string',
                'viewbill' => 'nullable|string',
                'ad1_d_name' => 'nullable|string',
                'ad1_name' => 'nullable|string',
                'ad1_regex' => 'nullable|string',
            ]);
        
            $municipality = MunicipalityOperator::create($request->all());
        
            return response()->json(['message' => 'Saved successfully', 'data' => $municipality], 201);
        }

    public function showMunicipalityOperator()
        {
            try {
                // Fetch all municipality operators
                $municipalities = MunicipalityOperator::all();
        
                return response()->json([
                    'municipalities' => $municipalities,
                    'message' => 'Municipality operators fetched successfully!'
                ]);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Error fetching municipality operators', 'error' => $e->getMessage()], 500);
            }
        }   
        
    public function MunicipalityOperator()
    {
        $municipalities = MunicipalityOperator::select('id', 'name')->get(); // Fetch id & name

        return Inertia::render('Admin/Municipality/MunicipalityOperator', [
            'municipalities' => $municipalities
        ]);
    }
    public function fetchMunicipalityOperator(Request $request)
    {
        $mode = $request->input('mode'); // Get mode from user input

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3Mzk3OTc1MzUsInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5Nzk3NTM1In0.d-5zd_d8YTFYC0pF68wG6qqlyrfNUIBEuvxZ77Rxc0M',
            'Authorisedkey' => 'Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I='
        ])->post('https://sit.paysprint.in/service-api/api/v1/service/bill-payment/municipality/getoperator', [
            'mode' => $mode
        ]);
        if(!$response->successful())
        {
            return response()->json([
                'status' => false,
                'msg' => 'Facing issues while geting reponse try again.'
            ]);
        }
        return response()->json($response->json());
    }

    public function FetchMunicipalityDetails()
    {
        return Inertia::render('Admin/Municipality/FetchMunicipalityDetails');
    }

    public function fetchBillDetails(Request $request)
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorisedkey' => 'Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=',
                'Token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3Mzk3OTc1MzUsInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5Nzk3NTM1In0.d-5zd_d8YTFYC0pF68wG6qqlyrfNUIBEuvxZ77Rxc0M',
                'accept' => 'application/json'
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/bill-payment/municipality/fetchbill', [
                'canumber' => $request->canumber,
                'operator' => $request->operator
            ]);
            if(!$response->successful()){
                return response()->json(); 
            }

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch bill details',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function storeBillDetails(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'amount' => 'required|integer',
            'response_code' => 'required|integer',
            'status' => 'required|boolean',
            'message' => 'required|string',
        ]);
    
        $bill = MunicipalityBill::create($validatedData);
    
        return response()->json([
            'message' => 'Bill details saved successfully!',
            'bill' => $bill
        ]);
    }

    public function showMunicipalityBill()
    {
        return Inertia::render('Admin/Municipality/MunicipalityBill');
    }

    
    public function PayMunicipalityBill(Request $request)
      {
        // Validate user input
        $validatedData = $request->validate([
            'canumber' => 'required|numeric',
            'operator' => 'required|integer',
            'amount' => 'required|numeric',
            'ad1' => 'required|integer',
            'ad2' => 'required|integer',
            'ad3' => 'required|numeric',
            'referenceid' => 'required|integer',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);
    
        // Make API request
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3Mzk3OTc1MzUsInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5Nzk3NTM1In0.d-5zd_d8YTFYC0pF68wG6qqlyrfNUIBEuvxZ77Rxc0M',
            'accept' => 'application/json',
            'Authorisedkey' => 'Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I='
        ])->post('https://sit.paysprint.in/service-api/api/v1/service/bill-payment/municipality/paybill', $validatedData);
    
        $responseData = $response->json();
    
        // Save to database
        // MunicipalityPayBill::create([
        //     'canumber' => $validatedData['canumber'],
        //     'operator' => $validatedData['operator'],
        //     'amount' => $validatedData['amount'],
        //     'ad1' => $validatedData['ad1'],
        //     'ad2' => $validatedData['ad2'],
        //     'ad3' => $validatedData['ad3'],
        //     'referenceid' => $validatedData['referenceid'],
        //     'latitude' => $validatedData['latitude'],
        //     'longitude' => $validatedData['longitude'],
        //    'ackno' => $responseData['ackno'] ?? 'N/A', // Use a default value
        //     'operatorid' => $responseData['operatorid'] ?? null,
        //     'message' => $responseData['message'] ?? null,
        //     'response_code' => $responseData['response_code'] ?? null,
        //     'status' => $responseData['status'] ?? false,
        // ]);
    
        // Return JSON response
        
        return response()->json([
            'data' => $responseData,
            'message' => 'Payment processed successfully'
        ], 200);
      }
    
    public function MunicipalityStatus()
    {
        return Inertia::render('Admin/Municipality/MunicipalityStatus');
    }

    public function MunicipalityEnquiryStatus(Request $request)
    {
        // Validate input
        $request->validate([
            'referenceid' => 'required|string',
        ]);

        $referenceId = $request->input('referenceid');

        // API request
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3Mzk3OTc1MzUsInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5Nzk3NTM1In0.d-5zd_d8YTFYC0pF68wG6qqlyrfNUIBEuvxZ77Rxc0M',
            'accept' => 'application/json',
            'Authorisedkey' => 'Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I='
        ])->post('https://sit.paysprint.in/service-api/api/v1/service/bill-payment/municipality/status', [
            'referenceid' => $referenceId,
        ]);
        // $responseData = $response->json();

        // Check if response contains valid data
        // if ($responseData['status'] && isset($responseData['data'])) {
        //     $data = $responseData['data'];
    
        //     // Save data to database
        //     MunicipalityTransaction::create([
        //         'txnid' => $data['txnid'],
        //         'operatorname' => $data['operatorname'],
        //         'canumber' => $data['canumber'],
        //         'amount' => $data['amount'],
        //         'comm' => isset($data['comm']) ? $data['comm'] : 0.00, // Default to 0 if null
        //         'tds' => $data['tds'],
        //         'status' => $data['status'],
        //         'refid' => $data['refid'],
        //         'operatorid' => $data['operatorid'],
        //         'dateadded' => $data['dateadded'],
        //         'refunded' => $data['refunded'],
        //         'refundtxnid' => isset($data['refundtxnid']) ? $data['refundtxnid'] : null,
        //         'daterefunded' => isset($data['daterefunded']) ? $data['daterefunded'] : null,
        //     ]);
            
        // }
        if(!$response->successful()){
            return response()->json([
                'status' => false,
                'msg' => 'Validation Error occured or technical glitch'
            ]);
        }
        return response()->json($response->json());
    }
}
