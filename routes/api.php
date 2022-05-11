<?php

use Dingo\Api\Routing\Router;

/** @var Router $api */
$api = app(Router::class);

/**
 * API v1
 */
$api->version('v1', function (Router $api) {
    /**
     * Payment routes
     */
    $api->group([
        'prefix' => 'payment',
        'middleware' => 'bindings'
    ], function (Router $api) {
        /**
         * Init pay and save to DB
         */
        $api->post('init', 'App\Http\Controllers\Api\PaymentController@initPayment');
        /**
         * Get info from payment
         */
        $api->get('info/{payment}', 'App\Http\Controllers\Api\PaymentController@infoPayment');
        /**
         * Get user payments
         */
        $api->get('all/{userId}', 'App\Http\Controllers\Api\PaymentController@getPaymentsUser');
        /**
         * Feedback tinkoff
         */
        $api->post('feedback', 'App\Http\Controllers\Api\PaymentController@feedback');
    });
});
