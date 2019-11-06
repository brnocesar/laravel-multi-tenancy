<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cargo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'codigo',
        'descricao',
        'status',
        'requerente',
    ];

    public function funcionarios(){
        return $this->hasMany(Funcionario::class);
    }

}
