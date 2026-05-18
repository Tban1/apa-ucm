<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNominaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'user_ids'   => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'uuid', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_ids.required' => 'Debes seleccionar al menos un académico.',
            'user_ids.min'      => 'Debes seleccionar al menos un académico.',
            'user_ids.*.exists' => 'Uno o más académicos seleccionados no son válidos.',
        ];
    }
}
