<?php

namespace Atanunu\XpressWallet\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CardSetupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'required|string',
            'type' => 'required|string|in:VIRTUAL,PHYSICAL',
            'currency' => 'required|string|size:3',
            'design' => 'nullable|string|max:100',
            'metadata' => 'nullable|array',
        ];
    }
}
