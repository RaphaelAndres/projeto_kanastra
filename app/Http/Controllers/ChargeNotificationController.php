<?php

namespace App\Http\Controllers;

use App\Repositories\ChargeNotificationRepository;

class ChargeNotificationController extends Controller
{
    public static function chargePendingInvoices() {
        echo 'Trying to send charge notifications.';
        $repository = new ChargeNotificationRepository;
        $repository->chargePendingInvoices();
    }
}
