<?php

use App\Core\Router;
use App\Controllers\AdminController;
use App\Controllers\ItemsController;
use App\Controllers\ViewController;

$router = new Router('/grupo_j.eldorado/public');

//View Usuário
$router->get('/', [ViewController::class, 'index']);
$router->get('/materiais', [ViewController::class, 'materiais']);
$router->get('/ultimos-trabalhos', [ViewController::class, 'ultimosTrabalhos']);
$router->get('/granitos', [ViewController::class, 'granitos']);
$router->get('/marmores', [ViewController::class, 'marmores']);
$router->get('/quartzos', [ViewController::class, 'quartzos']);

//View Admin
$router->get('/admin', [ViewController::class, 'login']);
$router->get('/admin/login', [ViewController::class, 'login']);
$router->get('/admin/dashboard', [ViewController::class, 'dashboard']);
$router->get('/admin/home-edit', [ViewController::class, 'homeEdit']);
$router->get('/admin/cadastro', [ViewController::class, 'cadastro']);

//Rotas BackEnd Admin
$router->post('/admin/login', [AdminController::class, 'login']);
$router->post('/admin/logout', [AdminController::class, 'logout']);
$router->post('/admin/cadastrar', [AdminController::class, 'register']);
$router->get('/admin/listar-admins', [AdminController::class, 'listAdmins']);
$router->delete('/admin/deletar/{id}', [AdminController::class, 'deleteAdmin']);

//Rotas BackEnd Items
$router->post('/admin/item/cadastrar', [ItemsController::class, 'create']);
$router->get('/admin/item/listar', [ItemsController::class, 'list']);
$router->get('/admin/item/exibir/{id}', [ItemsController::class, 'show']);
$router->put('/admin/item/atualizar/{id}', [ItemsController::class, 'update']);
$router->delete('/admin/item/deletar/{id}', [ItemsController::class, 'delete']);

return $router;
