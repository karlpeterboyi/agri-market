<?php

namespace App\Services;

class MpesaB2CService
{
    public function send($phone, $amount)
    {
        return [
            'success' => true,
            'reference' => 'B2C-' . strtoupper(uniqid())
        ];
    }
}