<?php

namespace App\Http\Middleware;

use App\Models\IpBan_Servers;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GlobalBlockRestrictedIp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }
}
