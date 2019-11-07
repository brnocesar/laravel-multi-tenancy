<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use  App\Models\Tenants\Colaborador;

class Cargo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'codigo',
        'descricao',
        'status',
        'requerente',
    ];

    public function colaboradores(){
        return $this->hasMany(Colaborador::class);
    }

}
