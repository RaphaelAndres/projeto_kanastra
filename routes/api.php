<?php

use App\Http\Controllers\ChargeNotificationController;
use App\Http\Controllers\InvoiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/internal-upload-invoices', [InvoiceController::class, 'postInvoiceUpload'])->name('postUploadInvoices');
Route::post('/upload-invoices', [InvoiceController::class, 'postExternalInvoiceUpload']);

Route::post('/pay-invoice', [InvoiceController::class, 'payInvoice'])->name('payInvoice');

Route::get('/charge-customers', [ChargeNotificationController::class, 'chargePendingInvoices']);