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

## 0. Requisitos<a name="sec0"></a>
Antes de iniciar este projeto certifique-se de ter instalado em seu computador os seguintes propramas:
- Banco de dados (preferencialmente): MySQL 5.7+ ou MariaDB 10.2.0+;
- PHP 7.2 ou superior;
- Apache 2.4+;
- Composer

Além disso, é recomendável que você já tenha desenvolvido ao menos um projeto utilizando o framework Laravel de modo a tirar máximo proveito deste guia. Você pode acessar o repositório de um projeto Laravel básico [aqui](https://github.com/brnocesar/ecomp/tree/master/4-laravel).

## 1. Criando o projeto Laravel<a name="sec1"></a>
A versão do Laravel utilizada neste projeto é a 5.8. Para criar um projeto especificamente com esta versão utilizamos o comando abaixo:
```sh
$ composer create-project --prefer-dist laravel/laravel project "5.8.*"
```

## 2. Configurando o ambiente para instalação do pacote Tenancy<a name="sec2"></a>
### 2.1. Configurações de conexão
Antes de fazer a instalação do pacote através do Composer, é necessário configurar uma conexão chamada `system` que permita ao Tenancy criar novas bases de dados para os _tenants_. Para isso, é necessário ter um usuário no Banco de Dados com permissões elevadas.
Você pode criar esse usuário e a base de dados "_master_" por linha de comando (exemplo abaixo) ou no seu SGBD de preferência.

```sql
CREATE DATABASE IF NOT EXISTS tenancy_db;
CREATE USER IF NOT EXISTS tenancy_user@localhost IDENTIFIED BY 'someRandomAndVeryComplexPassword';
GRANT ALL PRIVILEGES ON *.* TO tenancy_user@localhost WITH GRANT OPTION;
```
Após isso devemos configurar as conexões com o Banco de Dados no nosso projeto. Conexões no plural porque é necessário mais de uma, duas no caso: uma para a Base de Dados principal e outra para realizar a troca entre as Bases de Dados.

Vá até o arquivo `config/database.php` e adicione as novas conexões abaixo da `'mysql'`. Não esqueça de adicionar as credenciais do usuário do Banco de Dados. Lembre-se também de incluir estas informações, bem como a conexão como `system` no arquivo `.env`.

```php
    ...
    'connections' => [
        ...
        'mysql' => [
            ...
        ],
        'system' => [
            'driver' => 'mysql',
            'host' => env('TENANCY_HOST', '127.0.0.1'),
            'port' => env('TENANCY_PORT', '3306'),
            'database' => env('TENANCY_DATABASE', 'tenancy_db'),
            'username' => env('TENANCY_USERNAME', 'tenancy_user'),
            'password' => env('TENANCY_PASSWORD', 'someRandomAndVeryComplexPassword'),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => 'innoDB',
        ],
        'tenant' => [
            'driver' => 'mysql',
            'host' => env('TENANCY_HOST', '127.0.0.1'),
            'port' => env('TENANCY_PORT', '3306'),
            'database' => env('TENANCY_DATABASE', 'tenancy_db'),
            'username' => env('TENANCY_USERNAME', 'tenancy_user'),
            'password' => env('TENANCY_PASSWORD', 'someRandomAndVeryComplexPassword'),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => 'innoDB',
        ],
    ...
```

```
DB_CONNECTION=system
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tenancy_db
DB_USERNAME=tenancy_user
DB_PASSWORD=someRandomAndVeryComplexPassword
```

### 2.2. Adicionando o pacote Tenancy como uma dependência do projeto
A versão 5.8 do Laravel é compatível com a versão [5.4](https://tenancy.dev/docs/hyn/5.4) do Tenancy, portanto, é esta versão do pacote que iremos instalar através do Composer.
```sh
project$ composer require "hyn/multi-tenant:5.4.*"
```
### 2.3. Configurações iniciais do pacote e projeto
#### 2.3.1. Ajustando/definindo as _migrations_
Após adicionar o pacote como uma dependência do projeto, devemos "publicar" os arquivos do Tenancy no projeto, ou seja, copiar os arquivos (_migrations_, configurações e etc, marcados com a tag) para nosso projeto:
```sh
project$ php artisan vendor:publish --tag=tenancy
```
Agora que temos as _migrations_ do pacote podemos adicionar novas colunas à tabela _hostnames_. O comando abaixo irá criar um arquivo com nome similar a `2019_xx_xx_xxxxxx_tenancy_add_fields_hostnames.php` no diretório padrão das _migrations_. Basta especificar as novas colunas na função `up()`, como no bloco abaixo:
```php
    public function up()
    {
        Schema::table('hostnames', function (Blueprint $table) {
            $table->string('responsavel')->nullable();
            $table->string('fantasia')->nullable();
            $table->string('cidade')->nullable();
            $table->string('razao_social')->nullable();
            $table->string('cnpj')->nullable();
        });
    }
```
Também **devemos** criar uma pasta chamada `tenant` dentro do diretório `database/migrations`. Esta nova pasta irá armazenar as migrations comuns aos tenants, permitindo rodar de forma independente cada conjunto de _migrations_. As primeiras _migrations_ que colocaremos neste novo diretório são as criadas por padrão pelo Laravel, para tanto, copiamos (NÃO movemos, COPIAMOS!) os arquivos abaixo para o diretório `tenant`:
`2014_10_12_000000_create_users_table.php` e `2014_10_12_100000_create_password_resets_table.php`

#### 2.3.2. _Includes_
Devemos fazer um _"include"_ (não estou bem certo que é exatamente isso) no **model User** para forçar a conexão correta a ser feita na Base de Dados (melhorar essa parte!). Para isso basta adicionar:
```php
<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable{
    use Notifiable, UsesTenantConnection;
    ...
}

```
Além disso, devemos inserir o código abaixo no método `boot()` do arquivo `app/Providers/AppServiceProvider.php` para definir a conexão `tenant` como padrão quando um website _tenant_ for identificado:
```php
use Illuminate\Support\ServiceProvider;
use Hyn\Tenancy\Environment;

class AppServiceProvider extends ServiceProvider{
    public function boot(){        
        $env = app(Environment::class);

        if ($fqdn = optional($env->hostname())->fqdn) {
            config(['database.default' => 'tenant']);
        }
    }
}
```

#### 2.3.3. Configurações do Banco de Dados
Se você estiver usando o MySQL deve habilitar a _flag_ `uuid-limit-length-to-32` no arquivo `config/tenancy`, pois o MySQL não suporta nomes para as bases de dados com mais de 32 caracteres.

Talvez (dependendo da sua versão do Banco de Dados) você tenha que adicionar mais uma alteração no método `boot()` do aqruivo `app/Providers/AppServiceProvider.php`. Se trata da configuração do tamanho padrão de _strings_ armazenadas nas tabelas do Banco de Dados (ou algo do tipo...)
```php
use Hyn\Tenancy\Environment;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider{
    public function boot(){
        Schema::defaultStringLength(191);
        
        $env = app(Environment::class);

        if ($fqdn = optional($env->hostname())->fqdn) {
            config(['database.default' => 'tenant']);
        }
    }
}
```
#### 2.3.4. Rodando as _migrations_
Após isso, executamos o comando abaixo para rodar as _migrations_ do sistema e teremos cinco novas tabelas: users, password_resets, migrations, hostnames e websites.
```sh
project$ php artisan migrate --database=system
```
Não há necessidade de especificar a conexão usada pois o comando acima roda as migrations "do sistema principal", ou seja, as que estão fora da pasta `tenant`. Para rodar as migrations de todos os `tenants` podemos utilizar o comando apresentado abaixo, porém, usualmente isso não será necessário. Na próxima seção vamos adicionar um método ao `controller` que será responsável por rodar as `migrations` de cada tenant quando ele for criado.
```sh
project$ php artisan tenancy:migrate
```

## 3. Criando _tenants_
### 3.1. _Controller_
Como serão poucos _controllers_ para o sistema principal, podemos cria-lo no local padrão sem muita preocupação com a organização dos diretórios. Então rodamos o comando:
```sh
project$ php artisan make:controller TenantController
```
Vamos escrever um método `store()` no que será responsável por criar os _tenants_. Escrevemos um método para garantir que o nome da Base de dados não ultrapasse 32 caracteres de comprimento (`setLimitCharacters()`) e rodamos as _migrations_ do _tenant_ criado atráves do método `runMigrations()`, que é chamado no retorno de `store()`.
```php
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
        ]);

        if( !$migrated ){ // return FALSE for sucess
            return 'Tenant criado com sucesso.';
        }
        return 'Erro ao rodar migrations.';
    }
}
```

Por enquanto as únicas validações que fazemos são na requisição e se as _migrations_ do _tenant_ foram rodadas com sucesso. Para validar a requisição criamos um _formrequest_ para o método `store()`.
```sh
project$ php artisan make:request StoreTenantController
```
No _formrequest_ especificamos a obrigatoriedade, o tipo de dados que cada _field_ deve receber e as mensagens de erro.
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTenantRequest extends FormRequest
{
    public function authorize(){
        return true;
    }

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
```
### 3.2. Rotas
Por fim adicionamos uma rota para este método no arquivo `routes/web.php`. Fazemos do tipo GET, o que nos permite testar usando a URL.
```php
Route::get('createTenant', 'TenantController@store');
```

### 3.3. Testando
Até esse momento o funcionamento esperado é o seguinte:
1. após acessarmos a rota do método `store()` passando os devidos parâmetros (de forma correta) devemos receber um json com as informações do _tenant_ criado;
2. é criada uma base de dados de nome `<nome fantasia>_<cidade>_<sequencia aleatória de caractéres>` 
3. podemos acessar o endereço do novo _tenant_: `<nome fantasia>-<cidade>.project.local.br`, que é um subdomínio da aplicação principal.

Uma forma de verificar se o sistema está trocando as Bases de Dados é fazer o registro na aplicação. Como se tratam de diferentes Bases de Dados, será possível utilizar as mesmas credênciais para registro no sistema principal e em cada um dos _tenats_.

Para criar a autenticação do sistema rodamos o comando abaixo:
```sh
project$ php artisan make:auth
```
Para testar nossa aplicação devemos configurar um Virtual Host no Apache. (...)

Abra o seu navegador e acesse o endereço do domínio principal que você colocou nos arquivos `/conf/httpd-vhosts.conf` ou `/etc/hosts` (em meu caso, por exemplo, é `project.local.br`), você será redirecionado para a página inicial padrão do Laravel. Então acesse a rota do método `store()` pela URL e passe os parâmetros da requisição, como mostrado abaixo:
```
project.local.br/createTenant?responsavel=Potter Potatos&fantasia=Batatinha&cidade=Curitiba&razao_social=Batatas Infinitas LTDA&cnpj=12345678
```
Se tudo deu certo, agora temos duas URLs:
- da aplicação principal (`project.local.br`) e
- de um _tenant_ (`batatinha-curitiba.project.local.br`).

Acesse o seu Banco de Dados (terminal ou SGBD) e verifique se foi criada um Base de Dados com o nome seguindo o padrão mencionado acima. Se isso aconteceu, acesse ambos os endereços e efetue o registro com as mesmas credênciais. Se o procedimento for realizado com sucesso em ambos, significa que a troca de Base de Dados está sendo feita corretamente.

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

### 4.2. CRUDS
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
