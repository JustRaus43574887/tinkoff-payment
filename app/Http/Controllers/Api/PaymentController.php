<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PaymentRequest;
use App\Models\Payment;
use App\Transformers\PaymentTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Dingo\Api\Routing\Helpers;

class PaymentController extends Controller
{
    use Helpers;

    /**
     * Init payment
     *
     * @param PaymentRequest $request
     * @return \Dingo\Api\Http\Response
     */
    public function initPayment(PaymentRequest $request)
    {
        /**
         * Создаем платеж
         */
        $payment = Payment::query()->create(
            $request->all()
        );

        /**
         * Генерируем инвойс тинькофф
         */
        $http = Http::post(env('TINKOFF_API_URL').'Init', [
            'TerminalKey' => env('TINKOFF_API_TERMINAL_KEY'),
            'Amount' => $request->get('amount') * 100,
            'OrderId' => $payment->id,
            'Description' => $payment->description,
            'NotificationURL' => env('TINKOFF_API_CALLBACK_URL'),
            'SuccessURL' => 'https://admin.appinion.digital'
        ]);

        $tinkoffPayment = $http->collect();

        /**
         * Обновляем платеж
         */
        if ($tinkoffPayment->get('Success')) {
            /**
             * Платеж создан
             */
            $payment->update([
                'url' => $tinkoffPayment->get('PaymentURL'),
                'status' => Payment::PAYMENT_CREATE_SUCCESS
            ]);
        } else {
            /**
             * Платеж не создан из-за ошибки в тинькофф
             */
            $payment->update([
                'status' => Payment::PAYMENT_CREATE_ERROR
            ]);

            /**
             * Пишем логи
             */
            Log::error('Payment '.$payment->id
                .' error with tinkoff. Error code: '.$tinkoffPayment->get('ErrorCode'));
        }

        /**
         * Записываем информацию об ответе
         */
        $payment->feedbacks()->create([
            'status' => $http->status(),
            'feedback' => $tinkoffPayment->toJson()
        ]);

        return $this->response->item($payment, new PaymentTransformer());
    }

    /**
     * Get payment info
     *
     * @param Payment $payment
     * @return \Dingo\Api\Http\Response
     */
    public function infoPayment(Payment $payment)
    {
        return $this->response->item($payment, new PaymentTransformer());
    }

    /**
     * Список платежей пользователя
     *
     * @param $userId
     * @return \Dingo\Api\Http\Response
     */
    public function getPaymentsUser($userId)
    {
        /**
         * Получение платежей
         */
        $payments = Payment::query()->where('user_id', $userId)->get();

        return $this->response->collection($payments, new PaymentTransformer());
    }

    /**
     * Feedback from tinkoff
     *
     * @param Request $request
     * @return \Dingo\Api\Http\Response
     */
    public function feedback(Request $request)
    {
        /**
         * Ищем платеж в БД
         */
        $payment = Payment::query()->findOrFail(
            $request->get('OrderId')
        );

        /**
         * Если оплата успешна
         */
        if ($request->get('Status') == 'CONFIRMED') {
            # Статус платежа
            $status = Payment::PAYMENT_SUCCESS;
            # Статус ответа
            $statusFeedback = 200;
        }
        /**
         * Если произошла ошибка
         */
        if ($request->get('Status') == 'REJECTED') {
            # Статус платежа
            $status = Payment::PAYMENT_ERROR;
            # Статус ответа
            $statusFeedback = 500;
        }

        /**
         * Записываем фитбек тинькофф
         */
        $payment->feedbacks()->create([
            'status' => $statusFeedback,
            'feedback' => $request->all()
        ]);

        /**
         * Обновляем статус платежа
         */
        $payment->update([
            'status' => $status
        ]);

        return $this->response->accepted();
    }
}
