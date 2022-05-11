<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'payment_id',
        'status',
        'feedback'
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'feedback' => 'array'
    ];
}
