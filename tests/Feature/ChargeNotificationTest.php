<?php

namespace Tests\Feature;

use App\Models\ChargeNotification;
use Carbon\Carbon;
use Tests\MockUtils;
use Tests\TestCase;

class ChargeNotificationTest extends TestCase
{
    public function test_chargeNotification_shouldCreateACharge()
    {
        $customer = MockUtils::mockCustomer();
        $invoice = MockUtils::mockInvoice(10, 1000, $customer->id);

        $response = $this->get('/api/charge-customers');
        $response->assertStatus(200);

        $charge_notification = $this->getChargeNotificationFromInvoiceId($invoice->id);
        
        $this->assertEquals($charge_notification->charged_at->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        $this->assertEquals($charge_notification->boleto_generation_date->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        $this->assertEquals($charge_notification->boleto_expiry_date, $invoice->debt_due_date);
        $this->assertEquals($charge_notification->boleto_amount, $invoice->debt_amount);
        $this->assertEquals($charge_notification->boleto_customer_document, $customer->document);
        $this->assertEquals($charge_notification->invoice_id, $invoice->id);
    }

    public function test_chargeNotification_shouldCreateNewCharges_whenDebtIsModified()
    {
        $customer = MockUtils::mockCustomer();
        $invoice = MockUtils::mockInvoice(10, 1000, $customer->id);

        $response = $this->get('/api/charge-customers');
        $response->assertStatus(200);

        $first_notification = $this->getChargeNotificationFromInvoiceId($invoice->id);
        
        $this->assertEquals($first_notification->charged_at->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        $this->assertEquals($first_notification->boleto_generation_date->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        $this->assertEquals($first_notification->boleto_expiry_date, $invoice->debt_due_date);
        $this->assertEquals($first_notification->boleto_amount, $invoice->debt_amount);
        $this->assertEquals($first_notification->boleto_customer_document, $customer->document);
        $this->assertEquals($first_notification->invoice_id, $invoice->id);

        $invoice->debt_amount -= 5;
        $invoice->save();

        $response = $this->get('/api/charge-customers');
        $response->assertStatus(200);

        $second_notification = $this->getChargeNotificationFromInvoiceId($invoice->id);
        
        $this->assertEquals($second_notification->charged_at->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        $this->assertEquals($second_notification->boleto_generation_date->format('Y-m-d'), Carbon::now()->format('Y-m-d'));
        $this->assertEquals($second_notification->boleto_expiry_date, $invoice->debt_due_date);
        $this->assertEquals($second_notification->boleto_amount, $invoice->debt_amount);
        $this->assertEquals($second_notification->boleto_customer_document, $customer->document);
        $this->assertEquals($second_notification->invoice_id, $invoice->id);
        $this->assertNotEquals($second_notification->boleto_amount, $first_notification->boleto_amount);
        $this->assertNotEquals($second_notification->boleto_code, $first_notification->boleto_code);
    }
    
    public function test_chargeNotification_shouldNotCreateACharge_whenDebtIsPaid()
    {
        $customer = MockUtils::mockCustomer();
        $invoice = MockUtils::mockInvoice(10, 1000, $customer->id);
        $invoice->paid = true;
        $invoice->debt_amount = 0;
        $invoice->save();

        $response = $this->get('/api/charge-customers');
        $response->assertStatus(200);

        $charge_notification = $this->getChargeNotificationFromInvoiceId($invoice->id);
        
        $this->assertNull($charge_notification);
    }

    private function getChargeNotificationFromInvoiceId(int $invoice_id) {
        return ChargeNotification::where('invoice_id', $invoice_id)->orderBy('charged_at', 'desc')->orderBy('id', 'desc')->first();
    }
}
