<?php

namespace App\Http\Requests\Tenants\Cargo;

use Illuminate\Foundation\Http\FormRequest;

class StoreCargoRequest extends FormRequest
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
            'nome' => 'required|string|max:255',
            'codigo' => 'required|numeric',
        ];
    }

    public function messages(){
        return [
            'nome.required' => 'Campo Cargo é obrigatório!',
            'codigo.required' => 'Campo Código é obrigatório!',
            'codigo.numeric' => 'Campo Código deve receber um número!',
        ];
    }
}
