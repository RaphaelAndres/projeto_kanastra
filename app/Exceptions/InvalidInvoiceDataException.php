<?php

namespace App\Exceptions;

use Exception;

class InvalidInvoiceDataException extends Exception 
{
    public function __construct(array $lines) {
        parent::__construct();
        $this->message = 'Invalid data given for invoice(s) in line(s): '.implode(", ",$lines);
    }
}
