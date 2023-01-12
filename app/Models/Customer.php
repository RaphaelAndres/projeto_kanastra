<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customers';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'name',
        'document',
        'email',
    ];

    protected $casts = [
        'name' => 'string',
        'document' => 'string',
        'email' => 'string',
    ];

}
