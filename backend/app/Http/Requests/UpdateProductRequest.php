<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id_categorie' => ['sometimes', 'required', 'exists:categories,id_categorie'],
            'nom' => ['sometimes', 'required', 'string', 'max:255'],
            'prix' => ['sometimes', 'required', 'numeric', 'min:0'],
            'quantite' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
