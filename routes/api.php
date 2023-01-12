<?php

use App\Http\Controllers\InvoiceController;
use App\Models\Customer;
use App\Models\Invoice;
use Carbon\Carbon;
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

Route::post('/upload-invoices', [InvoiceController::class, 'postInvoiceUpload'])->name('postUploadInvoices');

Route::get('/test', function() {
    dd(Invoice::get());
    $customer = new Customer(['name' => 'jorge', 'document' => '1234567890', 'email' => '123@teste.com']);
    $customer->save();
    $invoice = new Invoice();
    $invoice->debt_amount = 100.54;
    $invoice->debt_due_date = Carbon::now()->format('Y-m-d');;
    $invoice->external_debt_id = 123;
    $invoice->customer_id = $customer->id;
    $invoice->save();
    dd($customer);
});
