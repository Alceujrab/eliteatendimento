<?php

/**
 * Laravel - Front Controller para cPanel Shared Hosting
 *
 * Quando o Document Root do cPanel aponta para a raiz do projeto
 * (não para public/), este arquivo faz o bootstrap completo
 * do Laravel, sem depender de require/proxy para public/index.php.
 */

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determinar se a aplicação está em modo de manutenção...
if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Registrar o autoloader do Composer...
require __DIR__.'/vendor/autoload.php';

// Inicializar o Laravel...
/** @var Application $app */
$app = require_once __DIR__.'/bootstrap/app.php';

// Informar ao Laravel que a pasta public está em public/
$app->usePublicPath(__DIR__.'/public');

// Processar a requisição...
$app->handleRequest(Request::capture());
