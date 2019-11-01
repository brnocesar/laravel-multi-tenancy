<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTenantRequest;
use Hyn\Tenancy\Models\Hostname;
use Hyn\Tenancy\Models\Website;
use Hyn\Tenancy\Repositories\HostnameRepository;
use Hyn\Tenancy\Repositories\WebsiteRepository;
use Illuminate\Http\Request;
use Psy\Util\Str;

class TenantController extends Controller
{
    public function store(StoreTenantRequest $request){
        $subDominio = Str::slug($request->fantasia .'-'. $request->cidade);

        $website = new Website();
        $website->uuid = $this->setLimitCharacters( $subDominio );
        app(WebsiteRepository::class)->create( $website );

        $hostname = Hostname::create( [
            'responsavel' => $request->responsavel,
            'fantasia' => $request->fantasia,
            'cidade' => $request->cidade,
            'razao_social' => $request->razao_social,
            'cnpj' => $request->cnpj,
            'fqdn' =>  $subDominio .'.'. $request->getHost(),
        ] );
        $hostname = app(HostnameRepository::class)->create( $hostname );
        app(HostnameRepository::class)->attach( $hostname, $website );

        return response()->json( [ $this->runMigrations($website), $hostname ], 200);
    }

    public function setLimitCharacters(String $subDomain){
        $subDomain = str_replace('-','_', $subDomain) .'_';
        $countCharacters = strlen($subDomain);

        if( $countCharacters <= 16){
            $subDomain .= strtolower( Str::random(16) );
        }
        elseif( $countCharacters > 16 and  $countCharacters < 32 ){
            $randomSequenceLen = 32 - $countCharacters;
            $subDomain .= strtolower( Str::random( $randomSequenceLen ) );
        }
        else{
            $subDomain = substr($subDomain, 0, 31);
        }

        return $subDomain;
    }

    public function runMigrations(Website $website){
        $migrated = Artisan::call('tenancy:migrate', [
            '--website_id' => $website->id,
        ]); // return FALSE for sucess

        if( !$migrated ){
            return 'Tenant criado com sucesso.';
        }
        return 'Erro ao rodar migrations.';
    }
}
