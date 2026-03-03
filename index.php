<?php

/**
 * Laravel - Proxy para cPanel Shared Hosting
 *
 * Em hospedagem cPanel onde o Document Root aponta para a raiz do projeto
 * (e não para public/), este arquivo simplesmente carrega o front controller
 * real que está em public/index.php.
 *
 * O __DIR__ dentro de public/index.php continuará resolvendo para public/,
 * então todos os caminhos relativos funcionam normalmente.
 */

require __DIR__.'/public/index.php';
