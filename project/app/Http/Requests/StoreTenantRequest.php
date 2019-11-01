<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTenantRequest extends FormRequest
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
    public function rules(){
        return [
            'responsavel' => 'required|max:255',
            'fantasia' => 'required|max:255',
            'cidade' => 'required|max:255',
            'razao_social' => 'required|max:255',
            'cnpj' => 'required|numeric',
        ];
    }

    public function messages(){
        return [
            'responsavel.required' => 'Campo Responsavel é obrigatório!',
            'fantasia.required' => 'Campo Nome Fantasia é obrigatório!',
            'cidade.required' => 'Campo Cidade é obrigatório!',
            'razao_social.required' => 'Campo Razão social é obrigatório!',
            'cnpj.required' => 'Campo CNPJ é obrigatório!',
            'cnpj.numeric' => 'Campo CNPJ deve receber um número!',
        ];
    }
}
