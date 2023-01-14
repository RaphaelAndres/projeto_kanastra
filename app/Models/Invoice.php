<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'invoices';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'initial_debt_amount',
        'debt_amount',
        'debt_due_date',
        'debt_id',
        'paid',
        'customer_id',
    ];

    protected $casts = [
        'initial_debt_amount' => 'double',
        'debt_amount' => 'double',
        'debt_id' => 'int',
        'paid' => 'bool',
        'debt_due_date' => 'date:Y-m-d',
    ];

    protected $attributes = [
        'paid' => false,
    ];

    public function customer() {
        return $this->belongsTo(Customer::class);
    }

    public function chargeNotifications() {
        return $this->hasMany(ChargeNotification::class);
    }
}
