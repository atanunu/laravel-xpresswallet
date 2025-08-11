<?php

namespace Atanunu\XpressWallet\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MerchantCompleteRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Rely on upstream auth / middleware if needed
    }

    public function rules(): array
    {
        return [
            'business_name' => 'required|string|max:255',
            'business_email' => 'required|email',
            'business_phone' => 'required|string|max:32',
            'country' => 'required|string|size:2',
            'state' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:32',
            'industry' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
            'contact_first_name' => 'required|string|max:100',
            'contact_last_name' => 'required|string|max:100',
            'contact_email' => 'required|email',
            'contact_phone' => 'required|string|max:32',
            'documentation' => 'nullable|array',
            'documentation.*.type' => 'required_with:documentation|string|max:100',
            'documentation.*.reference' => 'required_with:documentation|string|max:255',
        ];
    }
}
