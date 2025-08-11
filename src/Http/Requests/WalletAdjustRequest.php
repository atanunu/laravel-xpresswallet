<?php

namespace Atanunu\XpressWallet\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WalletAdjustRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:100',
            'metadata' => 'nullable|array',
        ];
    }
}
