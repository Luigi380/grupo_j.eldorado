<?php

use App\Core\Router;
use App\Controllers\AuthenticationController;
use App\Controllers\RegistrationController;
use App\Controllers\ItemRegistrationController;
use App\Controllers\LogoutController;
use App\Controllers\HomeController;

$router = new Router('/grupo_j_eldorado/public');

$router->get('/', [HomeController::class, 'index']);

$router->post('/admin/login', [AuthenticationController::class, 'auth']);
$router->post('/admin/cadastrar', [RegistrationController::class, 'register']);

$router->post('/admin/item/cadastrar', [ItemRegistrationController::class, 'create']);
$router->get('/admin/item/listar', [ItemRegistrationController::class, 'list']);
$router->get('/admin/item/exibir/{$id}', [ItemRegistrationController::class, 'show']);
$router->put('/admin/item/atualizar/{$id}', [ItemRegistrationController::class, 'update']);
$router->delete('/admin/item/deletar/{$id}', [ItemRegistrationController::class, 'delete']);

$router->post('/admin/logout', [LogoutController::class, 'logout']);

return $router;
