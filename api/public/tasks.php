<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Taskboard\Interface\Http\Router;
use Taskboard\Infrastructure\Repository\PdoTaskRepository;
use Taskboard\Application\TaskService;
use Taskboard\Interface\Http\TaskController;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

$repo       = new PdoTaskRepository();
$service    = new TaskService();
$controller = new TaskController($service, $repo);

$routes = require dirname(__DIR__) . '/src/routes/api.php';

$router = new Router($controller);
$router->register($routes);
$router->handle();
