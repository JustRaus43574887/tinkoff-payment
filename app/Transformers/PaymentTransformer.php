<?php

namespace App\Transformers;

use App\Models\Payment;
use League\Fractal\TransformerAbstract;

class PaymentTransformer extends TransformerAbstract
{
    /**
     * @param Payment $payment
     * @return array
     */
    public function transform(Payment $payment)
    {
        return [
            'id' => $payment->id,
            'url' => $payment->url,
            'userId' => $payment->user_id,
            'amount' => $payment->amount,
            'description' => $payment->description,
            'status' => $payment->status,
            'feedbacks' => $payment->feedbacks,
            'dates' => [
                'created_at' => $payment->created_at,
                'updated_at' => $payment->updated_at
            ]
        ];
    }
}
