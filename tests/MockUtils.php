<?php

namespace Tests;

use App\Models\Customer;
use App\Models\Invoice;
use Carbon\Carbon;

class MockUtils
{
    public static function mockCustomer($name = "john", $document = "01234567890", $email = "john@doe.com") {
        $customer = new Customer([
            'name' => $name,
            'document' => $document,
            'email' => $email,
        ]);
        $customer->save();
        return $customer;
    }
    
    public static function mockInvoice(float $amount, int $debt_id, int $customer_id) {
        $invoice = new Invoice([
            "initial_debt_amount" => $amount,
            "debt_amount" => $amount,
            "debt_id" => $debt_id,
            "debt_due_date" => Carbon::now()->addDays(10)->format('Y-m-d'),
            "customer_id" => $customer_id,
        ]);
        $invoice->save();
        return $invoice;
    }
}