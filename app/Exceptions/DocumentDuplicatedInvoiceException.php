<?php

namespace App\Exceptions;

use Exception;

class DocumentDuplicatedInvoiceException extends Exception
{
    public function __construct(array $lines) {
        parent::__construct();
        $this->message = 'Debt ID duplicated in the following pair(s) of line(s): '.implode(", ",$lines);
    }
}
