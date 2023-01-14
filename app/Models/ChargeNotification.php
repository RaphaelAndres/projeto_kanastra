<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChargeNotification extends Model
{
    use HasFactory;

    protected $table = 'charge_notifications';
    protected $primaryKey = 'id';
    public $timestamps = false;
    
    protected $fillable = [
        'charged_at',
        'boleto_code',
        'boleto_generation_date',
        'boleto_expiry_date',
        'boleto_amount',
        'boleto_customer_document',
        'invoice_id',
    ];

    protected $dates = [
        'charged_at',
    ];

    protected $casts = [
        'boleto_code' => 'string',
        'boleto_expiry_date' => 'date:Y-m-d',
        'boleto_generation_date' => 'date:Y-m-d',
        'boleto_amount' => 'double',
        'boleto_customer_document' => 'string',
    ];

    public function invoice() {
        return $this->belongsTo(Invoice::class);
    }
}
