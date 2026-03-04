<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Incidente em produção (04/03/2026)

### Sintoma

- Localmente o login funcionava normalmente.
- No servidor remoto, após autenticar, o usuário era redirecionado e recebia `404` em `/admin/elite-seminovos`.

### Erros identificados

- `404` no acesso pós-login às rotas de tenant do Filament (`/admin/{tenant}`).
- Ambiente do servidor web rodando versão de PHP incompatível com dependências em produção (antes da correção).
- Validação de acesso ao tenant sensível a tipo (`tenant_id` vs `tenant->id`), podendo falhar em produção e resultar em `404`.

### Causa raiz

Combinação de fatores de ambiente e regra de acesso multi-tenant:

1. **PHP no webserver** estava abaixo da versão exigida pelas dependências instaladas.
2. A checagem de acesso ao tenant em `User::canAccessTenant()` usava comparação estrita de tipo, o que pode negar o tenant mesmo com mesmo valor, causando `404` na rota do painel.

### Correções aplicadas

1. Atualização do PHP do servidor para `8.4`.
2. Regeração de caches de produção:
	- `php artisan optimize:clear`
	- `php artisan config:cache`
	- `php artisan route:cache`
	- `php artisan view:cache`
3. Ajuste no modelo de usuário para robustez no tenancy:
	- Arquivo: `app/Models/User.php`
	- Adicionado cast: `'tenant_id' => 'integer'`
	- Ajuste em `canAccessTenant()` para comparar IDs como inteiro.
4. Limpeza de arquivos temporários de debug no servidor.

### Resultado

- Login e redirecionamento para o painel tenant voltaram a funcionar em produção.

### Checklist de deploy (recomendado)

- Confirmar versão de PHP do **webserver** (não só CLI).
- Garantir `APP_DEBUG=false` em produção.
- Regerar caches após deploy.
- Validar login + redirecionamento para `/admin/{tenant}`.
