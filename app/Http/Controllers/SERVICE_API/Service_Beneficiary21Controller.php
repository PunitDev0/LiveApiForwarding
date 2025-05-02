<?php

namespace App\Http\Controllers\SERVICE_API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use App\Models\RegisterBeneficiary2;
use App\Models\FetchBeneficiary;
use App\Models\BeneficiaryDeletion;
use App\Models\fetchbyBenied;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;

class Service_Beneficiary21Controller extends Controller
{


    public function registerBeneficiary(Request $request)
    {
        if ($request->isMethod('post')) {
            // Make the HTTP request to the external API
            $response = Http::withHeaders([
                'AuthorisedKey' => 'Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=',
                'accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/dmt-v2/beneficiary/registerbeneficiary', [
                'mobile' => $request->mobile,
                'benename' => $request->benename,
                'bankid' => $request->bankid,
                'accno' => $request->accno,
                'ifsccode' => $request->ifsccode,
                'verified' => $request->verified,
            ]);

            $responseData = $response->json();
            return response()->json(['responseData' => $responseData], 200);
            // Check if the response is successful
            if ($response->successful()) {
                // Retrieve the JSON data from the response

                // Return the response data in JSON format
            }

            // Handle failure scenario
            return response()->json([
                'error' => 'Failed to register beneficiary. Please check the request and try again.'
            ], $response->status()); // Returns the actual status code from the API response
        }

        // If not a POST request, return method not allowed response
        return response()->json(['error' => 'Invalid request method. Use POST.'], 405);
    }




    public function fetchBeneficiary(Request $request)
    {
        $mobile = $request->input('mobile');

        // Fetch data from API if mobile is provided
        if ($mobile) {
            $response = Http::withHeaders([
                'AuthorisedKey' => 'Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=',
                'Token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3Mzk5NDQ3MTksInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5OTQ0NzE5In0.1bNrePHYUe-0FodOCdAMpPhL3Ivfpi7eVTT9V7xXsGI', // Ensure to replace this with the actual token
                'accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/dmt-v2/beneficiary/registerbeneficiary/fetchbeneficiary', [
                'mobile' => $mobile,
            ]);

            // dd($response);
            // dd("Mobile");
            $responseData = $response->json();

            return response()->json(['response' => $responseData], 200);
            // Store the beneficiary data if the API call was successful
            if ($responseData['status'] === true && !empty($responseData['data'])) {
                return $responseData;
            }
        }
    }

    public function deleteBeneficiary()
    {
        return Inertia::render('Admin/beneficiary2/deleteBeneficiary');
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
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/dmt-v2/beneficiary/registerbeneficiary/deletebeneficiary', [
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

    public function getDeletionHistory()
    {
        $history = BeneficiaryDeletion::latest()->get();
        return response()->json($history);
    }
    public function fetchbyBenied()
    {
        return Inertia::render('Admin/beneficiary2/fetchbyBenied');
    }

    public function fetchBeneficiaryData(Request $request)
    {
        $request->validate([
            'mobile' => 'required|string|size:10',
            'beneid' => 'required|string'
        ]);

        try {
            $response = Http::withHeaders([
                'AuthorisedKey' => 'Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=',
                'Content-Type' => 'application/json',
                'Token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3Mzk5NDQ3MTksInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5OTQ0NzE5In0.1bNrePHYUe-0FodOCdAMpPhL3Ivfpi7eVTT9V7xXsGI',
                'accept' => 'application/json'
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/dmt-v2/beneficiary/registerbeneficiary/fetchbeneficiarybybeneid', [
                'mobile' => $request->mobile,
                'beneid' => $request->beneid
            ]);
            $responseData = $response->json();
            if (!$response->successful()) {
                throw new \Exception('API request failed: ' . ($responseData['message'] ?? 'Unknown error'));
            }
            return response()->json($responseData);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch beneficiary data',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
