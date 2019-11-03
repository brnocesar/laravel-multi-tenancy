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
