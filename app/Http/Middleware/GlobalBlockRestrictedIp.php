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
        $valid=false;
        $explodedIp = explode('.',$request->ip());//array with the client's address
        $globalRestrictions = IpBan_Servers::where('global',1)->get()->all();
        foreach($globalRestrictions as $restriction){
            $FBA = explode('.', $restriction->forbiddenIp); //forbiddenIP array

            $section=4;
            while(!$valid and $section>0){
                $FIP = $FBA[4-$section];//forbidden ip part
                $CIP = $explodedIp[4-$section];//client ip part
                if(
                    $FIP != $CIP and $FIP != "*"
                ){
                    $valid = true;
                }
                $section--;
            }

        }

        if(!$valid){
            if (auth()->check()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
            return redirect()->back()->with('error', 'Your Account is suspended, please contact Admin.');
        }

        return $next($request);
    }
}
