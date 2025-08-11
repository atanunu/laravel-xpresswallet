<?php

namespace Atanunu\XpressWallet\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MerchantChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reset_code' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
        ];
    }
}
