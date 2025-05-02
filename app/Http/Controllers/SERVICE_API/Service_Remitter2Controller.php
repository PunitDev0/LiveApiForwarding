<?php

namespace App\Http\Controllers\SERVICE_API;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;  
use App\Models\Remitter;
use App\Models\RemitterRegistration;
use App\Models\RemitterAadharVerify;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;

class Service_Remitter2Controller extends Controller
{
    public function showQueryForm()
    {
       
        return Inertia::render('Admin/remitter2/QueryRemitter');
    }

    public function queryRemitter(Request $request)
    {
        $requestIp = $request->ip(); // gets client IP
        try {
            // Validate the mobile number
            $validator = Validator::make($request->all(), [
                'mobile' => 'required|digits:10'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Make API call to the external service
            $response = Http::withHeaders([
                'AuthorisedKey' => 'Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=',
                'Token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3MzkzNDM0NDIsInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5MzQzNDQyIn0.oenxjDuLp4lPTB_fCDZL98ENr6I-ULmw0u9XkGgWZI4',
                'accept' => 'application/json'
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/dmt-v2/remitter/queryremitter', [
                'mobile' => $request->input('mobile')
            ]);

            // Return JSON response for the API call
            return response()->json([
                'success' => true,
                'data' => $response->json(),
                'ip' => $requestIp
            ]);

        } catch (\Exception $e) {
            Log::error('Remitter query error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching remitter data'
            ], 500);
        }
    }
    public function storeRemitterData(Request $request)
    {
        // Validate incoming data
        $request->validate([
            'mobile' => 'required|unique:remitters,mobile',
            'limit' => 'required|numeric',
        ]);
    
        // Store the remitter data in the database
        $remitter = Remitter::create([
            'mobile' => $request->mobile,
            'limit' => $request->limit,
        ]);
    
        return response()->json([
            'success' => true,
            'message' => 'Remitter data stored successfully',
            'data' => $remitter,
        ]);
    }


    public function maskAadhaar($aadhaar)
    {
        
        return 'XXXX-XXXX-XXXX-' . substr($aadhaar, -4);
    }

    public function showRemitterAdhaarVerifyApi()
    {
        return Inertia::render('Admin/remitter2/RemitterAdhaarVerifyApi');
    }
    
    public function verifyAadhaar(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|digits:10',
            'aadhaar_no' => 'required|digits:16',
        ]);
    
        if ($validator->fails()) {
            return back()->withErrors($validator);
        }
    
        try {
            // Initialize Guzzle HTTP client
            $client = new Client();
    
            // Make the API request with full Aadhaar number
            $response = $client->post('https://sit.paysprint.in/service-api/api/v1/service/dmt-v2/remitter/queryremitter/aadhar_verify', [
                'headers' => [
                    'AuthorisedKey' => 'Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=',
                    'Token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3MzkzNDM0NDIsInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5MzQzNDQyIn0.oenxjDuLp4lPTB_fCDZL98ENr6I-ULmw0u9XkGgWZI4',
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                ],
                'json' => [
                    'mobile' => $request->mobile,
                    'aadhaar_no' => $request->aadhaar_no,
                ],
            ]);
    

            $apiResponse = json_decode($response->getBody()->getContents(), true);

            $maskedAadhaar = $this->maskAadhaar($request->aadhaar_no);
 
            // $verification = RemitterAadharVerify::create([
            //     'mobile' => $request->mobile,
            //     'masked_aadhaar' => $maskedAadhaar, 
            //     'status' => $apiResponse['status'] ?? 'FAILED',
            //     'response_code' => $apiResponse['response_code'] ?? 'ERROR',
            //     'message' => $apiResponse['message'] ?? 'API call failed',
            // ]);
    
            // Return both API response and DB record
            if(isset($response))
            {
                return response()->json([
                    'apiData' => $apiResponse,
                    'mask_aadhar' => $maskedAadhaar,
                    'error' => null
                ]);
            }
    
        } catch (\Exception $e) {
            Log::error('Aadhaar verification error: ' . $e->getMessage());
            
            // return Inertia::render('Admin/remitter2/RemitterAdhaarVerifyApi', [
            return response()->json([
                'apiData' => null,
                'dbData' => null,
                'error' => 'Failed to verify Aadhaar: ' . $e->getMessage()
            ]);
        }
    }




    public function registerAdhaarRemitter(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|digits:10',
            'aadhaar_no' => 'required|digits:12',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ], 422);
        }
    
        try {
            $apiResponse = $this->verifyAadhaarWithAPI($request->mobile, $request->aadhaar_no);
    
            // Mask the Aadhaar number
            $maskedAadhaar = $this->maskAadhaar($request->aadhaar_no);
    
            // Store the verification result with masked Aadhaar
            // $verification = RemitterAadharVerify::create([
            //     'status' => $apiResponse['status'] ?? 'FAILED',
            //     'response_code' => $apiResponse['response_code'] ?? 'ERROR',
            //     'message' => $apiResponse['message'] ?? 'API call failed',
            //     'mobile' => $request->mobile,
            //     'masked_aadhaar' => $maskedAadhaar, // Store masked version
            // ]);
    
            // Return the response to the frontend
            return response()->json([
                'status' => $apiResponse['status'] ?? 'FAILED',
                'message' => $apiResponse['message'] ?? 'Verification failed',
                'mask_aadhar' => $maskedAadhaar
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred during verification: ' . $e->getMessage()
            ], 500);
        }
    }

    private function verifyAadhaarWithAPI($mobile, $aadhaar)
    {
       
        $client = new Client();
        $response = $client->post('https://sit.paysprint.in/service-api/api/v1/service/dmt-v2/remitter/queryremitter/aadhar_verify', [
            'headers' => [
                'AuthorisedKey' => 'Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=',
                'Token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3MzkzNDM0NDIsInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5MzQzNDQyIn0.oenxjDuLp4lPTB_fCDZL98ENr6I-ULmw0u9XkGgWZI4',
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ],
            'json' => [
                'mobile' => $mobile,
                'aadhaar_no' => $aadhaar, 
            ],
        ]);
        
        return json_decode($response->getBody()->getContents(), true);
    }

    public function showVerificationForm()
    {
        return Inertia::render('Admin/remitter2/RemitterAdhaarVerifyApi', [
            'data' => null,
            'error' => null
        ]);
    }


    //Register Remitter    
    // public function registerRemitter() 
    // {     
    //     return Inertia::render('Admin/remitter2/RegisterRemitter'); 
    // }
    public function registerRemitter(Request $request)
    {
        if ($request->isMethod('post')) {
            try {
                // Call external API
                $response = Http::withHeaders([
                    'AuthorisedKey' => 'Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=',
                    'Token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3MzkzNDM0NDIsInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5MzQzNDQyIn0.oenxjDuLp4lPTB_fCDZL98ENr6I-ULmw0u9XkGgWZI4',
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                ])->post('https://sit.paysprint.in/service-api/api/v1/service/dmt-v2/remitter/registerremitter', $request->all());

                $responseData = $response->json();

                // Extract relevant data from the response
                $status = $responseData['status'] ?? null;
                $message = $responseData['message'] ?? null;
                $limit = $responseData['data']['limit'] ?? null;
                $mobile = $responseData['data']['mobile'] ?? $request->mobile;

                // Store the registration attempt and response
                // $registration = RemitterRegistration::create([
                //     'mobile' => $request->mobile,
                //     'otp' => $request->otp,
                //     'stateresp' => $request->stateresp,
                //     'data' => $request->data,
                //     'accessmode' => $request->accessmode,
                //     'is_iris' => $request->is_iris,
                //     'limit' => $limit, // Store limit in its own column
                //     'api_response' => $responseData,
                //     'status' => $status,
                //     'message' => $message,
                // ]);

                return response()->json([
                    'data' => [
                        'mobile' => $mobile,
                        'limit' => $limit,
                        'status' => $status,
                        'message' => $message,
                        // 'registration_id' => $registration->id
                    ]
                ], $response->status());

            } catch (\Exception $e) {
                // Store failed attempt
                // $registration = RemitterRegistration::create([
                //     'mobile' => $request->mobile,
                //     'otp' => $request->otp,
                //     'stateresp' => $request->stateresp,
                //     'data' => $request->data,
                //     'accessmode' => $request->accessmode,
                //     'is_iris' => $request->is_iris,
                //     'limit' => null,
                //     'status' => 'error',
                //     'message' => $e->getMessage(),
                //     'api_response' => ['error' => $e->getMessage()]
                // ]);

                return response()->json([
                    'error' => 'Failed to communicate with external API',
                    'message' => $e->getMessage()
                ], 500);
            }
        }

        // Get recent registrations for display
        // $recentRegistrations = RemitterRegistration::latest()
        //     ->take(5)
        //     ->get()
        //     ->map(function ($registration) {
        //         return [
        //             'id' => $registration->id,
        //             'mobile' => $registration->mobile,
        //             'status' => $registration->status,
        //             'message' => $registration->message,
        //             'accessmode' => $registration->accessmode,
        //             'created_at' => $registration->created_at,
        //             'limit' => $registration->limit,
        //         ];
        //     });

        // return Inertia::render('Admin/remitter2/RegisterRemitter', [
        //     'recentRegistrations' => $recentRegistrations
        // ]);
    }

    public function getRegistrations()
    {
        $registrations = RemitterRegistration::latest()
            ->paginate(10)
            ->through(function ($registration) {
                return [
                    'id' => $registration->id,
                    'mobile' => $registration->mobile,
                    'status' => $registration->status,
                    'message' => $registration->message,
                    'accessmode' => $registration->accessmode,
                    'created_at' => $registration->created_at,
                    'limit' => $registration->limit,
                ];
            });

        return Inertia::render('Admin/remitter2/Registrations', [
            'registrations' => $registrations
        ]);
    }

}
