<?php

namespace App\Exceptions;

use Exception;

class InvalidCustomerDataException extends Exception 
{
    public function __construct(array $lines) {
        parent::__construct();
        $this->message = 'Invalid data given for costumer(s) in line(s): '.implode(", ",$lines);
    }
}
