<?php

namespace App\Http\Requests\Tenants\Cargo;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCargoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => 'required|integer',
            'nome' => 'string|max:255',
            'descricao' => 'string|max:255',
            'codigo' => 'numeric',
        ];
    }
}
