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
Agora que temos as _migrations_ do pacote podemos adicionar novas colunas à tabela _hostnames_. O comando abaixo irá criar um arquivo com nome similar a `2019_xx_xx_xxxxxx_tenancy_add_fields_hostnames.php` no diretório padrão das _migrations_. Então basta especificar as novas colunas na função `up()` deste arquivo, como no bloco que se segue:
```sh
project$ php artisan make:migrate tenancy_add_fields_hostnames --table==hostnames
```

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
Também **devemos** criar uma pasta chamada `tenant` dentro do diretório `database/migrations`. Esta nova pasta irá armazenar as migrations comuns aos tenants, permitindo rodar de forma independente cada conjunto de _migrations_. As primeiras _migrations_ que colocaremos neste novo diretório são as criadas por padrão pelo Laravel, para tanto, copiamos (NÃO movemos, COPIAMOS!) os seguintes arquivos para o diretório `tenant`:
`2014_10_12_000000_create_users_table.php` e `2014_10_12_100000_create_password_resets_table.php`

#### 2.3.2. _Includes_
Devemos fazer um _"include"_ no **model User** para forçar a conexão correta a ser feita na Base de Dados (melhorar essa parte!). Para isso basta adicionar:
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
Não há necessidade de especificar a conexão usada pois o comando acima roda as migrations "do sistema principal", ou seja, as que estão fora da pasta `tenant`. Para rodar as migrations de todos os `tenants` podemos utilizar o comando apresentado abaixo. 
```sh
project$ php artisan tenancy:migrate
```
Na próxima seção vamos adicionar um método ao `controller` que será responsável por rodar as `migrations` de cada tenant quando ele for criado.
