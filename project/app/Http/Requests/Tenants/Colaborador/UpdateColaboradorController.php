<?php

namespace App\Http\Requests\Tenants\Colaborador;

use Illuminate\Foundation\Http\FormRequest;

class UpdateColaboradorController extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id'            => 'required|numeric',
            'matricula'     => 'string|max:255',
            'nome'          => 'string|max:255',
            'cracha'        => 'string|max:255',
            'cpf'           => 'string|max:255',
            'nascimento'    => 'date',
            'centro_custo'  => 'string|max:255',
            'status'        => 'boolean',
            'requerente'    => 'boolean',
            'cargo_id'      => 'numeric',
        ];
    }

    public function messages(){
        return [
            'nascimento.date' => 'Campo Data de \'nascimento\' deve receber um número!',
        ];
    }
}
