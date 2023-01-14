<?php

namespace App\Repositories;

use App\Exceptions\InvalidCustomerDataException;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;

class CustomerRepository
{
    public function __construct() {}

    public function processCustomersFromInvoiceUpload(array $formatted_spreadsheet): SupportCollection {
        $this->validateCustomerInputs($formatted_spreadsheet);
        
        $customer_documents = $this->getDocumentsFromSpreadsheet($formatted_spreadsheet);
        $existing_customers = $this->loadExistingCustomersByDocumentNumber($customer_documents);
        
        $customers = $this->filterAndCreateMissingCustomers($formatted_spreadsheet, $existing_customers);
        $this->insertCustomerArray($customers);

        return $this->loadExistingCustomersByDocumentNumber($customer_documents);
    }

    private function validateCustomerInputs(array $formatted_spreadsheet): void {
        $failing_lines = [];

        foreach (array_slice($formatted_spreadsheet, 1) as $line => $customer) {
            //0 => name, 1 => document, 2 => email
            if (!$customer[0] || !$customer[1] || !$customer[2]) {
                $failing_lines[] = $line + 2;
                continue;
            }

            if (strlen($customer[1]) !== 11 && strlen($customer[1]) !== 14) {
                $failing_lines[] = $line + 2;
                continue;
            }

            if (!filter_var($customer[2], FILTER_VALIDATE_EMAIL)) {
                $failing_lines[] = $line + 2;
                continue;
            }
        }

        if ($failing_lines) {
            throw new InvalidCustomerDataException($failing_lines);
        }
    }

    private function getDocumentsFromSpreadsheet(array $formatted_spreadsheet): array {
        $documents = [];
        foreach (array_slice($formatted_spreadsheet, 1) as $customer) {
            $documents[] = $customer[1];
        }

        return $documents;
    }

    private function loadExistingCustomersByDocumentNumber(array $customer_documents): SupportCollection {
        return DB::table('customers')->whereIn('document', $customer_documents)->get();
    }

    private function filterAndCreateMissingCustomers(array $formatted_spreadsheet, SupportCollection $existing_customers): Collection {
        $customers = new Collection();

        foreach (array_slice($formatted_spreadsheet, 1) as $customer) {
            $document = $customer[1];
            $customer_exists = $existing_customers->filter(function($customer) use($document) {
                return $customer->document == $document;
            })->first();
            
            if ($customer_exists) {
                continue;
            }
            
            $name = $customer[0];
            $email = $customer[2];

            $customers->add(new Customer([
                'name' => $name,
                'document' => $document,
                'email' => $email,
            ]));
        }

        return $customers;
    }

    private function insertCustomerArray(Collection $customers): void {
        Customer::insert($customers->toArray());
    }
}
