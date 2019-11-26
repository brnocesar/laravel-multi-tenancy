<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use  App\Models\Tenants\Cargo;

class Colaborador extends Model
{
    use SoftDeletes;

    protected $fillable = [ 'matricula', 'nome', 'cargo_id', 'admissao', 'cracha', 'cpf', 'nascimento' ];

    protected $table = 'colaboradores';

    public function cargo(){
        return $this->belongsTo(Cargo::class);
    }
}
