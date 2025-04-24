<?php

use App\Http\Controllers\API\Beneficiary2Controller;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\BusTicketController;
use App\Http\Controllers\API\DMTBank1Controller;
use App\Http\Controllers\API\InsuranceController;
use App\Http\Controllers\API\LICController;
use App\Http\Controllers\API\LPGController;
use App\Http\Controllers\API\MunicipalityController;
use App\Http\Controllers\API\Refund2Controller;
use App\Http\Controllers\API\Remitter2Controller;
use App\Http\Controllers\API\Transaction2Controller;
use App\Http\Controllers\API\UtilitybillPaymentController;

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


// Transaction2 Routes
Route::post('/transactions/penny-drop', [Transaction2Controller::class, 'pennyDrop'])->name('transactions.penny-drop');
Route::post('/transactions/send-otp', [Transaction2Controller::class, 'transactionSentOtp'])->name('transactions.send-otp');
Route::post('/transactions/transact', [Transaction2Controller::class, 'transact'])->name('transactions.transact');
Route::post('/transactions/status', [Transaction2Controller::class, 'transactionStatus'])->name('transactions.status');

// Refund2 Routes
Route::post('/refunds/otp', [Refund2Controller::class, 'refundOtp'])->name('refunds.otp');
Route::post('/refunds/process', [Refund2Controller::class, 'processRefund'])->name('refunds.process');

// BusTicketController Routes (Assumed)
Route::post('/bus-tickets/operators', [BusTicketController::class, 'fetchBusOperators'])->name('bus-tickets.operators');
Route::post('/bus-tickets/search', [BusTicketController::class, 'searchBus'])->name('bus-tickets.search');
Route::post('/bus-tickets/book', [BusTicketController::class, 'bookTicket'])->name('bus-tickets.book');
Route::post('/bus-tickets/status', [BusTicketController::class, 'fetchTicketStatus'])->name('bus-tickets.status');

// DMTBank1Controller Routes (Assumed)
Route::post('/dmt-bank/verify', [DMTBank1Controller::class, 'verifyBankDetails'])->name('dmt-bank.verify');
Route::post('/dmt-bank/transfer', [DMTBank1Controller::class, 'processBankTransfer'])->name('dmt-bank.transfer');
Route::post('/dmt-bank/status', [DMTBank1Controller::class, 'fetchTransferStatus'])->name('dmt-bank.status');

// InsuranceController Routes (Assumed)

Route::post('/insurance/premium-details', [InsuranceController::class, 'fetchLICBill'])->name('insurance.premium-details');
Route::post('/insurance/pay-premium', [InsuranceController::class, 'payInsuranceBill'])->name('insurance.pay-premium');
Route::post('/insurance/status', [InsuranceController::class, 'fetchInsuranceStatus'])->name('insurance.status');

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