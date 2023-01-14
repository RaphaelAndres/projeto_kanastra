<?php

namespace App\Repositories;

use App\Exceptions\DocumentDuplicatedInvoiceException;
use App\Exceptions\DuplicatedInvoiceException;
use App\Exceptions\InvalidAmountForPaymentException;
use App\Exceptions\InvalidInvoiceDataException;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;

class InvoiceRepository
{
    public function __construct() {}

    public function processInvoicesFromInvoiceUpload(array $formatted_spreadsheet, SupportCollection $customers): void {
        $this->validateInvoiceInputs($formatted_spreadsheet);
        
        $invoice_documents = $this->getDebtIdsFromSpreadsheet($formatted_spreadsheet);
        $existing_invoices = $this->loadExistingInvoicesByDebtId($invoice_documents);

        $this->validateRepeatedDebtIds($formatted_spreadsheet, $existing_invoices);
        $this->validateRepeatedDebtIdsInDocument($formatted_spreadsheet);
        
        $invoices = $this->createMissingInvoices($formatted_spreadsheet, $customers);
        $this->insertInvoiceArray($invoices);
    }

    private function validateInvoiceInputs(array $formatted_spreadsheet): void {
        $failing_lines = [];

        foreach (array_slice($formatted_spreadsheet, 1) as $line => $invoice) {
            //3 => debtAmount, 4 => debtDueDate, 5 => debtId
            if (!$invoice[3] || !$invoice[4] || !$invoice[5]) {
                $failing_lines[] = $line + 2;
                continue;
            }

            if ($invoice[3] <= 0) {
                $failing_lines[] = $line + 2;
                continue;
            }

            if (Carbon::createFromFormat('Y-m-d', $invoice[4]) < Carbon::now()) {
                $failing_lines[] = $line + 2;
                continue;
            }
        }

        if ($failing_lines) {
            throw new InvalidInvoiceDataException($failing_lines);
        }
    }

    private function getDebtIdsFromSpreadsheet(array $formatted_spreadsheet): array {
        $debt_ids = [];
        foreach (array_slice($formatted_spreadsheet, 1) as $invoice) {
            $debt_ids[] = $invoice[5];
        }

        return $debt_ids;
    }

    private function loadExistingInvoicesByDebtId(array $debt_ids): SupportCollection {
        return DB::table('invoices')->whereIn('debt_id', $debt_ids)->get();
    }

    private function validateRepeatedDebtIds(array $formatted_spreadsheet, SupportCollection $existing_invoices) {
        $failing_lines = [];

        foreach (array_slice($formatted_spreadsheet, 1) as $line => $invoice) {
            $debt_id = (int) $invoice[5];
            $invoice_exists = $existing_invoices->filter(function($invoice) use($debt_id) {
                return $invoice->debt_id == $debt_id;
            })->first();
            
            if ($invoice_exists) {
                $failing_lines[] = $line + 2;
            }
        }

        if ($failing_lines) {
            throw new DuplicatedInvoiceException($failing_lines);
        }
    }

    private function validateRepeatedDebtIdsInDocument(array $formatted_spreadsheet) {
        $failing_lines = [];

        foreach (array_slice($formatted_spreadsheet, 1) as $base_line => $base_invoice) {
            foreach (array_slice($formatted_spreadsheet, 1) as $final_line => $final_invoice) {
                if ($final_invoice[5] === $base_invoice[5] && $base_line !== $final_line) {
                    $failing_lines[] = "[".($base_line + 2).", ".($final_line + 2)."]";
                    continue;
                }
            }
        }

        if ($failing_lines) {
            throw new DocumentDuplicatedInvoiceException($failing_lines);
        }
    }

    private function createMissingInvoices(array $formatted_spreadsheet, SupportCollection $customers): Collection {
        $invoices = new Collection();

        foreach (array_slice($formatted_spreadsheet, 1) as $invoice) {
            $document = $invoice[1];

            $customer = $customers->filter(function($customer) use ($document) {
                return $customer->document == $document;
            })->first();

            $debt_amount = $invoice[3];
            $debt_due_date = $invoice[4];
            $debt_id = $invoice[5];
            
            $invoices->add(new Invoice([
                'debt_amount' => $debt_amount,
                'initial_debt_amount' => $debt_amount,
                'debt_due_date' => $debt_due_date,
                'debt_id' => $debt_id,
                'customer_id' => $customer->id,
            ]));
        }

        return $invoices;
    }

    private function insertInvoiceArray(Collection $invoices): void {
        Invoice::insert($invoices->toArray());
    }

    public function payInvoice(Request $request): Invoice {
        $debt_id = $request['debtId'];
        $invoice = $this->getInvoiceByDebtId($debt_id);
        
        $payment_amount = $request['paidAmount'];

        if ($payment_amount > $invoice->debt_amount) {
            throw new InvalidAmountForPaymentException('The payment amount is larger than the actual debt of R$'.$invoice->debt_amount);
        }

        if ($payment_amount == $invoice->debt_amount) {
            $invoice->paid = true;
        }
        
        $invoice->debt_amount -= $payment_amount;
        $invoice->updated_at = $request['paidAt'];
        $invoice->save();

        return $invoice;
    }

    private function getInvoiceByDebtId(string $debt_id): Invoice {
        return Invoice::where('debt_id', $debt_id)->first();
    }

    public function getPendingInvoicesWithCustomerData(): Collection {
        return Invoice::with('customer')->where('debt_due_date', '>=', Carbon::now()->format('Y-m-d'))->where('paid', false)->get();
    }
}
