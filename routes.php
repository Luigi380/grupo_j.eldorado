<?php

use App\Core\Router;
use App\Controllers\AdminController;
use App\Controllers\ItemsController;
use App\Controllers\ViewController;
use App\Controllers\PublicMaterialsController;

$router = new Router('');

//View Usuário
$router->get('/', [ViewController::class, 'index']);
$router->get('/materiais', [ViewController::class, 'materiais']);
$router->get('/ultimos-trabalhos', [ViewController::class, 'ultimosTrabalhos']);
$router->get('/granitos', [ViewController::class, 'granitos']);
$router->get('/marmores', [ViewController::class, 'marmores']);
$router->get('/quartzos', [ViewController::class, 'quartzos']);
$router->get('/sobre', [ViewController::class, 'sobreNos']);

//View Admin
$router->get('/admin', [ViewController::class, 'login']);
$router->get('/admin/login', [ViewController::class, 'login']);
$router->get('/admin/dashboard', [ViewController::class, 'dashboard']);
$router->get('/admin/home-edit', [ViewController::class, 'homeEdit']);
$router->get('/admin/cadastro', [ViewController::class, 'cadastro']);
$router->get('/admin/settings', [ViewController::class, 'settings']);
$router->get('/admin/gerenciar-materiais', [ViewController::class, 'gerenciarMateriais']);

//Rotas BackEnd Admin
$router->post('/admin/login', [AdminController::class, 'login']);
$router->post('/admin/logout', [AdminController::class, 'logout']);
$router->post('/admin/cadastrar', [AdminController::class, 'register']);
$router->get('/admin/listar-admins', [AdminController::class, 'listAdmins']);
$router->delete('/admin/deletar/{id}', [AdminController::class, 'deleteAdmin']);
$router->get('/admin/current', [AdminController::class, 'getCurrentAdmin']);
$router->put('/admin/update-email', [AdminController::class, 'updateEmail']);
$router->put('/admin/update-password', [AdminController::class, 'updatePassword']);

//Rotas BackEnd Items
$router->post('/admin/upload-imagem', [ItemsController::class, 'uploadImage']);
$router->post('/admin/item/cadastrar', [ItemsController::class, 'create']);
$router->get('/admin/item/listar', [ItemsController::class, 'list']);
$router->get('/admin/item/exibir/{id}', [ItemsController::class, 'show']);
$router->post('/admin/item/atualizar/{id}', [ItemsController::class, 'update']); // Mudado para POST para facilitar
$router->delete('/admin/item/deletar/{id}', [ItemsController::class, 'delete']);

//Rotas Públicas de Materiais (Não requer autenticação - apenas leitura)
$router->get('/api/materiais/publico/listar', [PublicMaterialsController::class, 'list']);
$router->get('/api/materiais/publico/todos', [PublicMaterialsController::class, 'listAll']);
$router->get('/api/materiais/publico/detalhes/{id}', [PublicMaterialsController::class, 'show']);

return $router;
