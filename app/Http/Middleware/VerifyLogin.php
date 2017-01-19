<?php

namespace App\Http\Middleware;

use Closure;
use App\Dao\{LoginDao,AccessModelDao};
use Lib\PublicClass\{S};

class VerifyLogin
{
    public function handle( $request, Closure $next )
    {
        $user    = session('user');
        $isLogin = LoginDao::verify( $user );
        if( AccessModelDao::isApiModel())
        {
            if( !$isLogin ) S::error('20008');
        }
        else
        {
            if( !$isLogin )
            {
                if( LoginDao::$loginError == 'token' )
                    abort(310);
                else
                    return redirect('login');
            }
            if( !LoginDao::menus()) abort(403);
            LoginDao::refreshCache( $user );
            $prefix = AccessModelDao::getPrefix();
            $menus  = session('menus.data');

            foreach ( $menus as $k=> $menuOne )
            {
                if( $menuOne['prefix'] == $prefix )
                {
                    $menus[$k]['is_active'] = 'on';
                }
            }

            view()->share( 'system_menus', $menus );
            view()->share( 'sessionUser', $user );
        }
        return $next($request);
    }
}
