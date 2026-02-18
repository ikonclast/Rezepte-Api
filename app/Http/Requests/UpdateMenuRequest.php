<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMenuRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'title'        => ['sometimes', 'string', 'max:255'],
            'guest_count'  => ['sometimes', 'integer', 'min:1', 'max:10000'],
            'recipes'      => ['sometimes', 'array', 'min:1'],
            'recipes.*'    => ['integer', 'exists:recipes,id'],
        ];
    }
}
