<p align="center">
<img src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/9a/Laravel.svg/1200px-Laravel.svg.png" width="90">
<img src="https://cdn3.iconfinder.com/data/icons/ui-icons-5/16/plus-small-01-512.png" width="90">
<img src="https://avatars1.githubusercontent.com/u/33319474?s=400&v=4" width="90">
</p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Canil Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/license.svg" alt="License"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://awesome.re/mentioned-badge.svg" alt="Mentioned in Awesome Laravel"></a>
</p>

# Desenvolvimento de aplicação _Multi-tenant_ usando o pacote [Tenancy](https://tenancy.dev/)

## 4. Estrutura dos _tenants_
### 4.1. Estruturando os objetos/tabelas
Os objetos necessários para que os _tenants_ funcionem são: **colaboradores**, **cargos**, **produtos** e **salários**. E eles se relacionam da seguinte forma:
- cada **colaborador** cadastrado terá um **cargo**;
- cada **cargo** pode ser ocupado por mais de um **colaborador**;
- os **produtos** que cada colaborador terá acesso dependem de seu **cargo**;
- cada **produto** pode estar disponível a mais de um **cargo**;
- e é claro, como os colaboradores não são relógios (pra trabalhar de graça), cada colaborador terá seu próprio **salário**.

Agora vamos "traduzir" esses relacionamentos para a lógica das tabelas no Banco de Dados:
1. **cargos x colaboradores**: cada cargo pode ter vários colaboradores (`hasMany`) e cada colaborador pertence a um cargo (`belongsTo`). Basicamente se trata de uma relação do tipo 1:N e a tabela 'colaboradores' terá uma chave estrangeira apontando para a tabela 'cargos';
2. **cargos x produtos**: cada cargo pode ter vários produtos associados (`belongsToMany`), assim como cada produto pode pertencer a diferentes cargos (`belongsToMany`). Esta  é uma relação do tipo N:N, o que significa que precisaremos de uma tabela pivô para relacionar os _id_'s;
3. **colaboradores x salarios**: cada colaborador tem apenas um salário (`hasOne`) e cada salário é específico para o colaborador (`belongs`). Aqui temos a relação mais simples possível, 1:1, então precisamos apenas de uma chave estrangeira na 'salarios' apontando para 'colaboradores'.

### 4.2. CRUDs
#### 4.2.1. Cargos
##### 4.2.1.1 _Model_ e _migration_
Vamos começar pelo CRUD de cargos, sendo o primeiro passo criar uma _model_ com o nome `Cargo` (no singular). As _models_ são criadas por padrão na pasta `app` e com o intuito de organizar melhor os arquivos do nosso projeto, vamos alocar as _models_ relativas aos _tenants_ dentro da pasta `Models/Tenants`.

Como o prural do nome desta _model_ (convenção para nomear as tabelas) é obtido apenas adicionando a letra "S" ao final, podemos usar o comando abaixo para criar a _migration_ ao mesmo tempo.
```sh
project$ php artisan make:model Models/Tenants/Cargo -m
```
Lembre-se que as migrations comuns aos _tenants_ devem ficar na pasta `database/migrations/tenant`, então devemos movê-la. Suprimindo possíveis comentários, os arquivos criados como apresentados abaixo:
- `app/Models/Tenants/Cargo.php`
```php
<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Model;

class Cargo extends Model
{
    //
}
```
- `database/migrations/tenant/2019_11_03_170700_create_cargos_table.php`
```php
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCargosTable extends Migration
{
    public function up(){
        Schema::create('cargos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
        });
    }

    public function down(){
        Schema::dropIfExists('cargos');
    }
}
```

Agora podemos começar a adicionar código aos arquivos criados e começando pela _migration_ definimos as colunas da tabela 'cargos' no método `up()`:
```php
...
    public function up(){
        Schema::create('cargos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nome');
            $table->string('codigo')->unique();
            $table->string('descricao')->nullable();
            $table->boolean('status')->default(true)->nullable();
            $table->boolean('requerente')->default(true)->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }
...
```

Na _model_ apenas adicionamos o uso do `SoftDeletes` e um vetor chamado `$fillabe` com as colunas de 'cargos' que queremos fazer atribuição em massa. Como essa é a primeira tabela que criamos, não há necessidade de definir as relações com as outras tabelas.
```php
<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Model;

class Cargo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nome',
        'codigo',
        'descricao',
        'status',
        'requerente',
    ];
}
```
Agora geramos as tabelas em todos os _tenants_ que ja tenham sido criados rodando o comando:
```sh
project$ php artisan tenancy:migrate
```

##### 4.2.1.2. _Controller_
Para finalizar o CRUD criamos o _controller_ de 'cargos'. Por questão da organização vamos criá-lo em uma pasta especifica apenas para os _controllers_ dos _tenants_. O próximo comando o cria dentro da pasta `Tenant` no local padrão. Adicionamos os métodos padrões e resultado é apresentado na sequência:
```sh
project$ php artisan make:controller Tenant/CargoController
```
- `app/Http/Controllers/Tenants/CargoController.php`
```php
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
```
Note que para cada método deve ser criado um _formrequest_ e esse procedimento já foi realizado na **Seção 3.1** quando fizemos o _controller_ do sistema principal, portanto não vou repeti-lo. Apenas se atente a criá-los de modo a manter os arquivos organizados, no meu caso, criei os _formrequests_ de 'cargo' em uma pasta específica para eles.

##### 4.2.1.3. Rotas
Crie as rotas no arquivo `routes/web.php` como mostrado abaixo:
```php
Route::get('createCargo', 'Tenants\CargoController@store');
Route::get('showCargo', 'Tenants\CargoController@show');
Route::get('updateCargo', 'Tenants\CargoController@update');
Route::get('deleteCargo', 'Tenants\CargoController@destroy');
Route::get('toListCargos', 'Tenants\CargoController@index');
```
Para acessá-las basta seguir o padrão:
```
http://batatinha-curitiba.projeto-tenancy.local.br/<nome da rota>?<field>=<valor>&<field>=<valor>...
```

Caso você queira conferir os arquivos originais, eles podem ser acessados no _commit_ [0791ef3c27a37c63c14b82bbe17ea5a4f1d10241](https://github.com/brnocesar/multi-tenancy/commit/0791ef3c27a37c63c14b82bbe17ea5a4f1d10241).
