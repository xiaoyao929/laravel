<?php

namespace App\Dao;

use App\Model\{Menu,PermissionRole};
use Illuminate\Support\Facades\{Cache};
use Request;

class LoginDao
{
    public static $loginError = '';
    const MENUS_UPDATE_TIME   = 7200; //菜单更新时间（秒）
    const LOGIN_TIME          = 60;   //登录保持时间（分）
    const REFRESH_TIME        = 10;   //默认刷新时间（分）
    /**
     * 验证登录信息是否完整
     * @param $user
     * @return bool
     */
    public static function verify( $user ):bool
    {
        $action = true;
        if( empty( $user ) || empty( $user['id'] ) || empty( $user['account'] ) || empty( $user['nickname'] ) || empty( $user['node_id'] ) || empty( $user['token'] ))
        {
            self::delAllUserSession();
            $action = false;
        }
        else
        {
            $token      = $user['token'];
            $cacheToken = Cache::get( "cache:login:{$user['id']}" );
            if( $token != $cacheToken )
            {
                $action = false;
                self::delAllUserSession();
                self::$loginError = 'token';
            }
        }
        return $action;
    }
    private static function delAllUserSession()
    {
        $param = [
            'user' => [],
            'menus'=> [],
        ];
        session( $param );
    }
    public static function menus():bool
    {
        $sessionMenus = session( 'menus' );
        $action       = false;

        if( empty( $sessionMenus ))
        {
            $menus  = self::getMenus();
            $action = true;
        }
        elseif (( time() - $sessionMenus['time'] ) > self::MENUS_UPDATE_TIME )
        {
            $menus = self::getMenus();
            $action = true;
        }

        if( $action )
        {
            session([ 'menus'=> [ 'time'=> time(), 'data'=> $menus ]]);
        }

        return true;
    }

    public static function setCache( $user )
    {
        $token = md5($user['id'] . date('YmdHis') . rand( 10000, 99999 ));
        Cache::put( "cache:login:{$user['id']}", $token, self::LOGIN_TIME );
        Cache::put( "cache:login:check:{$user['id']}", $token, self::REFRESH_TIME );
        return $token;
    }

    public static function refreshCache( $user )
    {
        $token = $user['token'];
        if( !Cache::has( "cache:login:check:{$user['id']}" ))
        {
            Cache::put( "cache:login:{$user['id']}", $token, self::LOGIN_TIME );
            Cache::put( "cache:login:check:{$user['id']}", $token, self::REFRESH_TIME );
        }
    }

    private static function getMenus():array
    {
        $user  = session('user');

        $menus = Menu::leftJoin( 'permissions as b', 'menu.permission_id', '=', 'b.id' )
            -> where( 'menu.visiable', 1 )
            -> select( 'menu.id', 'menu.name', 'menu.parent_id', 'b.name as url', 'menu.icon', 'menu.prefix', 'menu.permission_id' )
            -> orderBy( 'menu.parent_id', 'asc' )
            -> orderBy( 'menu.sort', 'asc' )
            -> get()
            -> toArray();

        $return = [];

        $arr = PermissionRole::where( 'role_id', $user['role_id'] )
            -> pluck( 'permission_id' )
            -> toArray();

        foreach ( $menus as $menu )
        {
            if( $menu['parent_id'] == 0 )
            {
                $return[$menu['id']] = [
                    'id'    => $menu['id'],
                    'name'  => $menu['name'],
                    'icon'  => $menu['icon'],
                    'prefix'=> $menu['prefix'],
                    'url'   => $menu['url'],
                ];
            }
            else
            {
                if( $user['role_admin'] == 1 )
                {
                    $return[$menu['parent_id']]['menus'][] = [
                        'id'  => $menu['id'],
                        'name'=> $menu['name'],
                        'url' => $menu['url'],
                    ];
                }
                else
                {
                    if( in_array( $menu['permission_id'], $arr ))
                    {
                        $return[$menu['parent_id']]['menus'][] = [
                            'id'  => $menu['id'],
                            'name'=> $menu['name'],
                            'url' => $menu['url'],
                        ];
                    }
                }
            }
        }

        $data = array_merge( $return );

        foreach ( $data as $k=> $v )
        {
            if( empty( $v['menus'] )) unset($data[$k]);
        }

        return array_merge( $data );
    }
}