<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenuRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'        => ['required', 'string', 'max:255'],
            'guest_count'  => ['required', 'integer', 'min:1', 'max:10000'],
            'recipes'      => ['required', 'array', 'min:1'],
            'recipes.*'    => ['integer', 'exists:recipes,id'],
        ];
    }
}
