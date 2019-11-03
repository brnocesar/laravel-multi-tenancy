
<p align="center">
<img src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/9a/Laravel.svg/1200px-Laravel.svg.png" width="90">
<img src="https://cdn3.iconfinder.com/data/icons/ui-icons-5/16/plus-small-01-512.png" width="90">
<img src="https://avatars1.githubusercontent.com/u/33319474?s=400&v=4" width="90">
</p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/license.svg" alt="License"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://awesome.re/mentioned-badge.svg" alt="Mentioned in Awesome Laravel"></a>
</p>

# multi-tenancy
Exemplo de aplicação web baseada no conceito "multi-tenant", desenvolvida com Laravel e o pacote Tenancy.

## Funcionalidades
###  1. Sistema principal
#### 1.1. Criação de _tenats_
Para criar um '_tenant_' devemos passar os seguintes parâmetros:
- nome do responsável pela empresa: _string_ não nula;
- nome fantasia: _string_ não nula;
- razão social: _string_ não nula;
- cidade: _string_ não nula;
- CNPJ: inteiro não nulo.
Cada _tenant_ criado pode ser acessado através de um subdomínio da aplicação principal. Este subdomínio é criado em função do nome fantasia e da cidade.

### 2. _Tenants_
#### 2.1. CRUD de Cargos
Para criar 'cargos' em cada um dos _tenants_ devemos passar os seguintes parâmetros:
- nome: _string_ não nula;
- código: inteiro;
- descrição: _string_
- status: _boolean_, 0 ou 1;
- requerente: _boolean_, 0 ou 1.
Também é possível ver um cargo em específico, editar, deletar e listar todos.

## Guia de desenvolvimento (em construção)
- [Capítulos 0 & 1 - Requisitos & Criando o projeto Laravel](https://github.com/brnocesar/multi-tenancy/blob/master/guia_desenvolvimento/cap_0-1.md).
- [Capítulo 2 - Configurando o ambiente para instalação do pacote Tenancy](https://github.com/brnocesar/multi-tenancy/blob/master/guia_desenvolvimento/cap_2.md).
- [Capítulo 3 - Criando _tenants_](https://github.com/brnocesar/multi-tenancy/blob/master/guia_desenvolvimento/cap_3.md).
- [Capítulo 4 - Estrutura dos _tenants_](https://github.com/brnocesar/multi-tenancy/blob/master/guia_desenvolvimento/cap_4.md).
    - 4.1 - Estruturando os objetos/tabelas
    - 4.2 - CRUDs
        - 4.2.1 - Cargo

###### por enquanto é isso ;)
