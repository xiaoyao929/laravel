<?php

namespace App\Http\Middleware;

use Closure;

class OutputMsg
{
    public function handle( $request, Closure $next )
    {
        $promptMsg = session('prompt_msg');
        if( !empty( $promptMsg ))
        {
            view()->share( 'promptMsg', $promptMsg );
        }
        return $next($request);
    }
}
