<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidAmountForPaymentException;
use App\Repositories\InvoiceRepository;
use App\Repositories\InvoiceUploadRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class InvoiceController extends Controller
{
    private InvoiceRepository $repository;

    public function __construct() {
        $this->repository = new InvoiceRepository;
    }

    public function uploadInvoices() {
        return view('upload-invoices');
    }

    public function postInvoiceUpload(Request $request) {
        $request->validate([
            'spreadsheet' => 'required|mimes:csv,txt',
        ]);

        $spreadsheet = $request->file('spreadsheet');
        
        $invoice_upload_repository = new InvoiceUploadRepository();
        $errors = $invoice_upload_repository->processInvoiceUpload($spreadsheet);
        if ($errors) {
            return Redirect::back()->withErrors($errors);
        }

        return redirect('/');
    }

    public function postExternalInvoiceUpload(Request $request) {
        $request->validate([
            'spreadsheet' => 'required|mimes:csv,txt',
        ]);

        $spreadsheet = $request->file('spreadsheet');
        $invoice_upload_repository = new InvoiceUploadRepository();
        $errors = $invoice_upload_repository->processInvoiceUpload($spreadsheet);
        if ($errors) {
            return response(json_encode($errors), 400);
        }
        
        return response('Ok', 200);
    }

    public function payInvoice(Request $request) {
        $request->validate([
            'debtId' => 'required|numeric',
            'paidAt' => 'required|date',
            'paidAmount' => 'required|numeric',
            'paidBy' => 'required|string',
        ]);
        
        try {
            $invoice = $this->repository->payInvoice($request);
        } catch (InvalidAmountForPaymentException $e) {
            return response($e->getMessage(), 400);
        }
        
        return response(json_encode($invoice), 200);
    }
}
