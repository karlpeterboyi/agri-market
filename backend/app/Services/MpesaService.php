<?php

namespace App\Services;

class MpesaService
{
    public function stkPush(
        string $phone,
        float $amount,
        string $reference
    )
    {
        return [
            'success' => true,
            'merchant_request_id' => uniqid(),
            'checkout_request_id' => uniqid(),
            'response_description' => 'STK Push sent'
        ];
    }
}