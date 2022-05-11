<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    const PAYMENT_INIT = 0; # Платеж прониацилизирован
    const PAYMENT_SUCCESS = 1; # Платеж был оплачен
    const PAYMENT_ERROR = 2; # Платеж не был оплачен
    const PAYMENT_CREATE_SUCCESS = 3; # Платеж создан, фитбек тинькофф записан
    const PAYMENT_CREATE_ERROR = 4; # Платеж не создан, фитбек тинькофф не записан

    /**
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'amount',
        'description',
        'url',
        'status'
    ];

    protected $with = [
        'feedbacks'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function feedbacks()
    {
        return $this->hasMany(PaymentLog::class);
    }
}
