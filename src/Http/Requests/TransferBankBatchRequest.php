<?php

namespace Atanunu\XpressWallet\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferBankBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'transfers' => 'required|array|min:1|max:100',
            'transfers.*.bank_code' => 'required|string|max:20',
            'transfers.*.account_number' => 'required|string|max:20',
            'transfers.*.amount' => 'required|numeric|min:0.01',
            'transfers.*.narration' => 'nullable|string|max:255',
            'transfers.*.reference' => 'nullable|string|max:100',
            'transfers.*.currency' => 'required|string|size:3',
        ];
    }
}
