<?php
/**
 * Diagnóstico de Deploy - REMOVER EM PRODUÇÃO
 * Acesse: https://crm.cfauto.com.br/_debug.php
 */
header('Content-Type: text/html; charset=utf-8');

echo '<h1>Diagnóstico Elite Atendimento v2</h1>';
echo '<hr>';

// 1. Info do PHP
echo '<h2>1. PHP e Servidor</h2>';
echo '<p>Versão PHP: ' . phpversion() . '</p>';
echo '<p>SAPI: ' . php_sapi_name() . '</p>';
echo '<p>Server Software: ' . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . '</p>';
echo '<p>DOCUMENT_ROOT: ' . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . '</p>';

// 2. Testar boot do Laravel
echo '<h2>2. Boot do Laravel</h2>';
try {
    require __DIR__.'/vendor/autoload.php';

    $app = require_once __DIR__.'/bootstrap/app.php';
    $app->usePublicPath(__DIR__.'/public');

    // Criar kernel e bootar sem enviar resposta
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

    echo '<p>✅ Laravel bootstrapped com sucesso</p>';
    echo '<p>Base Path: ' . $app->basePath() . '</p>';
    echo '<p>Public Path: ' . $app->publicPath() . '</p>';
    echo '<p>Environment: ' . $app->environment() . '</p>';
    echo '<p>Debug: ' . ($app->hasDebugModeEnabled() ? 'true' : 'false') . '</p>';

    // 3. Verificar rotas
    echo '<h2>3. Rotas admin/{tenant}</h2>';
    $router = $app->make('router');
    $routes = collect($router->getRoutes()->getRoutes())
        ->filter(fn ($r) => str_contains($r->uri(), 'admin/{tenant'))
        ->take(5);

    if ($routes->isEmpty()) {
        echo '<p>❌ NENHUMA rota com {tenant} encontrada! Routes não estão registradas.</p>';

        // Listar todas as rotas para debug
        echo '<p>Todas as rotas registradas:</p><ul>';
        foreach (collect($router->getRoutes()->getRoutes())->take(20) as $r) {
            echo '<li>' . implode('|', $r->methods()) . ' ' . $r->uri() . '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>✅ Rotas com {tenant} encontradas:</p><ul>';
        foreach ($routes as $r) {
            echo '<li>' . implode('|', $r->methods()) . ' <strong>' . $r->uri() . '</strong> → ' . ($r->getName() ?: 'sem nome') . '</li>';
        }
        echo '</ul>';
    }

    // 4. Testar resolução do tenant
    echo '<h2>4. Resolução do Tenant "elite-seminovos"</h2>';
    try {
        $tenant = \App\Models\Tenant::where('slug', 'elite-seminovos')->first();
        if ($tenant) {
            echo '<p>✅ Tenant encontrado: ID=' . $tenant->id . ', Name=' . $tenant->name . ', Slug=' . $tenant->slug . '</p>';
            echo '<p>getRouteKeyName(): ' . $tenant->getRouteKeyName() . '</p>';
            echo '<p>getRouteKey(): ' . $tenant->getRouteKey() . '</p>';

            // Testar resolveRouteBinding (como o Filament faz)
            $resolved = (new \App\Models\Tenant)->resolveRouteBinding('elite-seminovos', 'slug');
            echo '<p>resolveRouteBinding("elite-seminovos", "slug"): ' . ($resolved ? '✅ OK (ID=' . $resolved->id . ')' : '❌ NULL!') . '</p>';
        } else {
            echo '<p>❌ Tenant NÃO encontrado com slug "elite-seminovos"</p>';
        }
    } catch (\Exception $e) {
        echo '<p>❌ Erro no tenant: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }

    // 5. Simular requisição para /admin/elite-seminovos
    echo '<h2>5. Simular Request para /admin/elite-seminovos</h2>';
    try {
        $request = \Illuminate\Http\Request::create('/admin/elite-seminovos', 'GET');
        $response = $kernel->handle($request);
        echo '<p>Status Code: <strong>' . $response->getStatusCode() . '</strong></p>';
        if ($response->getStatusCode() === 302 || $response->getStatusCode() === 301) {
            echo '<p>Redirect para: ' . $response->headers->get('Location') . '</p>';
        }
        if ($response->getStatusCode() === 404) {
            echo '<p>❌ Laravel retornou 404!</p>';
            // Tentar /admin para ver se redireciona
            $request2 = \Illuminate\Http\Request::create('/admin', 'GET');
            $response2 = $kernel->handle($request2);
            echo '<p>/admin status: ' . $response2->getStatusCode() . '</p>';
            if ($response2->getStatusCode() === 302) {
                echo '<p>/admin redireciona para: ' . $response2->headers->get('Location') . '</p>';
            }
        }
        if ($response->getStatusCode() === 200) {
            echo '<p>✅ Laravel retornou 200! O problema é apenas o rewrite do LiteSpeed.</p>';
        }
        if ($response->getStatusCode() === 500) {
            echo '<p>❌ Laravel retornou 500! Conteúdo:</p>';
            echo '<pre>' . htmlspecialchars(substr($response->getContent(), 0, 2000)) . '</pre>';
        }
    } catch (\Exception $e) {
        echo '<p>❌ Exception: ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    }

    // 6. Testar /admin/login
    echo '<h2>6. Simular Request para /admin/login</h2>';
    try {
        $request3 = \Illuminate\Http\Request::create('/admin/login', 'GET');
        $response3 = $kernel->handle($request3);
        echo '<p>Status Code: <strong>' . $response3->getStatusCode() . '</strong></p>';
        if ($response3->getStatusCode() === 200) {
            echo '<p>✅ /admin/login funciona!</p>';
        }
    } catch (\Exception $e) {
        echo '<p>❌ Exception: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }

} catch (\Throwable $e) {
    echo '<p>❌ Falha ao bootar Laravel: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
}

echo '<hr>';
echo '<p><strong>⚠️ REMOVA ESTE ARQUIVO APÓS DIAGNÓSTICO!</strong></p>';
