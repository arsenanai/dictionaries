<?php

namespace App\Http\Middleware;

use Closure;

class Localization
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
        $local = 'ru';
        if($request->hasHeader('Accept-Language')){
            $header = $request->header('X-localization');
            if (strpos($header, ',') !== false) {
                $local = explode(",", $header)[0];
            }else
                $local = $header;
        }
        app()->setLocale($local);
        return $next($request);
        
    }
}
