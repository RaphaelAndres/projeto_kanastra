<?php

namespace App\Exceptions;

use Exception;

class DuplicatedInvoiceException extends Exception
{
    public function __construct(array $lines) {
        parent::__construct();
        $this->message = 'Debt ID already being used in invoice(s) in line(s): '.implode(", ",$lines);
    }
}
