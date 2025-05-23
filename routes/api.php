<?php

use App\Http\Controllers\API\Beneficiary2Controller;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\BusTicketController;
use App\Http\Controllers\API\CMSAirtelController;
use App\Http\Controllers\API\DMTBank1Controller;
use App\Http\Controllers\API\FastagRechargeController;
use App\Http\Controllers\API\InsuranceController;
use App\Http\Controllers\API\InsurancePremiumPaymentController;
use App\Http\Controllers\API\LICController;
use App\Http\Controllers\API\LPGController;
use App\Http\Controllers\API\MunicipalityController;
use App\Http\Controllers\API\Refund2Controller;
use App\Http\Controllers\API\Remitter2Controller;
use App\Http\Controllers\API\Transaction2Controller;
use App\Http\Controllers\API\UtilitybillPaymentController;
use App\Http\Controllers\API\RechargeController;
use App\Http\Controllers\SERVICE_API\Service_Beneficiary21Controller;
use App\Http\Controllers\SERVICE_API\Service_BusTicket1Controller;
use App\Http\Controllers\SERVICE_API\Service_InsurancePremiumPaymentController;
use App\Http\Controllers\SERVICE_API\Service_LPGController;
use App\Http\Controllers\SERVICE_API\Service_MunicipalityController;
use App\Http\Controllers\SERVICE_API\Service_Refund2Controller;
use App\Http\Controllers\SERVICE_API\Service_Remitter2Controller;
use App\Http\Controllers\SERVICE_API\Service_Transaction2Controller;
use App\Http\Controllers\SERVICE_API\Service_UtilitybillPaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
|
*/

Route::get('/test', function () {
    // Just for testing - mock IP
    request()->server->set('REMOTE_ADDR', '106.219.160.181');

    return request()->ip();
});

// Remitter2 Routes
Route::post('/remitters/query', [Remitter2Controller::class, 'queryRemitter'])->name('remitters.query');
Route::post('/remitters/verify-aadhaar', [Remitter2Controller::class, 'verifyAadhaar'])->name('remitters.verify-aadhaar');
Route::post('/remitters/register', [Remitter2Controller::class, 'registerRemitter'])->name('remitters.register');

// Beneficiary2 Routes
Route::post('/beneficiaries/create', [Beneficiary2Controller::class, 'registerBeneficiary'])->name('beneficiaries.create');
Route::post('/beneficiaries/delete', [Beneficiary2Controller::class, 'destroyBeneficiary'])->name('beneficiaries.delete');
Route::post('/beneficiaries/fetch', [Beneficiary2Controller::class, 'fetchBeneficiary'])->name('beneficiaries.fetch');
Route::post('/beneficiaries/fetch-beneId', [Beneficiary2Controller::class, 'fetchBeneficiaryDataByID'])->name('beneficiaries.fetch');


// Transaction2 Routes
Route::post('/transactions/penny-drop', [Transaction2Controller::class, 'pennyDrop'])->name('transactions.penny-drop');
Route::post('/transactions/send-otp', [Transaction2Controller::class, 'transactionSentOtp'])->name('transactions.send-otp');
Route::post('/transactions/transact', [Transaction2Controller::class, 'transact'])->name('transactions.transact');
Route::post('/transactions/status', [Transaction2Controller::class, 'transactionStatus'])->name('transactions.status');

// Refund2 Routes
Route::post('/refunds/otp', [Refund2Controller::class, 'refundOtp'])->name('refunds.otp');
Route::post('/refunds/process', [Refund2Controller::class, 'processRefund'])->name('refunds.process');

// BusTicketController Routes (Assumed)
Route::post('/bus-tickets/source_cities', [BusTicketController::class, 'fetchSourceCities'])->name('bus-tickets.operators');
Route::post('/bus-tickets/available_tickets', [BusTicketController::class, 'fetchAvailableTrips'])->name('bus-tickets.search');
Route::post('/bus-tickets/trip_details', [BusTicketController::class, 'fetchTripDetails'])->name('bus-tickets.book');
Route::post('/bus-tickets/book_ticket', [BusTicketController::class, 'bookTicket'])->name('bus-tickets.status');
Route::post('/bus-tickets/boarding_points', [BusTicketController::class, 'fetchBoardingPointDetails'])->name('bus-tickets.boarding_points');
Route::post('/bus-tickets/get_booked_tickets', [BusTicketController::class, 'fetchBookedTickets'])->name('bus-tickets.get_book_tickets');
Route::post('/bus-tickets/block_tickets', [BusTicketController::class, 'blockTicket'])->name('bus-tickets.get_book_tickets');
Route::post('/bus-tickets/ticket_cancellation', [BusTicketController::class, 'cancelTicket'])->name('bus-tickets.get_book_tickets');

// DMTBank1Controller Routes (Assumed)
Route::post('/dmt-bank/verify', [DMTBank1Controller::class, 'verifyBankDetails'])->name('dmt-bank.verify');
Route::post('/dmt-bank/transfer', [DMTBank1Controller::class, 'processBankTransfer'])->name('dmt-bank.transfer');
Route::post('/dmt-bank/status', [DMTBank1Controller::class, 'fetchTransferStatus'])->name('dmt-bank.status');

// InsuranceController Routes (Assumed)

Route::post('/insurance/premium-details', [InsurancePremiumPaymentController::class, 'fetchLICBill'])->name('insurance.premium-details');
Route::post('/insurance/pay-premium', [InsurancePremiumPaymentController::class, 'payInsuranceBill'])->name('insurance.pay-premium');
Route::post('/insurance/status', [InsurancePremiumPaymentController::class, 'fetchInsuranceStatus'])->name('insurance.status');

// // LICController Routes (Assumed)
// Route::post('/lic/operators', [LICController::class, 'fetchLICOperators'])->name('lic.operators');
// Route::post('/lic/policy-details', [InsuranceController::class, 'fetchPolicyDetails'])->name('lic.policy-details');
// Route::post('/lic/pay-premium', [LICController::class, 'payPremium'])->name('lic.pay-premium');
// Route::post('/lic/status', [LICController::class, 'fetchStatus'])->name('lic.status');

// LPGController Routes (Assumed)
Route::post('/lpg/operators', [LPGController::class, 'fetchLPGOperators'])->name('lpg.operators');
Route::post('/lpg/book', [LPGController::class, 'bookCylinder'])->name('lpg.book');
Route::post('/lpg/status', [LPGController::class, 'fetchBookingStatus'])->name('lpg.status');

// Municipality Routes
Route::post('/municipality/operators', [MunicipalityController::class, 'fetchMunicipalityOperator'])->name('municipality.operators');
Route::post('/municipality/bill-details', [MunicipalityController::class, 'fetchBillDetails'])->name('municipality.bill-details');
Route::post('/municipality/pay-bill', [MunicipalityController::class, 'payMunicipalityBill'])->name('municipality.pay-bill');
Route::post('/municipality/status', [MunicipalityController::class, 'fetchMunicipalityStatus'])->name('municipality.status');





// Utility Bill Payment Routes
Route::post('/utility/operators', [UtilitybillPaymentController::class, 'fetchOperatorList'])->name('utility.operators');
Route::post('/utility/bill-details', [UtilitybillPaymentController::class, 'fetchBillDetails'])->name('utility.bill-details');
Route::post('/utility/pay-bill', [UtilitybillPaymentController::class, 'processBillPayment'])->name('utility.pay-bill');
Route::post('/utility/status', [UtilitybillPaymentController::class, 'fetchUtilityStatus'])->name('utility.status');

Route::post('/recharge/process', [RechargeController::class, 'processRecharge'])->name('api.recharge.process');
Route::post('/recharge/status', [RechargeController::class, 'fetchRechargeStatus'])->name('api.recharge.status');
Route::post('/recharge/operators', [RechargeController::class, 'getOperators'])->name('api.recharge.operators');

Route::post('/cms-airtel/generate-url', [CMSAirtelController::class, 'generateUrl'])->name('api.cmsairtel.generate');
Route::post('/cms-airtel/transaction-enquiry', [CMSAirtelController::class, 'airtelTransactionEnquiry'])->name('api.cmsairtel.enquiry');










// Route::get('/recharge/status-enquiry', [RechargeController::class, 'storeStatusEnquiry']);

Route::prefix('service_api')->group(function () {

    Route::prefix('dmt2')->group(function () {
        // Api Route for Beneficiary2Controller start
        Route::post('/beneficiaries/register', [Service_Beneficiary21Controller::class, 'registerBeneficiary']);
        Route::post('/beneficiaries/fetch', [Service_Beneficiary21Controller::class, 'fetchBeneficiary']);
        Route::post('/beneficiaries/destroy', [Service_Beneficiary21Controller::class, 'destroyBeneficiary']);
        Route::post('/beneficiaries/fetchBeneficiaryData', [Service_Beneficiary21Controller::class, 'fetchBeneficiaryData']);

        Route::post('/Remitter2/queryRemitter', [Service_Remitter2Controller::class, 'queryRemitter']);
        Route::post('/Remitter2/verifyAadhaar', [Service_Remitter2Controller::class, 'verifyAadhaar']);
        Route::post('/Remitter2/verifyAadhaarWithAPI', [Service_Remitter2Controller::class, 'registerAdhaarRemitter']); //Here this call the verifyAadharWithApi
        Route::post('/Remitter2/registerRemitter', [Service_Remitter2Controller::class, 'registerRemitter']);

        Route::post('/Transaction2/pennyDrop', [Service_Transaction2Controller::class, 'pennyDrop']);
        Route::post('/Transaction2/transactionSentOtp', [Service_Transaction2Controller::class, 'transactionSentOtp']);
        Route::post('/Transaction2/transaction', [Service_Transaction2Controller::class, 'transact']);
        Route::post('/Transaction2/transactionStatus', [Service_Transaction2Controller::class, 'transactionStatus']);

        Route::post('/Refund2/refundOtp', [Service_Refund2Controller::class, 'refundOtp']);
        Route::post('/Refund2/processRefund', [Service_Refund2Controller::class, 'processRefund']);
    });

    Route::prefix('bus')->group(function () {
        Route::get('/busTicket/sourceCities', [Service_BusTicket1Controller::class, 'fetchSourceCities']);
        Route::post('/busTicket/fetchAndStoreAvailableTrips', [Service_BusTicket1Controller::class, 'fetchAndStoreAvailableTrips']);
        Route::post('/busTicket/fetchTripDetails', [Service_BusTicket1Controller::class, 'fetchTripDetails']);
        Route::post('/busTicket/bookandstorebookticket', [Service_BusTicket1Controller::class, 'bookandstorebookticket']);
        Route::post('/busTicket/fetchandstoreboardingpointdetails', [Service_BusTicket1Controller::class, 'fetchandstoreboardingpointdetails']);
    });


    Route::prefix('utility')->group(function () {

        Route::post('/billPayment/operatorList', [Service_UtilitybillPaymentController::class, 'operatorList']);
        Route::post('/billPayment/fetchBillDetails', [Service_UtilitybillPaymentController::class, 'fetchBillDetails']);
        Route::post('/billPayment/processBillPayment', [Service_UtilitybillPaymentController::class, 'processBillPayment']);
        Route::post('/billPayment/fetchUtilityStatus', [Service_UtilitybillPaymentController::class, 'fetchUtilityStatus']);

        Route::get('/FastagRecharge/OperatorList', [FastagRechargeController::class, 'fastagRechargeOperatorList']);
        Route::post('/FastagRecharge/getConsumerDetails', [FastagRechargeController::class, 'getConsumerDetails']);


        //Api Route for FastagRechargeController end;
        Route::post('/LPG/fetchLPGOperator', [Service_LPGController::class, 'fetchLPGOperator']);
        Route::post('/LPG/FetchLPGDetails', [Service_LPGController::class, 'FetchLPGDetails']);
        Route::post('/LPG/payLpgBill', [Service_LPGController::class, 'payLpgBill']);
        Route::post('/LPG/getLPGStatus', [Service_LPGController::class, 'getLPGStatus']);

        //Api Route for InsurancePremiumPaymentController start
        Route::post('/Municipality/fetchMunicipalityOperator', [Service_MunicipalityController::class, 'fetchMunicipalityOperator']);
        Route::post('/Municipality/fetchBillDetails', [Service_MunicipalityController::class, 'fetchBillDetails']);
        Route::post('/Municipality/PayMunicipalityBill', [Service_MunicipalityController::class, 'PayMunicipalityBill']);
        Route::post('/Municipality/MunicipalityEnquiryStatus', [Service_MunicipalityController::class, 'MunicipalityEnquiryStatus']);

        Route::post('/InsurancePremiumPayment/fetchLICBill', [Service_InsurancePremiumPaymentController::class, 'fetchLICBill']);
        Route::post('/InsurancePremiumPayment/payInsuranceBill', [Service_InsurancePremiumPaymentController::class, 'payInsuranceBill']);
        Route::post('/InsurancePremiumPayment/fetchInsuranceStatus', [Service_InsurancePremiumPaymentController::class, 'fetchInsuranceStatus']);
    });
});
