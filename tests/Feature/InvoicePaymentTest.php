<?php

namespace Tests\Feature;

use Tests\MockUtils;
use Tests\TestCase;

class InvoicePaymentTest extends TestCase
{
    public function test_invoicePayment_withPartialValues_shouldSucceed()
    {
        $customer = MockUtils::mockCustomer();
        $invoice = MockUtils::mockInvoice(10, 1000, $customer->id);

        $request_params = [
            "debtId" => 1000,
            "paidAt" => "2022-06-09 15:00:00",
            "paidAmount" => $paid_amount = 5,
            "paidBy" => "John Doe"
        ];

        $response = $this->postJson('/api/pay-invoice', $request_params);

        $response->assertStatus(200);
        $invoice->refresh();
        
        $this->assertFalse($invoice->paid);
        $this->assertEquals($invoice->debt_amount, $invoice->initial_debt_amount - $paid_amount);
    }

    public function test_invoicePayment_withTotalValues_shouldSucceed()
    {
        $customer = MockUtils::mockCustomer();
        $invoice = MockUtils::mockInvoice(10, 1000, $customer->id);

        $request_params = [
            "debtId" => 1000,
            "paidAt" => "2022-06-09 15:00:00",
            "paidAmount" => 10,
            "paidBy" => "John Doe"
        ];

        $response = $this->postJson('/api/pay-invoice', $request_params);

        $response->assertStatus(200);
        $invoice->refresh();
        
        $this->assertTrue($invoice->paid);
        $this->assertEquals($invoice->debt_amount, 0);
    }

    public function test_invoicePayment_withValueAboveDebt_shouldFail()
    {
        $customer = MockUtils::mockCustomer();
        $invoice = MockUtils::mockInvoice(10, 1000, $customer->id);

        $request_params = [
            "debtId" => 1000,
            "paidAt" => "2022-06-09 15:00:00",
            "paidAmount" => 100,
            "paidBy" => "John Doe"
        ];

        $response = $this->postJson('/api/pay-invoice', $request_params);

        $response->assertStatus(400);
        $response->assertContent('The payment amount is larger than the actual debt of R$10');
        $invoice->refresh();
        
        $this->assertFalse($invoice->paid);
        $this->assertEquals($invoice->debt_amount, $invoice->initial_debt_amount);
    }
}
