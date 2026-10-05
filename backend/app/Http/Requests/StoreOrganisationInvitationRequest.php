<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrganisationInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'email' => 'nullable|email|required_without:phone',

            'phone' => 'nullable|string|required_without:email',

            'role' => 'required|string',

            'expires_at' => 'nullable|date',

        ];
    }
}