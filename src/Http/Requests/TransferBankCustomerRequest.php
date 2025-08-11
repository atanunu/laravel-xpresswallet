<?php

namespace Atanunu\XpressWallet\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferBankCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'required|string',
            'bank_code' => 'required|string|max:20',
            'account_number' => 'required|string|max:20',
            'amount' => 'required|numeric|min:0.01',
            'narration' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:100',
            'currency' => 'required|string|size:3',
        ];
    }
}
