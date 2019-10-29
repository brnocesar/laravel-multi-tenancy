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
$ composer create-project --prefer-dist laravel/laravel proj "5.8.*"
```

## 2. Configurando o ambiente para instalação do pacote Tenancy<a name="sec2"></a>
### 2.1. Configurações de conexão
Antes de fazer a instalação do pacote através do Composer, é necessário configurar uma conexão chamada `system` que permita ao Tenancy criar novas bases de dados para os _tenants_. Para isso, é necessário ter um usuário no Banco de Dados com permissões elevadas.
Você pode criar esse usuário e a base de dados "_master_" por linha de comando (exemplo abaixo) ou no seu SGBD de preferência.

```sql
CREATE DATABASE IF NOT EXISTS tenancy;
CREATE USER IF NOT EXISTS tenancy@localhost IDENTIFIED BY 'someRandomAndVeryComplexPassword';
GRANT ALL PRIVILEGES ON *.* TO tenancy@localhost WITH GRANT OPTION;
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
            'password' => env('TENANCY_PASSWORD', 'senhaMuitoDificil'),
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
            'password' => env('TENANCY_PASSWORD', 'senhaMuitoDificil'),
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
DB_PASSWORD=senhaMuitoDificil
```

### 2.2. Adicionando o pacote Tenancy como uma dependência do projeto
A versão 5.8 do Laravel é compatível com a versão [5.4](https://tenancy.dev/docs/hyn/5.4) do Tenancy, portanto, é esta versão do pacote que iremos instalar através do Composer.

```sh
project$ composer require "hyn/multi-tenant:5.4.*"
```
