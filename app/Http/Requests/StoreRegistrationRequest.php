<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|min:1|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|max:128|confirmed',
        ];
    }
}
