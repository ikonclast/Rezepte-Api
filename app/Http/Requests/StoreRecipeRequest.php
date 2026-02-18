<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecipeRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'portions' => ['required', 'integer', 'min:1'],

            'ingredients' => ['required', 'array', 'min:1'],
            'ingredients.*.ingredient_id' => ['required', 'integer', 'exists:ingredients,id'],
            'ingredients.*.quantity' => ['required', 'numeric', 'gt:0'],
            'ingredients.*.unit' => ['required', 'string', 'max:50'],

            'steps' => ['required', 'array', 'min:1'],
            'steps.*.step' => ['required', 'integer', 'min:1'],
            'steps.*.instruction' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Bitte gib einen Rezepttitel an.',
            'ingredients.required' => 'Ein Rezept muss mindestens eine Zutat enthalten.',
            'steps.required' => 'Mindestens ein Arbeitsschritt ist erforderlich.',
        ];
    }
}
