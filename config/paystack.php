<?php

/*
 * This file is part of the Laravel Paystack package.
 *
 * (c) Prosper Otemuyiwa <prosperotemuyiwa@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [

    /**
     * Public Key From Paystack Dashboard
     *
     */
    'publicKey' => getenv('PAYSTACK_PUBLIC_KEY'),

    /**
     * Secret Key From Paystack Dashboard
     *
     */
    'secretKey' => getenv('PAYSTACK_SECRET_KEY'),

    /**
     * Paystack Payment URL
     *
     */
    'paymentUrl' => getenv('PAYSTACK_PAYMENT_URL') ?: 'https://api.paystack.co',

    /**
     * Optional CA bundle for local development machines where PHP cannot find
     * a system trust store. The bundled file is project-local, not global.
     */
    'caBundle' => getenv('PAYSTACK_CA_BUNDLE') ?: base_path('certs/cacert.pem'),

    /**
     * Webhook signing secrets (comma-separated for rotation windows).
     * Paystack signs webhooks with a whsec_... secret when one is active.
     */
    'webhookSecrets' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) getenv('PAYSTACK_WEBHOOK_SECRET'))
    ))),

    /**
     * Optional email address of the merchant
     *
     */
    'merchantEmail' => getenv('MERCHANT_EMAIL'),

];
