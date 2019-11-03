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
