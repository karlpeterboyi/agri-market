<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Pesapal v3-style client with sandbox mock when credentials are missing.
 */
class PesapalService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('pesapal.base_url', 'https://cybqa.pesapal.com/pesapalv3'), '/');
    }

    public function isConfigured(): bool
    {
        return filled(config('pesapal.consumer_key'))
            && filled(config('pesapal.consumer_secret'));
    }

    public function getAccessToken(): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $res = Http::timeout(20)->post($this->baseUrl.'/api/Auth/RequestToken', [
                'consumer_key' => config('pesapal.consumer_key'),
                'consumer_secret' => config('pesapal.consumer_secret'),
            ]);

            if (!$res->successful()) {
                Log::warning('Pesapal token failed', ['body' => $res->body()]);

                return null;
            }

            return $res->json('token');
        } catch (\Throwable $e) {
            Log::warning('Pesapal token exception', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * @return array{redirect_url:?string, order_tracking_id:?string, mock?:bool}
     */
    public function checkoutPayment(
        Payment $payment,
        string $description,
        string $email,
        string $phone,
        string $firstName,
        string $lastName
    ): array {
        $callback = config('pesapal.callback_url')
            ?: url('/api/payments/pesapal/callback');

        $token = $this->getAccessToken();

        // Mock / sandbox without credentials — still usable end-to-end
        if (!$token) {
            $tracking = 'MOCK-'.Str::upper(Str::random(12));
            $payment->update(['pesapal_order_tracking_id' => $tracking]);

            $frontend = rtrim(env('FRONTEND_URL', 'http://127.0.0.1:5173'), '/');
            $redirect = $frontend.'/payments/pesapal/return?OrderTrackingId='.$tracking
                .'&payment_id='.$payment->id
                .'&mock=1';

            return [
                'redirect_url' => $redirect,
                'order_tracking_id' => $tracking,
                'mock' => true,
            ];
        }

        try {
            $payload = [
                'id' => (string) $payment->id,
                'currency' => 'TZS',
                'amount' => (float) $payment->amount,
                'description' => $description,
                'callback_url' => $callback,
                'notification_id' => config('pesapal.notification_id'),
                'billing_address' => [
                    'email_address' => $email,
                    'phone_number' => $phone,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'country_code' => 'TZ',
                ],
            ];

            $res = Http::withToken($token)
                ->timeout(30)
                ->post($this->baseUrl.'/api/Transactions/SubmitOrderRequest', $payload);

            if (!$res->successful()) {
                Log::warning('Pesapal submit failed', ['body' => $res->body()]);
                throw new \RuntimeException('Pesapal checkout failed: '.$res->body());
            }

            $data = $res->json();
            $tracking = $data['order_tracking_id'] ?? $data['OrderTrackingId'] ?? null;
            if ($tracking) {
                $payment->update(['pesapal_order_tracking_id' => $tracking]);
            }

            return [
                'redirect_url' => $data['redirect_url'] ?? $data['RedirectUrl'] ?? null,
                'order_tracking_id' => $tracking,
                'mock' => false,
            ];
        } catch (\Throwable $e) {
            Log::error('Pesapal checkout exception', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getTransactionStatus(string $trackingId): array
    {
        if (str_starts_with($trackingId, 'MOCK-')) {
            return [
                'status' => 'COMPLETED',
                'payment_status_description' => 'Completed',
                'confirmation_code' => $trackingId,
            ];
        }

        $token = $this->getAccessToken();
        if (!$token) {
            return ['status' => 'INVALID', 'payment_status_description' => 'No token'];
        }

        $res = Http::withToken($token)
            ->timeout(20)
            ->get($this->baseUrl.'/api/Transactions/GetTransactionStatus', [
                'orderTrackingId' => $trackingId,
            ]);

        return $res->json() ?: [];
    }
}
