<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SetTenantSessionContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user) {
            if ($user->role === 'superadmin') {
                app(TenantContext::class)->setTenantId(null);
                DB::statement('EXEC sec.sp_set_context @tenant_id = ?, @is_superadmin = ?', [null, 1]);
            } elseif (! empty($user->tenant_id)) {
                app(TenantContext::class)->setTenantId($user->tenant_id);
                DB::statement('EXEC sec.sp_set_context @tenant_id = ?, @is_superadmin = ?', [$user->tenant_id, 0]);
            }
        }

        return $next($request);
    }
}
