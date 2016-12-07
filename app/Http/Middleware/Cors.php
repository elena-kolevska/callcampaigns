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
        $domains = ["http://192.168.22.10:3000"];

        if (isset($request->server()['HTTP_ORIGIN'])){
            $origin = $request->server()['HTTP_ORIGIN'];

            if (in_array($origin, $domains)){
                return $next($request)
                    ->header('Access-Control-Allow-Origin', $origin)
                    ->header('Access-Control-Allow-Headers','Origin, Content-Type, Authorization')
                    ->header('Access-Control-Allow-Methods','GET, POST, PUT, DELETE, OPTIONS');
            }
        }

        return $next($request);
        }
}
