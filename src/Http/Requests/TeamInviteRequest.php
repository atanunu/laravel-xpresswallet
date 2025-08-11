<?php

namespace Atanunu\XpressWallet\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TeamInviteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'role' => 'required|string|max:100',
        ];
    }
}
