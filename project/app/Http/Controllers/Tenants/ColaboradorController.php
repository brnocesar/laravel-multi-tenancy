<?php

namespace App\Http\Controllers\Tenants;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenants\Colaborador\DestroyColaboradorController;
use App\Http\Requests\Tenants\Colaborador\ShowColaboradorController;
use App\Http\Requests\Tenants\Colaborador\StoreColaboradorController;
use App\Models\Tenants\Cargo;
use App\Models\Tenants\Colaborador;

class ColaboradorController extends Controller
{
    public function store(StoreColaboradorController $request){

        $cargo = Cargo::find($request->cargo_id);
        if( !$cargo ){
            return response()->json( [ 'Cargo não encontrado.', $cargo ], 400);
        }

        $colaborador = Colaborador::create( $request->all() );

        $cargo = $cargo->colaboradores()->save($colaborador); // talvez isso nem seja necessário para estabelecer o relacionamento

        $colaborador->save();

        return response()->json( [ 'Colaborador criado.', $colaborador ], 200);
    }

    public function show(ShowColaboradorController $request){
        $colaborador = Colaborador::find( $request->id );
        if( !$colaborador ){
            return response()->json( [ 'Colaborador não encontrado.', $colaborador ], 400);
        }

        return $colaborador;
    }

    public function update(Request $request){
        $colaborador = Colaborador::find( $request->id );
        if( !$colaborador ){
            return response()->json( [ 'Colaborador não encontrado.', $colaborador ], 400);
        }

        $colaborador->matricula = $request->matricula ? $request->matricula : $colaborador->matricula;
        $colaborador->nome = $request->nome ? $request->nome : $colaborador->nome;
        $colaborador->cracha = $request->cracha ? $request->cracha : $colaborador->cracha;
        $colaborador->cpf = $request->cpf ? $request->cpf : $colaborador->cpf;
        $colaborador->nascimento = $request->nascimento ? $request->nascimento : $colaborador->nascimento;
        $colaborador->centro_custo = $request->centro_custo ? $request->centro_custo : $colaborador->centro_custo;
        if( $request->status != null ){
            if( $colaborador->status != $request->status ){
                $colaborador->status = $request->status;
            }
        }
        if( $request->requerente != null ){
            if( $colaborador->requerente != $request->requerente ){
                $colaborador->requerente = $request->requerente;
            }
        }
        if( $request->cargo_id != null ){
            $cargo = Cargo::find($request->cargo_id);
            if( $cargo ){
                // $colaborador->cargo()->associate($cargo); // acho que isso não é necessário
                $colaborador->cargo_id = $request->cargo_id;
            }
            else{
                $colaborador->save();
                return response()->json( [ 'Colaborador atualizado sem mudança do cargo.', $colaborador ], 200);
            }
        }
        $colaborador->save();

        return response()->json( [ 'Colaborador atualizado.', $colaborador ], 200);
    }

    public function destroy(DestroyColaboradorController $request){
        $colaborador = Colaborador::find( $request->id );
        if( !$colaborador ){
            return response()->json( [ 'Colaborador não encontrado.', $colaborador ], 400);
        }

        $colaborador->delete();

        return response()->json( [ 'Colaborador deletado.', $colaborador ], 200);
    }

    public function index(){
        $colaboradores = Colaborador::all();
        if( $colaboradores->count() > 0 ){
            return response()->json( [ 'Colaboradores.', $colaboradores ], 200);
        }
        return response()->json( [ 'Colaboradores não encontrados.', $colaboradores ], 400);
    }
}
