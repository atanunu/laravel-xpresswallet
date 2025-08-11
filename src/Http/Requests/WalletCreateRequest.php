<?php

namespace Atanunu\XpressWallet\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WalletCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'required_without:customer_identifier|string|nullable',
            'customer_identifier' => 'required_without:customer_id|string|nullable',
            'currency' => 'required|string|size:3',
            'type' => 'required|string|in:PRIMARY,SECONDARY',
            'metadata' => 'nullable|array',
        ];
    }
}
