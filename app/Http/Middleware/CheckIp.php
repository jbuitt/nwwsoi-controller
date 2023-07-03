<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckIp
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
        $allowedIpsRegex = config('nwwsoi-controller.allowed_ips_regex');
        if (!preg_match("/$allowedIpsRegex/", $request->ip())) {
            logger('The IP ' . $request->ip() . ' is not in the list of allowed IPs.');
            abort(403);
        }
        return $next($request);
    }
}
