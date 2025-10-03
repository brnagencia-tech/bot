<?php
// public/index.php — Front Controller

require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Core\Router;

session_start();

$router = new Router();
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
