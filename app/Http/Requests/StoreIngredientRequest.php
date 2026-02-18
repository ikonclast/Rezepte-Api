<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIngredientRequest extends FormRequest
{
    /**
     * Jeder eingeloggte Benutzer darf Zutaten anlegen.
     * (Admin-Check erfolgt nicht hier, sondern in der Middleware/Policy.)
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validierungsregeln für das Anlegen einer neuen Zutat.
     */
    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:100', 'unique:ingredients,name'],
            'unit_type' => ['required', 'string', 'max:50'],
        ];
    }


    public function messages(): array
    {
        return [
            'name.required' => 'Bitte gib einen Namen für die Zutat an.',
            'name.unique'   => 'Diese Zutat existiert bereits.',
            'unit_type.required' => 'Bitte gib eine Einheit an (z. B. g, ml oder Stück).',
        ];
    }
}
