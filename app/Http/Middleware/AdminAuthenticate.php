<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthManager;
use Illuminate\Support\Facades\Auth;

class AdminAuthenticate
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::user())
        {

            if (!in_array(Auth::user()->admin, array(1,2))) {
                return back()->with('status', 'You are not an admin');
            }

            return $next($request);
        }
        else
        {
            return back();
        }
    }
}
