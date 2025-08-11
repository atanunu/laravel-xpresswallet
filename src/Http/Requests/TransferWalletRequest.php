<?php

namespace Atanunu\XpressWallet\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferWalletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source_wallet_id' => 'required|string',
            'destination_wallet_id' => 'required|string|different:source_wallet_id',
            'amount' => 'required|numeric|min:0.01',
            'narration' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:100',
            'currency' => 'required|string|size:3',
        ];
    }
}
