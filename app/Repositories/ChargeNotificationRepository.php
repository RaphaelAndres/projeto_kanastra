<?php

namespace App\Repositories;

use App\Models\ChargeNotification;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class ChargeNotificationRepository
{
    public function __construct() {}

    public function chargePendingInvoices() {
        $pending_invoices = $this->getPendingInvoicesWithCustomerData();
        $charge_notifications = $this->generateChargeNotifications($pending_invoices);
        
        $this->SaveChargeNotifications($charge_notifications);
    }

    private function getPendingInvoicesWithCustomerData(): Collection {
        $invoice_repository = new InvoiceRepository();
        return $invoice_repository->getPendingInvoicesWithCustomerData();
    }

    private function generateChargeNotifications(Collection $pending_invoices): Collection {
        $pending_notifications = new Collection;
        foreach($pending_invoices as $invoice) {
            $old_charge_notification = $this->getLastChargeNotificationForInvoice($invoice->id);
            $charge_notification = null;

            if (!$old_charge_notification || $old_charge_notification->boleto_amount !== $invoice->debt_amount) {
                $charge_notification = $this->generateBoletoData($invoice);
            }
            if (!$charge_notification) {
                $charge_notification = $this->mountBoletoDataBasedOnChargeNotification($old_charge_notification);
            }

            $charge_notification->invoice_id = $invoice->id;
            $pending_notifications->add($charge_notification);

            $this->notify($charge_notification);
        }

        return $pending_notifications;
    }

    private function getLastChargeNotificationForInvoice(int $invoice_id): ?ChargeNotification {
        return ChargeNotification::where('invoice_id', $invoice_id)->orderBy('boleto_generation_date', 'desc')->first();
    }

    private function generateBoletoData(Invoice $invoice): ChargeNotification {
        return new ChargeNotification([
            'boleto_code' => rand(10000, 99999),
            'boleto_generation_date' => Carbon::now()->format('Y-m-d'),
            'boleto_expiry_date' => $invoice->debt_due_date,
            'boleto_amount' => $invoice->debt_amount,
            'boleto_customer_document' => $invoice->customer->document,
        ]);
    }

    private function mountBoletoDataBasedOnChargeNotification(ChargeNotification $charge_notification): ChargeNotification {
        return new ChargeNotification([
            'boleto_code' => $charge_notification->boleto_code,
            'boleto_generation_date' => $charge_notification->boleto_generation_date,
            'boleto_expiry_date' => $charge_notification->boleto_expiry_date,
            'boleto_amount' => $charge_notification->boleto_amount,
            'boleto_customer_document' => $charge_notification->boleto_customer_document,
        ]);
    }
    
    private function notify(ChargeNotification $charge_notification) {
        //this would be the method responsible for notifying the customer of the debt, along with the generated/previously used email.
    }

    private function SaveChargeNotifications(Collection $charge_notifications) {
        ChargeNotification::insert($charge_notifications->toArray());
    }
}
