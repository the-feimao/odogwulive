<?php

namespace App\Support;

use GuzzleHttp\Client;
use Unicodeveloper\Paystack\Paystack;

/**
 * The package builds its own Guzzle client without a CA bundle override. On
 * some local Windows PHP installs that makes Paystack fail with cURL error 60.
 * This subclass keeps the package API intact and only supplies a project-local
 * CA file, so no machine-wide php.ini change is needed.
 */
class PaystackWithLocalCa extends Paystack
{
    public function __construct()
    {
        $this->setKey();
        $this->setBaseUrl();

        $caBundle = (string) config('paystack.caBundle', '');
        $verify = $caBundle !== '' && is_file($caBundle) ? $caBundle : true;

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Authorization' => 'Bearer '.$this->secretKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'verify' => $verify,
        ]);
    }
}
