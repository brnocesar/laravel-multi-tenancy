
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

- [Funcionalidades do sistema](#funcionalidades)
- [Procedimentos pós-clone](#pos-clone)
- [Guia de desenvolvimento](#guia)
- [Configurando um _Virtual Host_](#vh)

## Funcionalidades<a name="funcionalidades"></a>
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
- código: inteiro não nulo;
- descrição: _string_;
- status: _boolean_, 0 ou 1;
- requerente: _boolean_, 0 ou 1.

Também é possível visualizar, editar e deletar um cargo específico, assim como listar todos que não foram deletados.

#### 2.2. CRUD de Colaboradores
Para criar 'colaboradores' em cada um dos _tenants_ devemos passar os seguintes parâmetros:
- matricula: _string_ única;
- nome: _string_ não nula;
- cargo_id: inteiro não nulo;
- admissao: _date_ (valor padrão é a data atual);
- cracha: _string_;
- cpf: _string_;
- nascimento: _date_.

Também é possível visualizar, editar e deletar um cargo específico, assim como listar todos que não foram deletados.

## Procedimentos pós-clone<a name="pos-clone"></a>
#### 1. Configurações gerais e de ambiente
##### 1.1 Instalar as dependências via Composer
```sh
multi-tenant$ cd project
project$ composer install
```

##### 1.2. Criar a Base de Dados e um usuário para Banco de Dados
```sql
CREATE DATABASE IF NOT EXISTS tenancy_db character set UTF8mb4 collate utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS tenancy_user@localhost IDENTIFIED BY '142536';
GRANT ALL PRIVILEGES ON *.* TO tenancy_user@localhost WITH GRANT OPTION;
```

Como os arquivos `.env.example` e `config/database.php` fazem parte do repositório do projeto, as credênciais de usuário para acesso ao Banco de Dados e o nome da Base de Dados usada no projeto são os mesmos usados no exemplo acima. Portanto, se você não mudou nada do bloco acima, basta apenas conferir os arquivos nos próximos passos.

##### Altere os arquivos de ambiente: `.env` e `config/database.php`
Faça uma cópia do arquivo `.env.example` para criar o `.env`:
```sh
project$ cp .env.example .env
```
Agora adicione as credênciais da Base de Dados e do usuário do Banco de Dados:
- no arquivo `.env`:
```
DB_CONNECTION=system
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tenancy_db
DB_USERNAME=tenancy_user
DB_PASSWORD=142536
```

- e em `config/database.php`:
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
            'password' => env('TENANCY_PASSWORD', '142536'),
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
            'database' => env('TENANCY_DATABASE', 'data_base_name'),
            'username' => env('TENANCY_USERNAME', 'tenancy_user'),
            'password' => env('TENANCY_PASSWORD', '142536'),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => 'innoDB',
        ],
    ...

```

##### 1.3. Crie uma nova chave para a aplicação
```sh
project$ php artisan key:generate
```

##### 1.4. Gere o arquivo de _cache_ das configurações de ambiente
```sh
project$ php artisan config:cache
```

##### 1.5. Rode as _migrations_
```sh
project$ php artisan migrate:refresh
```

## Guia de desenvolvimento (em construção)<a name="guia"></a>
- [Capítulos 0 & 1 - Requisitos & Criando o projeto Laravel](https://github.com/brnocesar/multi-tenancy/blob/master/guia_desenvolvimento/cap_0-1.md)
- [Capítulo 2 - Configurando o ambiente para instalação do pacote Tenancy](https://github.com/brnocesar/multi-tenancy/blob/master/guia_desenvolvimento/cap_2.md)
- [Capítulo 3 - Criando _tenants_](https://github.com/brnocesar/multi-tenancy/blob/master/guia_desenvolvimento/cap_3.md)
- [Capítulo 4 - Estrutura dos _tenants_](https://github.com/brnocesar/multi-tenancy/blob/master/guia_desenvolvimento/cap_4.md)
    - [4.1 - Estruturando nossa aplicação](https://github.com/brnocesar/multi-tenancy/blob/master/guia_desenvolvimento/cap_4.md#secao4.1)
    - 4.2 - CRUDs
        - [4.2.1 - Cargos](https://github.com/brnocesar/multi-tenancy/blob/master/guia_desenvolvimento/cap_4.md#secao4.2.1)
        - [4.2.2 - Colaboradores](https://github.com/brnocesar/multi-tenancy/blob/master/guia_desenvolvimento/cap_4.md#secao4.2.2)
        - [4.2.3 - Salários]()
        - [4.2.4 - Produtos]()

## Configurando um _Virtual Host_ no Apache<a name="vh"></a>
##### 1. Arquivo de _Virtual Host_
Para testarmos nossa aplicação devemos configurar um _Virtual Host_ no Apache. O primeiro passo para isso é criar um arquivo para o _virtual host_ do nosso projeto, estes arquivos ficam no diretório `/etc/apache2/sites-available/`. Fazemos uma cópia do arquivo padrão nomeando-a como `project.local.br.conf`:
```
$ sudo cp /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/project.local.br.conf
```
Suprimindo os comentários o arquivo ficará parecido com o bloco abaixo (provavelmente o este bloco não estará em seu arquivo, então basta colá-lo no final).
```
<VirtualHost *:80>
 ServerName
 ServerAlias
 DocumentRoot
 <Directory "">
	Options +Indexes +Includes +FollowSymLinks +MultiViews
	AllowOverride All
	Allow from all
	Require all granted
 </Directory>
</VirtualHost>
```
As diretivas que devemos preencher são:
- **ServerName**: estabelece o domínio de base que deve corresponder à esta definição de virtual host;
- **ServerAlias**: define outros nomes que devem corresponder como se fossem o nome de base (subdomínios, www, etc);
- **DocumentRoot**: reflete o diretório que contém o arquivo raiz do projeto (index.html, index.php, etc);
- **Directory**: contém o caminho do diretório raiz de nosso projeto.

Podemos obter o caminho do arquivo raiz utilizando o terminal, para isso navegue até a pasta 'public' do projeto Laravel e então rode o comando `$ pwd` (_print working directory_). Agora basta colar a saída deste comando na diretiva **DocumentRoot**.
```sh
project$ cd public
project/public$ pwd 
```
Para projetos Laravel, a raiz do projeto fica 1 (um) nível acima do arquivo raiz.

No meu caso, o bloco que deve ser adicionado ao arquivo de _virtual host_ ficou assim:
```
<VirtualHost *:80>
 ServerName project.local.br
 ServerAlias *.project.local.br
 DocumentRoot "/home/bruno/repositorios/multi-tenancy/project/public"
 <Directory "/home/bruno/repositorios/multi-tenancy/project/">
	Options +Indexes +Includes +FollowSymLinks +MultiViews
	AllowOverride All
	Allow from all
	Require all granted
 </Directory>
</VirtualHost>
```

##### 2. Ativando o arquivo de _Virtual Host_
Agora que temos um arquivo de _virtual host_ para nossa aplicação, devemos ativa-lo. Isso pode ser feito utilizando uma ferramenta fornecida pelo Apache, rodando o comando:
```sh
$ sudo a2ensite project.local.br
```
Para que as alterações tenha efeito, é necessário reiniciar o Apache:
```sh
$ sudo systemctl restart apache2
ou
$ sudo service apache2 restart
```

##### 3. Configurando o arquivo de _hosts_ local
Nesta etapa adicionamos os domínios e subdomínios da nossa aplicação ao arquivo `/etc/hosts`. Dessa forma, estes serão resolvidos para o _localhost_, o que permitirá acessá-los:
```
127.0.0.1	localhost
127.0.0.1	project.local.br
127.0.0.1	subdominio.project.local.br
127.0.1.1	pv
```

##### 4. Habilite `mod_rewrite` no Apache
Certifique-se de que o `mod_rewrite` do Apache está ativado. Para garantir isso, basta rodar:
```sh
$ cd /etc/apache2/mods-available
$ sudo a2enmod rewrite
$ sudo /etc/init.d/apache2 restart
```

##### 5. Testando
Para testar o _virtual host_ configurado, basta acessar o(s) domínio(s) pelo navegador.
```
http://project.local.br
```

**(*) OBSERVAÇÃO 1: Note que para cada _tenant_ criado, devemos adicionar seu subdomínio no arquivo `/etc/hosts` apontando para o _localhost_ (127.0.0.1).**


**(*) OBSERVAÇÃO 2:** Se você receber qualquer excessão sobre permissão, rode o comando abaixo. Mas tenha em mente que só estamos fazendo isso pois estamos no ambiente local, __JAMAIS__ na devemos fazer isso em ambiente de produção.
```sh
$ sudo chmod -R 777 projeto/
```

###### por enquanto é isso ;)
