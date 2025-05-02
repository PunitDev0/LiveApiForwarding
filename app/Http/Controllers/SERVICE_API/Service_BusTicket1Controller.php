<?php

namespace App\Http\Controllers\SERVICE_API;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;
use App\Models\SourceCity;
use App\Models\AvailableTrip;
use App\Models\BusCurrentTrip;
use App\Models\BookBusTicket;
use App\Models\getboardingpointdetails;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service_BusTicket1Controller extends Controller
{
    

    public function fetchSourceCities()
    {
        try {
            
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'authorisedkey' => 'Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=',
                'Token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3MzkxODE3OTQsInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5MTgxNzk0In0.xu6dtDjw_kbRZ-WW6SaxbHkedurAhN-IXs8iC-Bsg2s'
                ])->post('https://sit.paysprint.in/service-api/api/v1/service/bus/ticket/source');
                
                $data = $response->json();
                // dd($response); issue occurred when hiting own forward api in api.php
                if (!$response->successful()) {
                    // dd("hi");
                    throw new \Exception('API request failed: ' . ($data['message'] ?? 'Unknown error'));
                }
                // dd("hi1");
                
                if ($data['status'] && isset($data['data']['cities'])) {
                    
                    // dd($cities);
                    return response()->json(['data' => $data]);
            }

            throw new \Exception('Invalid data structure received from API');

        } catch (\Exception $e) {
            \Log::error('Failed to fetch cities: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch or store cities',
                'error' => $e->getMessage()
            ], 500);
        }
    }




   
    public function fetchAndStoreAvailableTrips(Request $request)
    {
        try {

            $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3MzkxODE3OTQsInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5MTgxNzk0In0.xu6dtDjw_kbRZ-WW6SaxbHkedurAhN-IXs8iC-Bsg2s';
            $authKey = 'Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=';
    
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'Token' => $token,
                'authorisedkey' => $authKey
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/bus/ticket/availabletrips', $request->all());
    
            $jsonResponse = $response->json();
            
            // Add additional error checking
            if (!$response->successful()) {
                throw new \Exception('API request failed: ' . ($jsonResponse['message'] ?? 'Unknown error'));
            }
    
            return response()->json($jsonResponse);
    
        } catch (\Exception $e) {
            \Log::error('Bus API Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch trips: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //Current Trip
    
    public function getCurrentTripDetails()
    {
        return Inertia::render('Admin/busTicket/getCurrentTripDetails');
    }

    public function fetchTripDetails(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'trip_id' => 'required|string',
            ]);
            
            // Add logging to track the request
            \Log::info('Fetching trip details for trip_id: ' . $request->trip_id);
            
            // Make the API call
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'authorisedkey' => 'Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=',
                'token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3MzkzNDM0NDIsInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5MzQzNDQyIn0.oenxjDuLp4lPTB_fCDZL98ENr6I-ULmw0u9XkGgWZI4'
                ])->post('https://sit.paysprint.in/service-api/api/v1/service/bus/ticket/tripdetails', [
                    'trip_id' => $request->trip_id
                ]);
                
                // Log the API response for debugging
                \Log::info('API Response:', [
                    'status' => $response->status(),
                    'body' => $response->json()
                ]);
                
                // Check if the request was successful
                if (!$response->successful()) {
                // dd("HI1");
                \Log::error('API request failed', [
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                throw new \Exception('API request failed: ' . $response->status());
            }
    
            $data = $response->json();
            // return $data;
    
            // Check if we got valid data
            if (!isset($data['status'])) {
                throw new \Exception('Invalid response format from API');
            }
    
            if (!$data['status']) {
                throw new \Exception($data['message'] ?? 'API returned error status');
            }
    
            return response()->json([
                'status' => true,
                'data' => $data['data'] ?? null,
                'message' => $data['message'] ?? 'Success'
            ]);
    
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Failed to fetch trip details: ' . $e->getMessage());
            
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch trip details: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeTripDetails(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'trip_id' => 'required',
                'boarding_points' => 'required|array',
                'boarding_points.*.location' => 'required|string',
                'boarding_points.*.address' => 'required|string',
                'boarding_points.*.city' => 'required|string',
                'boarding_points.*.time' => 'required|integer',
                'boarding_points.*.landmark' => 'nullable|string',
                'boarding_points.*.contact' => 'nullable|string',
            ]);

            // Clear existing records for this trip
            BusCurrentTrip::where('trip_id', $request->trip_id)->delete();

            // Store boarding points in the database
            foreach ($request->boarding_points as $point) {
                BusCurrentTrip::create([
                    'trip_id' => $request->trip_id,
                    'location' => $point['location'],
                    'address' => $point['address'],
                    'city' => $point['city'],
                    'time' => $point['time'],
                    'landmark' => $point['landmark'],
                    'contact' => $point['contact']
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Trip details stored successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error storing trip details: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error storing trip details: ' . $e->getMessage()
            ], 500);
        }
    }
    public function getbookTicket()
    {
        return Inertia::render('Admin/busTicket/bookTicket');
    }
    public function bookandstorebookticket(Request $request)
    {
        try {
            // Validate input
            $request->validate([
                'refid' => 'required|integer',
                'amount' => 'required|integer',
                'base_fare' => 'required|string',
                'blockKey' => 'required|string',
                'passenger_phone' => 'required|string',
                'passenger_email' => 'required|email'
            ]);

            // API call
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'authorisedkey' => 'Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=',
                'token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3MzkzNDM0NDIsInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5MzQzNDQyIn0.oenxjDuLp4lPTB_fCDZL98ENr6I-ULmw0u9XkGgWZI4'
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/bus/ticket/bookticket', [
                'refid' => $request->refid,
                'amount' => $request->amount,
                'base_fare' => $request->base_fare,
                'blockKey' => $request->blockKey,
                'passenger_phone' => $request->passenger_phone,
                'passenger_email' => $request->passenger_email
            ]);

            // Convert response to JSON
            $responseData = $response->json();
            if($response->successful())
            {
                return response()->json([
                    'status' => true,
                    'response' => $responseData,
                ]);
            }

            return response()->json([
                'success' => $responseData['status'] ?? false, // Use actual booking status
      'message' => $responseData['message'] ?? 'Ticket booking failed',
    //   'data' => $ticket
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getboardingpointdetails()
    {
        return Inertia::render('Admin/busTicket/getBoardingPointDetails');
    }

    public function fetchandstoreboardingpointdetails(Request $request) {
        try {
            // Validate input
            $request->validate([
                'bpId' => 'required|integer',
                'trip_id' => 'required|integer',
            ]);
    
            // API call
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'authorisedkey' => 'Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=',
                'token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3MzkzNDM0NDIsInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5MzQzNDQyIn0.oenxjDuLp4lPTB_fCDZL98ENr6I-ULmw0u9XkGgWZI4'
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/bus/ticket/boardingPoint', [
                'bpId' => $request->bpId, 
                'trip_id' => $request->trip_id,
            ]);
    
            // Convert response to JSON
            $responseData = $response->json();
    
            // Check if response is successful and contains 'data'
            if (!isset($responseData['data']) || !isset($responseData['status'])) {
                return response()->json([
                    'success' => false,
                    'message' => $responseData['message'] ?? 'Invalid response from API',
                ], 400);
            }
    
            return response()->json([
    
                'api_response' => $responseData // Sending full response for debugging
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
}
