<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true; //öffentlich weil kein token vorhanden bei registration
    }



    public function rules(): array
    {
        return [
            'username' => ['required', 'alpha_dash', 'min:3', 'max:30', 'unique:users,username'],
            'email'    => ['required', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }


    public function messages(): array
    {
        return [
            'username.unique' => 'Dieser Benutzername ist bereits vergeben.',
            'email.unique'    => 'Diese E-Mail wird bereits verwendet.',
        ];
    }
}
