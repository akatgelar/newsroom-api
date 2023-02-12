<?php

namespace App\Http\Middleware;

use Closure;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        header('Access-Control-Allow-Origin:  *');
        header('Access-Control-Allow-Headers:  enctype, Content-Type, X-Auth-Token, Authorization, Origin');
        header('Access-Control-Allow-Methods:  GET, POST, PUT, DELETE');
        return $next($request);
    }
}
