## projeto joopyter

# plataforma utilizada para cadastrar albuns musicais de artistas independentes

## características
tipografia:
- título h1 (55px): poppins black, orange-200
- título h2 (40px): poppins black, orange-200
- título h3 (24px): poppins black, orange-200
- título h4 (20px): poppins medium ou black

- texto (18px): poppins regular, stone-200
- texto (18px): poppins regular, stone-300
- texto (18px): poppins medium, stone-200
- texto (16px): poppins semibold, stone-200
- texto (16px): poppins regular, stone-300
- texto (13px): poppins semibold, stone-200
- texto (13px): poppins semibold, orange-200
- texto (12px): poppins medium, stone-300

- link (18px): poppins regular, sublinhado, orange-200
- link (16px): poppins regular, sublinhado, orange-200
- link (16px): poppins regular, sublinhado, red-60

- button (18px): poppins bold, orange-200

teal-700 e red-700

background:
- bg-stone-900
- bg-stone-800
- border-stone-600

other:
- rounded-md
- border 1px

- banco de dados - mysql
- backend - laravel 9
- frontend - blade, tailwind, alpine


## checklists
# interface
[x] logo presente em todas as telas
[x] favico em todas as janelas
[x] landing page com login / signup

# interação
[] usuários não-autenticados não podem ver os conteúdos - middleware auth nas rotas internas
[] usuários autenticados podem acessar os conteúdos - pela web ou por consultas API's JSON
[] usuários podem ser de 2 tipos - administrador e final

# API's
- todos os usuários deve se autenticar usando laravel sanctum
- retornar um JSON com lista de albuns (+ suas faixas) paginada e ordenada pelo último
```GET /api/albums
```
- filtrar a lista de albuns
```GET /api/albums?q=alguma+busca
```
- filtrar e ordenar a lista de albuns pelo data de lançamento descendente
```GET /api/albums?q=alguma+busca&sort=release_date desc
```
- requisições não-autenticadas não devem ser permitidas
- os registros listados devem obedecer escopos e políticas - laravel scopes e laravel policies

# usuário administrador
[] deve existir somente 1 usuário administrador
[] usuário administrador deve ser semeado - laravel seeding com o banco de dados
```php artisan migrate --seed
```
```php
User::insert([
    'name' =- 'SysAdmin',
    'email' =- 'admin@email.com',
    'password' =- Hash::make('123456'),
    'created_at' =- now(),
    'updated_at' =- now()
])
```
[] usuário administrador modera e exclui os albuns
[] usuário administrador gerencia e ativa/inativa usuários finais
- dashboard
    - total usuários cadastrados
    - total usuários inativos
    - total albuns cadastrados
    - total albuns excluídos
    - total faixas cadastrados
    - total faixas excluídas
- usuários
    - barra de pesquisa por nome ou id
    - tabela de usuários paginada (laravel paginate/simplepaginate) e ordenada pelo último: id, nome, total albuns, total faixas, data do cadastro, conta ativa?
    - botão/checkbox para ativar/desativar usuário
- albuns
    - barra de pesquisa por nome, ano de lançamento ou nome do artista
    - tabela de albuns paginada e ordenada pelo último: id, artista (nome do usuário), nome, ano de lançamento, total de faixas, data do cadastro
    - link para mostrar um album: tela com imagem cover, lista de faixas, botão para excluir album (usar laravel soft delete)
- sua conta
    - alterar nome/email
    - redefinir senha
- fazer logout

# usuários finais
[] usuários finais são cadastrados pelo formulário público - imagem, nome, data de nascimento, e-mail, senha, bio
```migration users
- id (big int)
- name (string, required)
- dob (string, required)
- email (string, required)
- password (string, required)
- is_active (boolean, default true)
- profile_pic (string, nullable)
- bio (string, nullable)
- artistic_pic (string, nullable)
- formation_date (string, nullable)
- description (string, nullable)
```
[] usuário final cadastra seus albuns - imagem, title, artist, data de lançamento, 
[] o album deve conter - imagem cover, data de lançamento, lista de faixas, total
```migration albums
- id (big int)
- image cover (string)
- name (string)
- total lenght (??)
- release_date (date)
- user_id (big int foreign key references id on users)
- deleted_at (timestamp) ou apenas $table--softDeletes()
```
[] a faixa de música deve conter - número, nome, duração
```migration tracks
- id (big int)
- name (string)
- length (??)
- file
- album_id (big int foreign key references id on albums)
```
[] usuários finais não podem ter acesso aos albuns de outros usuários - laravel scopes
[] usuários finais só podem editar/excluir os seus albuns - laravel policies
[] usuários finais inativados não podem fazer login - laravel login is_active
- dashboard
    - cards dos seus albuns cadastrados: cada card contém imagem, título, link para ver detalhes
- seus albuns
    - barra de pesquisa por nome ou ano de lançamento
    - tabela de albuns paginada e ordenada pelo último: id, nome, ano de lançamento, total de faixas, data do cadastro
    - link para mostrar um album: tela com imagem cover, lista de faixas, botão para excluir album (usar laravel soft delete), link para editar album
    - tela para editar album: alterar foto, nome, ano de lançamento, apagar/adicionar novas faixas
- sua conta
    - alterar nome/email
    - redefinir senha
- fazer logout



users
[x] create_users_table
[] User
    [x] scope - só ter acesso ao seus dadosf
    [] policy - só pode editar/excluir os seus dados
[] AuthController
    [x] logout
    [x] is_active
[] UserController
    show
    edit
    update
    destroy
[] ArtistController
    show
    edit
    update
[] UserPolicy

albums
[] create_albums_table
[] Album
    users foreign key
    tasks foreign key
    protected static function booted()
    {
        static::addGlobalScope(new UserScope);
    }
[] AlbumController
    index
    create
    store
    show
    edit
    update
    destroy
[] AlbumPolicy

tracks
[] create_tracks_table
[] Track
    albums foreign key
[] TrackController
    index
    create
    store
    show
    edit
    update
    destroy
[] TrackPolicy



index() ->  Display a listing of the resource.
create() -> Show the form for creating a new resource.
store(Request $request) -> Store a newly created resource in storage.
show(Model $model) -> Display the specified resource.
edit(Model $model) -> Show the form for editing the specified resource.
update(Request $request, Model $model) -> Update the specified resource in storage.
destroy(Model $model) -> Remove the specified resource from storage.





api
ok- rotas básicas de autenticação
+-(lembrei disso depois de ter criado um usuario)- usuário padrão semeado com seeder
ok- api restful para albums
ok- api restful para tracks
ok- tracks pertencem aos albums
ok- user_id~album_id e album_id~track_id não podem ser fillable
+-(albums to pegando todos. user nao)- api de listagem de users e albums devem ser paginadas(20)
ok- todas as apis quando retornarem devem ser passadas por API Resource
    ok API Resource para user
    ok API Resource para album: nested um resource de user, e uma coleção de resources de tracks
    ok API Resource para track
ok- get Albums: só dados genéricos
ok- get Albums/id: dados genéricos, user e tracks
ok- a lista de tracks será feita no get do Album
ficou sem /post- para inserir as tracks usar albums/id/tracks/post
ok- para update/delete das tracks usar albums/id/tracks/id

