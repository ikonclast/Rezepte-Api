<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Ingredient;

class UpdateIngredientRequest extends FormRequest
{
    /**
     * Authentifizierte Benutzer dürfen Zutaten bearbeiten.
     * (Admin-Pflicht wird über Middleware enforced.)
     */
    public function authorize(): bool
    {
        return true;
    }

  
    public function rules(): array
    {
        /** @var Ingredient|mixed $model */
        $model = $this->route('ingredient');
        $ingredientId = $model instanceof Ingredient ? $model->getKey() : (int) $model;

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('ingredients', 'name')->ignore($ingredientId, 'id'),
            ],
            'unit_type' => ['required', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Bitte gib einen Namen für die Zutat an.',
            'name.unique'   => 'Diese Zutat existiert bereits.',
            'unit_type.required' => 'Bitte gib eine Einheit an.',
        ];
    }
}
