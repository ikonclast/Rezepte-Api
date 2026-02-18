<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
   
    public function authorize(): bool
    {
        // Autorisierung macht die Policy (Controller->authorize())
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'username' => ['sometimes', 'string', 'min:3', 'max:50'],
            'email'    => ['sometimes', 'email', 'unique:users,email,{$userId}'],
            'password' => ['sometimes', 'string', 'min:8', 'confirmed'],
            // optional: weitere Felder whitelisten (z. B. name)
        ];
    }
}
