<?php

namespace App\Repositories;

use App\Exceptions\DocumentDuplicatedInvoiceException;
use App\Exceptions\DuplicatedInvoiceException;
use App\Exceptions\InvalidCustomerDataException;
use App\Exceptions\InvalidInvoiceDataException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InvoiceUploadRepository
{
    public function __construct() {}

    public function processInvoiceUpload(UploadedFile $spreadsheet): array {
        $formatted_spreadsheet = $this->loadInvoicesFromSpreadsheet($spreadsheet);
        try {
            DB::beginTransaction();
            $customers = $this->processCustomersFromInvoiceUpload($formatted_spreadsheet);
            $this->processInvoicesFromInvoiceUpload($formatted_spreadsheet, $customers);
            DB::commit();
        } catch (InvalidCustomerDataException |
                InvalidInvoiceDataException |
                DuplicatedInvoiceException |
                DocumentDuplicatedInvoiceException $e) {
            DB::rollBack();
            return ['msg' => $e->getMessage()];
        }
        return [];
    }

    private function loadInvoicesFromSpreadsheet(UploadedFile $spreadsheet): array {
        $spreadsheet_data = $spreadsheet->get();
        $lines = explode(PHP_EOL, $spreadsheet_data);
        $formatted_spreadsheet = [];

        foreach ($lines as $data) {
            $formatted_spreadsheet[] = str_getcsv($data);
        }
        
        return $formatted_spreadsheet;
    }

    private function processCustomersFromInvoiceUpload(array $formatted_spreadsheet): Collection {
        $customer_repository = new CustomerRepository();
        return $customer_repository->processCustomersFromInvoiceUpload($formatted_spreadsheet);
    }

    private function processInvoicesFromInvoiceUpload(array $formatted_spreadsheet, Collection $customers): void {
        $invoice_repository = new InvoiceRepository();
        $invoice_repository->processInvoicesFromInvoiceUpload($formatted_spreadsheet, $customers);
    }
}
