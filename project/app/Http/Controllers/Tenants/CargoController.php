<?php

namespace App\Http\Controllers\Tenants;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenants\Cargo\DestroyCargoRequest;
use App\Http\Requests\Tenants\Cargo\ShowCargoRequest;
use App\Http\Requests\Tenants\Cargo\StoreCargoRequest;
use App\Http\Requests\Tenants\Cargo\UpdateCargoRequest;
use App\Models\Tenants\Cargo;

class CargoController extends Controller
{
    public function store(StoreCargoRequest $request){
        $cargo = Cargo::create( $request->all() );

        $cargo->save();

        return response()->json( [ 'Cargo criado.', $cargo ], 200);
    }

    public function show(ShowCargoRequest $request){
        $cargo = Cargo::find( $request->id );
        if( !$cargo ){
            return response()->json( [ 'Cargo não encontrado.', $cargo ], 400);
        }

        return $cargo;
    }

    public function update(UpdateCargoRequest $request){
        $cargo = Cargo::find( $request->id );
        if( !$cargo ){
            return response()->json( [ 'Cargo não encontrado.', $cargo ], 400);
        }

        $cargo->nome = $request->nome ? $request->nome : $cargo->nome;
        $cargo->descricao = $request->descricao ? $request->descricao : $cargo->descricao;
        $cargo->codigo = $request->codigo ? $request->codigo : $cargo->codigo;
        if( $request->status != null ){
            if( $cargo->status != $request->status ){
                $cargo->status = $request->status;
            }
        }
        if( $request->requerente != null ){
            if( $cargo->requerente != $request->requerente ){
                $cargo->requerente = $request->requerente;
            }
        }
        $cargo->save();

        return response()->json( [ 'Cargo atualizado.', $cargo ], 200);
    }

    public function destroy(DestroyCargoRequest $request){
        $cargo = Cargo::find( $request->id );
        if( !$cargo ){
            return response()->json( [ 'Cargo não encontrado.', $cargo ], 400);
        }

        $cargo->delete();

        return response()->json( [ 'Cargo deletado.', $cargo ], 200);
    }

    public function index(){
        $cargos = Cargo::all();
        if( $cargos->count() > 0 ){
            return response()->json( [ 'Cargos.', $cargos ], 200);
        }
        return response()->json( [ 'Cargos não encontrados.', $cargos ], 400);
    }

}
