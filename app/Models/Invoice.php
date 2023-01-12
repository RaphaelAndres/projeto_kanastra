<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'invoices';
    protected $primaryKey = 'hash';
    
    protected $fillable = [
        'debt_amount',
        'debt_due_date',
        'external_debt_id',
        'paid',
        'customer_id',
    ];
    
    protected $dates = [
        'debt_due_date',
    ];

    protected $casts = [
        'debt_amount' => 'double',
        'external_debt_id' => 'int',
        'paid' => 'bool',
    ];

    protected $attributes = [
        'paid' => false,
    ];

}
