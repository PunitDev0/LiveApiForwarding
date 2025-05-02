<?php

namespace App\Http\Controllers\SERVICE_API;

use Illuminate\Routing\Controller;;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\UtilityOperator;
use App\Models\UtilityBillPayment;
use App\Models\UtilityStatusEnquiry;
use App\Models\BillDetail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class Service_UtilitybillPaymentController extends Controller
{
    public function operatorList()
    {
        try {
            // Check if we need to fetch new data

                // Fetch data from API
                $response = Http::withHeaders([
                    'Authorisedkey' => 'Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=',
                    'Token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3Mzk5NDQ3MTksInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5OTQ0NzE5In0.1bNrePHYUe-0FodOCdAMpPhL3Ivfpi7eVTT9V7xXsGI',
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                ])->post('https://sit.paysprint.in/service-api/api/v1/service/bill-payment/bill/getoperator', [
                    'mode' => 'online'
                ]);

                if (!$response->successful()) {
                    throw new \Exception('API request failed: ' . $response->status());
                }

                $apiData = $response->json();

                if (!isset($apiData['data']) || !is_array($apiData['data'])) {
                    throw new \Exception('Invalid data format received from API');
                }
            return response()->json([
                'operators' => $apiData,
                'success' => $response ? 'Operators updated successfully' : null
            ]);

        } catch (\Exception $e) {
            \Log::error('Operator list error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch operators: ' . $e->getMessage()
            ]);
        }
    }
   public function fetchBillDetails(Request $request)
    {

        // Validate the request
        $validated = $request->validate([
            'operator' => 'required|numeric',
            'canumber' => 'required|numeric',
            'mode' => 'required|in:online,offline',
        ]);

        try {
            $response = Http::withHeaders([
                'Authorisedkey' => 'Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=',
                'Token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3Mzk5NDQ3MTksInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5OTQ0NzE5In0.1bNrePHYUe-0FodOCdAMpPhL3Ivfpi7eVTT9V7xXsGI',
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/bill-payment/bill/fetchbill', [
                'operator' => $validated['operator'],
                'canumber' => $validated['canumber'],
                'mode' => $validated['mode'],
            ]);

            $data = $response->json();

            return response()->json([
                'success' => $data 
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'billData' => null,
                'errors' => ['api' => 'Failed to fetch bill details: ' . $e->getMessage()]
            ]);
        }
    }

    public function payBill()
    {
        return Inertia::render('Admin/UtilityBillPayment/PayBill');
    }

    public function processBillPayment(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'canumber' => 'required|string|min:5'
        ]);

        try {
            $apiUrl = config('services.paysprint.url', 'https://sit.paysprint.in/service-api/api/v1/service/bill-payment/bill/paybill');
            
            // Generate a unique reference ID
            $referenceId = 'REF' . time() . rand(1000, 9999);
            
            $payload = [
                "operator" => "11",
                "canumber" => $validated['canumber'],
                "amount" => "100",
                "referenceid" => $referenceId,
                "latitude" => "27.2232",
                "longitude" => "78.26535",
                "mode" => "online",
                "bill_fetch" => [
                    "billAmount" => "820.0",
                    "billnetamount" => "820.0",
                    "billdate" => date('d-M-Y'),
                    "dueDate" => date('Y-m-d', strtotime('+7 days')),
                    "acceptPayment" => true,
                    "acceptPartPay" => false,
                    "cellNumber" => $validated['canumber'],
                    "userName" => "SALMAN"
                ]
            ];

            // Log the request payload for debugging
            \Log::info('Payment API Request:', [
                'url' => $apiUrl,
                'payload' => $payload
            ]);

            $response = Http::withHeaders([
                'Authorisedkey' => config('services.paysprint.auth_key', 'Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I='),
                'Token' => config('services.paysprint.token', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3Mzk3OTc1MzUsInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5Nzk3NTM1In0.d-5zd_d8YTFYC0pF68wG6qqlyrfNUIBEuvxZ77Rxc0M'),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post($apiUrl, $payload);

            // Log the API response for debugging
            \Log::info('Payment API Response:', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            // Check if the response was successful
            if (!$response->successful()) {
                throw new \Exception('API request failed: ' . $response->body());
            }

            $responseData = $response->json();

            // Validate response data
            if (!isset($responseData['status'])) {
                throw new \Exception('Invalid API response format');
            }

            // Store data in the database
            // $billPayment = UtilityBillPayment::create([
            //     'consumer_number' => $validated['canumber'],
            //     'amount' => 100,
            //     'operator_id' => $responseData['operatorid'] ?? null,
            //     'ack_no' => $responseData['ackno'] ?? null,
            //     'reference_id' => $referenceId,
            //     'response_code' => $responseData['response_code'] ?? null,
            //     'status' => $responseData['status'] ?? false,
            //     'message' => $responseData['message'] ?? null,
            // ]);

            // // If database storage fails, log it but don't fail the request
            // if (!$billPayment) {
            //     \Log::error('Failed to store bill payment record', [
            //         'consumer_number' => $validated['canumber'],
            //         'reference_id' => $referenceId
            //     ]);
            // }

            return response()->json($responseData);
            
        } catch (\Exception $e) {
            // Log the detailed error
            \Log::error('Bill payment error: ' . $e->getMessage(), [
                'consumer_number' => $validated['canumber'] ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => false,
                'error' => 'Payment processing failed: ' . $e->getMessage()
            ], 500);
        }
    }





    public function utilityStatusEnquiry()
{
    return Inertia::render('Admin/UtilityBillPayment/UtilityStatusEnquiry');
}
public function fetchUtilityStatus(Request $request)
    {
        try {
            $validated = $request->validate([
                'referenceid' => 'required|string',
            ]);

            Log::info('Fetching utility status for reference ID: ' . $validated['referenceid']);

            $response = Http::withHeaders([
                'Authorisedkey' => 'Y2RkZTc2ZmNjODgxODljMjkyN2ViOTlhM2FiZmYyM2I=',
                'Token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0aW1lc3RhbXAiOjE3Mzk3OTc1MzUsInBhcnRuZXJJZCI6IlBTMDAxNTY4IiwicmVxaWQiOiIxNzM5Nzk3NTM1In0.d-5zd_d8YTFYC0pF68wG6qqlyrfNUIBEuvxZ77Rxc0M',
                'accept' => 'application/json',
                'content-type' => 'application/json'
            ])->post('https://sit.paysprint.in/service-api/api/v1/service/bill-payment/bill/status', [
                'referenceid' => $validated['referenceid']
            ]);

            $apiResponse = $response->json();
            Log::info('API Response:', ['response' => $apiResponse]);

            // If the API response is successful and contains data
            // if (isset($apiResponse['status']) && $apiResponse['status'] && isset($apiResponse['data'])) {
            //     $data = $apiResponse['data'];
            //     Log::info('Attempting to store data:', ['data' => $data]);

                // try {
                //     $stored = UtilityStatusEnquiry::updateOrCreate(
                //         ['reference_id' => $data['refid']], // The unique identifier
                //         [
                //             'transaction_id' => $data['txnid'] ?? null,
                //             'operator_name' => $data['operatorname'] ?? null,
                //             'customer_number' => $data['canumber'] ?? null,
                //             'amount' => $data['amount'] ?? 0,
                //             'additional_data_1' => $data['ad1'] ?? null,
                //             'additional_data_2' => $data['ad2'] ?? null,
                //             'additional_data_3' => $data['ad3'] ?? null,
                //             'commission' => $data['comm'] ?? 0,
                //             'tds' => $data['tds'] ?? 0,
                //             'transaction_status' => $data['status'] ?? null,
                //             'operator_id' => $data['operatorid'] ?? null,
                //             'date_added' => $data['dateadded'] ?? null,
                //             'refunded' => $data['refunded'] !== "0",
                //             'refund_transaction_id' => $data['refunded'] !== "0" ? ($data['refundtxnid'] ?? null) : null,
                //             'date_refunded' => $data['refunded'] !== "0" ? ($data['daterefunded'] ?? null) : null
                //         ]
                //     );

                //     Log::info('Data stored successfully:', ['stored_data' => $stored]);
                // } catch (\Exception $e) {
                //     Log::error('Database storage error:', [
                //         'error' => $e->getMessage(),
                //         'trace' => $e->getTraceAsString()
                //     ]);
                // }
            // } else {
            //     Log::warning('Invalid API response structure:', ['response' => $apiResponse]);
            // }
            if(!$response->successful()){
                return response()->json([
                    'status' => false,
                    'msg' => "error"
                ]);
            }
            return response()->json($apiResponse);

        } catch (\Exception $e) {
            Log::error('Controller error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while processing your request'
            ], 500);
        }
    }
}
