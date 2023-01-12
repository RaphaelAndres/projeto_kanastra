<?php

namespace App\Http\Controllers;

use App\Repositories\InvoiceRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\File;

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
            'spreadsheet' => [
                'required',
                File::types(['csv'])
            ],
        ]);

        $spreadsheet = $request->file('spreadsheet');
        
        $this->repository->processInvoiceUpload($spreadsheet);
        return redirect('/');
    }

    public function customerList() {
        return view('customer-list');
    }
}
