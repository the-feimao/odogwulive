<?php

namespace App\Support;

use App\Models\PaymentSetting;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;

/**
 * Thin Paystack API client for organisation payouts.
 *
 * The installed Paystack package handles checkout but has no transfer API.
 * This class uses the same configured secret key and project-local CA bundle
 * as checkout, and keeps all secret handling on the server.
 */
class PaystackTransferClient
{
    public function banks(string $country = 'nigeria'): array
    {
        $response = $this->request('GET', '/bank', [
            'query' => [
                'country' => $country,
                'perPage' => 100,
            ],
        ]);

        return collect($response['data'] ?? [])
            ->map(fn ($bank) => [
                'code' => (string) ($bank['code'] ?? ''),
                'name' => (string) ($bank['name'] ?? ''),
            ])
            ->filter(fn ($bank) => $bank['code'] !== '' && $bank['name'] !== '')
            ->sortBy('name')
            ->values()
            ->all();
    }

    public function resolveAccount(string $accountNumber, string $bankCode): array
    {
        $response = $this->request('GET', '/bank/resolve', [
            'query' => [
                'account_number' => $accountNumber,
                'bank_code' => $bankCode,
            ],
        ]);

        return (array) ($response['data'] ?? []);
    }

    public function createRecipient(array $details): string
    {
        $response = $this->request('POST', '/transferrecipient', [
            'json' => [
                'type' => 'nuban',
                'name' => $details['account_name'],
                'account_number' => $details['account_number'],
                'bank_code' => $details['bank_code'],
                'currency' => $details['currency'],
                'metadata' => [
                    'organization_id' => $details['organization_id'],
                ],
            ],
        ]);

        $code = (string) ($response['data']['recipient_code'] ?? '');
        if ($code === '') {
            throw new PaystackTransferException('Paystack did not return a transfer recipient.');
        }

        return $code;
    }

    public function initiateTransfer(array $payload): array
    {
        return $this->request('POST', '/transfer', [
            'json' => $payload,
        ]);
    }

    public function fetchTransfer(string $reference): array
    {
        return $this->request('GET', '/transfer', [
            'query' => ['reference' => $reference],
        ]);
    }

    public function subunitAmount(int $amount): int
    {
        return (int) round($amount * 100);
    }

    private function request(string $method, string $uri, array $options = []): array
    {
        $secretKey = (string) config('paystack.secretKey', '');
        if ($secretKey === '') {
            $secretKey = (string) (PaymentSetting::find(1)?->paystackSecretKey ?? '');
        }

        if ($secretKey === '') {
            throw new PaystackTransferException('Paystack is not configured.');
        }

        $caBundle = (string) config('paystack.caBundle', '');
        $verify = $caBundle !== '' && is_file($caBundle) ? $caBundle : true;

        $client = new Client([
            'base_uri' => rtrim((string) config('paystack.paymentUrl', 'https://api.paystack.co'), '/').'/',
            'headers' => [
                'Authorization' => 'Bearer '.$secretKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'verify' => $verify,
            'timeout' => 30,
        ]);

        try {
            $response = $client->request($method, ltrim($uri, '/'), $options);
            $payload = json_decode((string) $response->getBody(), true);

            if (! is_array($payload)) {
                throw new PaystackTransferException('Paystack returned an unreadable response.');
            }

            if (($payload['status'] ?? false) !== true) {
                throw new PaystackTransferException($this->errorMessage($payload));
            }

            return $payload;
        } catch (RequestException $exception) {
            $payload = json_decode((string) $exception->getResponse()?->getBody(), true);

            throw new PaystackTransferException($this->errorMessage(is_array($payload) ? $payload : []));
        } catch (TransferException $exception) {
            throw new PaystackTransferException('Paystack could not be reached. Please try again.');
        }
    }

    private function errorMessage(array $payload): string
    {
        $message = trim((string) ($payload['message'] ?? ''));

        return $message !== '' ? $message : 'Paystack rejected the payout request.';
    }
}
