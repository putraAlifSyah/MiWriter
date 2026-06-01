<?php

namespace App\Http\Requests;

use App\Enums\BookStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|min:1|max:200',
            'genre' => 'nullable|string|max:100',
            'synopsis' => 'nullable|string|max:2000',
            'status' => ['nullable', Rule::enum(BookStatus::class)],
            'target_word_count' => 'nullable|integer|min:0',
            'target_deadline' => 'nullable|date',
        ];
    }
}
