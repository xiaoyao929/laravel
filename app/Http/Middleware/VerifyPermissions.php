<?php
/**
 * 验证用户权限
 */
namespace App\Http\Middleware;

use Closure;
use App\Model\{Users};
use App\Dao\{PermissionDao};

class VerifyPermissions
{
    public function handle( $request, Closure $next )
    {
        $user   = session('user');
        $action = false;
        if( $user['role_admin'] == 1 )
        {
            $action = true;
        }
        else
        {
            $arr  = PermissionDao::getPermissions();
            $path = '/'.$request-> path();
            if( in_array( $path, $arr ))
            {
                $re = Users::where( 'id', $user['id'] )-> first();
                if( $re-> can($path) )
                {
                    $action = true;
                }
            }
            else
            {
                $action = true;
            }
        }
        if( $action )
        {
            return $next($request);
        }
        else
        {
            abort( 403, '没有权限' );
        }
    }
}
