<?php 

namespace rndwiga\MultiTenant;

use Closure;
use rndwiga\MultiTenant\Traits\TenantConnector;
use Auth;

class AuthTenant
{
    use TenantConnector;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $tenantDatabase = Auth::user()->tenantUser->tenant->tenantDatabase;

        $this->resolveDatabase($tenantDatabase);

        return $next($request);
    }
} 