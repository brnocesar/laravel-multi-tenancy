<?php

namespace App\Http\Requests\Tenants\Colaborador;

use Illuminate\Foundation\Http\FormRequest;

class StoreColaboradorController extends FormRequest
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
            'matricula' => 'required|string|max:255',
            'nome' => 'required|string|max:255',
            // 'cargo_id' => 'required|numeric',
        ];
    }

    public function messages(){
        return [
            'nome.required' => 'Campo Nome é obrigatório!',
            'matricula.required' => 'Campo Matrícula é obrigatório!',
            'codigo.numeric' => 'Campo Matrícula deve receber um número!',
        ];
    }
}
