<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;
use Illuminate\Routing\Controller;

class BusTicketController extends Controller
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

    public function fetchSourceCities(Request $request)
    {
        try {
            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'accept' => 'application/json',
                'authorisedkey' => base64_decode($this->secretKey),
                'Token' => $jwtToken
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/bus/ticket/source');

            $data = $response->json();

            if (!$response->successful()) {
                throw new \Exception($data['message'] ?? 'API request failed');
            }

            if ($data['status'] && isset($data['data']['cities'])) {
                return response()->json([
                    'success' => true,
                    'data' => $data
                ], 200);
            }

            throw new \Exception('Invalid data structure received from API');
        } catch (\Exception $e) {
            Log::error('Failed to fetch cities: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch cities: ' . $e->getMessage()
            ], 500);
        }
    }

    public function fetchAvailableTrips(Request $request)
    {
        try {
            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'Token' => $jwtToken,
                'authorisedkey' => base64_decode($this->secretKey)
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/bus/ticket/availabletrips', $request->all());

            $jsonResponse = $response->json();

            if (!$response->successful()) {
                throw new \Exception($jsonResponse['message'] ?? 'API request failed');
            }

            return response()->json([
                'success' => $jsonResponse['status'] ?? true,
                'data' => $jsonResponse
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to fetch trips: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch trips: ' . $e->getMessage()
            ], 500);
        }
    }

    public function fetchTripDetails(Request $request)
    {
        try {
            $request->validate([
                'trip_id' => 'required|string',
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'authorisedkey' => base64_decode($this->secretKey),
                'Token' => $jwtToken
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/bus/ticket/tripdetails', [
                'trip_id' => $request->trip_id
            ]);

            $data = $response->json();

            if (!$response->successful()) {
                Log::error('API request failed', [
                    'status' => $response->status(),
                    'response' => $data
                ]);
                throw new \Exception('API request failed: ' . $response->status());
            }

            if (!isset($data['status']) || !$data['status']) {
                throw new \Exception($data['message'] ?? 'Invalid response from API');
            }

            return response()->json([
                'success' => true,
                'data' => $data['data'] ?? null,
                'message' => $data['message'] ?? 'Success'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to fetch trip details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch trip details: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bookTicket(Request $request)
    {
        try {
            $request->validate([
                'refid' => 'required|integer',
                'amount' => 'required|integer',
                'base_fare' => 'required|string',
                'blockKey' => 'required|string',
                'passenger_phone' => 'required|string',
                'passenger_email' => 'required|email'
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'authorisedkey' => base64_decode($this->secretKey),
                'Token' => $jwtToken
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/bus/ticket/bookticket', [
                'refid' => $request->refid,
                'amount' => $request->amount,
                'base_fare' => $request->base_fare,
                'blockKey' => $request->blockKey,
                'passenger_phone' => $request->passenger_phone,
                'passenger_email' => $request->passenger_email
            ]);

            $responseData = $response->json();

            if (!$response->successful()) {
                throw new \Exception($responseData['message'] ?? 'API request failed');
            }

            return response()->json([
                'success' => $responseData['status'] ?? true,
                'data' => $responseData
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error booking ticket: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error booking ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    public function fetchBoardingPointDetails(Request $request)
    {
        try {
            $request->validate([
                'bpId' => 'required|integer',
                'trip_id' => 'required|integer',
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'authorisedkey' => base64_decode($this->secretKey),
                'Token' => $jwtToken
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/bus/ticket/boardingPoint', [
                'bpId' => $request->bpId,
                'trip_id' => $request->trip_id,
            ]);

            $responseData = $response->json();

            if (!$response->successful() || !isset($responseData['data']) || !isset($responseData['status'])) {
                throw new \Exception($responseData['message'] ?? 'Invalid response from API');
            }

            return response()->json([
                'success' => true,
                'data' => $responseData
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching boarding point details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching boarding point details: ' . $e->getMessage()
            ], 500);
        }
    }

    public function fetchBookedTickets(Request $request)
    {
        try {
            $request->validate([
                'refid' => 'required|integer',
            ]);

            $requestId = time() . rand(1000, 9999);
            $jwtToken = $this->generateJwtToken($requestId);

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'authorisedkey' => base64_decode($this->secretKey),
                'Token' => $jwtToken
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/bus/ticket/check_booked_ticket', [
                'refid' => $request->refid,
            ]);

            $responseData = $response->json();

            if (!$response->successful() || !isset($responseData['data']) || !isset($responseData['status'])) {
                throw new \Exception($responseData['message'] ?? 'Invalid response from API');
            }

            return response()->json([
                'success' => true,
                'data' => $responseData
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching booked tickets: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching booked tickets: ' . $e->getMessage()
            ], 500);
        }
    }
}