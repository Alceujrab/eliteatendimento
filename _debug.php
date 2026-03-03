<?php
/**
 * Diagnóstico de Deploy - REMOVER EM PRODUÇÃO
 * Acesse: https://crm.cfauto.com.br/_debug.php
 */
header('Content-Type: text/html; charset=utf-8');

echo '<h1>Diagnóstico Elite Atendimento</h1>';
echo '<hr>';

// 1. Info do PHP
echo '<h2>1. PHP</h2>';
echo '<p>Versão: ' . phpversion() . '</p>';
echo '<p>SAPI: ' . php_sapi_name() . '</p>';
echo '<p>Módulo rewrite: ' . (function_exists('apache_get_modules') ? (in_array('mod_rewrite', apache_get_modules()) ? '✅ SIM' : '❌ NÃO') : '⚠️ Não é possível verificar (CGI/FPM)') . '</p>';

// 2. Server Variables relevantes
echo '<h2>2. Variáveis $_SERVER</h2>';
echo '<table border="1" cellpadding="5">';
$keys = ['DOCUMENT_ROOT', 'SCRIPT_FILENAME', 'SCRIPT_NAME', 'REQUEST_URI', 'PHP_SELF', 'REDIRECT_URL', 'REDIRECT_STATUS', 'PATH_INFO', 'PATH_TRANSLATED', 'SERVER_SOFTWARE', 'HTTPS'];
foreach ($keys as $k) {
    $v = $_SERVER[$k] ?? '<em>(não definido)</em>';
    echo "<tr><td><strong>{$k}</strong></td><td>" . htmlspecialchars((string) $v) . "</td></tr>";
}
echo '</table>';

// 3. Caminhos do projeto
echo '<h2>3. Caminhos</h2>';
echo '<p>__DIR__ (deste script): ' . __DIR__ . '</p>';
echo '<p>Existe vendor/autoload.php? ' . (file_exists(__DIR__ . '/vendor/autoload.php') ? '✅ SIM' : '❌ NÃO') . '</p>';
echo '<p>Existe bootstrap/app.php? ' . (file_exists(__DIR__ . '/bootstrap/app.php') ? '✅ SIM' : '❌ NÃO') . '</p>';
echo '<p>Existe public/index.php? ' . (file_exists(__DIR__ . '/public/index.php') ? '✅ SIM' : '❌ NÃO') . '</p>';
echo '<p>Existe .htaccess? ' . (file_exists(__DIR__ . '/.htaccess') ? '✅ SIM' : '❌ NÃO') . '</p>';
echo '<p>Existe index.php (raiz)? ' . (file_exists(__DIR__ . '/index.php') ? '✅ SIM' : '❌ NÃO') . '</p>';
echo '<p>Existe .env? ' . (file_exists(__DIR__ . '/.env') ? '✅ SIM' : '❌ NÃO') . '</p>';

// 4. Conteúdo do .htaccess
echo '<h2>4. Primeiras linhas do .htaccess</h2>';
if (file_exists(__DIR__ . '/.htaccess')) {
    echo '<pre>' . htmlspecialchars(file_get_contents(__DIR__ . '/.htaccess')) . '</pre>';
} else {
    echo '<p>❌ Arquivo não encontrado!</p>';
}

// 5. Conteúdo do index.php raiz
echo '<h2>5. index.php (raiz)</h2>';
if (file_exists(__DIR__ . '/index.php')) {
    echo '<pre>' . htmlspecialchars(file_get_contents(__DIR__ . '/index.php')) . '</pre>';
} else {
    echo '<p>❌ Arquivo não encontrado!</p>';
}

// 6. Testar banco de dados
echo '<h2>6. Banco de Dados</h2>';
try {
    $envFile = __DIR__ . '/.env';
    if (file_exists($envFile)) {
        $env = parse_ini_file($envFile, false, INI_SCANNER_RAW);
        $host = $env['DB_HOST'] ?? '127.0.0.1';
        $port = $env['DB_PORT'] ?? '3306';
        $db = $env['DB_DATABASE'] ?? '';
        $user = $env['DB_USERNAME'] ?? '';
        $pass = $env['DB_PASSWORD'] ?? '';

        echo "<p>Host: {$host}:{$port} | DB: {$db} | User: {$user}</p>";

        $pdo = new PDO("mysql:host={$host};port={$port};dbname={$db}", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo '<p>✅ Conexão OK!</p>';

        // Verificar tabela tenants
        $stmt = $pdo->query('SELECT id, name, slug, is_active, deleted_at FROM tenants LIMIT 5');
        $tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo '<p>Registros na tabela tenants:</p>';
        echo '<table border="1" cellpadding="5"><tr><th>id</th><th>name</th><th>slug</th><th>is_active</th><th>deleted_at</th></tr>';
        foreach ($tenants as $t) {
            echo '<tr>';
            foreach ($t as $val) {
                echo '<td>' . htmlspecialchars((string) ($val ?? 'NULL')) . '</td>';
            }
            echo '</tr>';
        }
        echo '</table>';

        // Verificar users
        $stmt2 = $pdo->query('SELECT id, email, tenant_id, role, is_active, deleted_at FROM users LIMIT 5');
        $users = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        echo '<p>Registros na tabela users:</p>';
        echo '<table border="1" cellpadding="5"><tr><th>id</th><th>email</th><th>tenant_id</th><th>role</th><th>is_active</th><th>deleted_at</th></tr>';
        foreach ($users as $u) {
            echo '<tr>';
            foreach ($u as $val) {
                echo '<td>' . htmlspecialchars((string) ($val ?? 'NULL')) . '</td>';
            }
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo '<p>❌ .env não encontrado</p>';
    }
} catch (Exception $e) {
    echo '<p>❌ Erro: ' . htmlspecialchars($e->getMessage()) . '</p>';
}

// 7. Testar rewrite simulado
echo '<h2>7. Teste de Rewrite</h2>';
echo '<p>Tente acessar: <a href="/admin/elite-seminovos">/admin/elite-seminovos</a></p>';
echo '<p>Se der 404, o problema está no Apache rewrite ou no Laravel.</p>';
echo '<p>Para testar se o rewrite funciona, acesse: <a href="/_test-rewrite">/\_test-rewrite</a> — se esta URL mostrar o site/erro do Laravel, o rewrite está OK.</p>';

// 8. Verificar pastas de cache
echo '<h2>8. Cache Laravel</h2>';
$cacheDirs = [
    'bootstrap/cache' => __DIR__ . '/bootstrap/cache',
    'storage/framework/cache' => __DIR__ . '/storage/framework/cache',
    'storage/framework/sessions' => __DIR__ . '/storage/framework/sessions',
    'storage/framework/views' => __DIR__ . '/storage/framework/views',
];
foreach ($cacheDirs as $label => $path) {
    $exists = is_dir($path);
    $writable = $exists ? is_writable($path) : false;
    echo "<p>{$label}: " . ($exists ? '✅ existe' : '❌ não existe') . ' | ' . ($writable ? '✅ gravável' : '❌ não gravável') . "</p>";
}

// 9. Arquivos cached
echo '<h2>9. Arquivos de Cache</h2>';
$cacheFiles = [
    'bootstrap/cache/config.php',
    'bootstrap/cache/routes-v7.php',
    'bootstrap/cache/services.php',
    'bootstrap/cache/packages.php',
    'bootstrap/cache/events.php',
];
foreach ($cacheFiles as $f) {
    $full = __DIR__ . '/' . $f;
    echo "<p>{$f}: " . (file_exists($full) ? '✅ existe (' . number_format(filesize($full)) . ' bytes)' : '❌ não existe') . "</p>";
}

echo '<hr>';
echo '<p><strong>⚠️ REMOVA ESTE ARQUIVO APÓS DIAGNÓSTICO!</strong></p>';
