<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->tenant_id) {
            abort(403, 'Acesso não autorizado.');
        }

        // Compartilhar tenant globalmente com as views
        $tenant = auth()->user()->tenant;
        view()->share('currentTenant', $tenant);

        return $next($request);
    }
}
