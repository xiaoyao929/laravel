<?php

namespace App\Dao;

use App\Model\{Role};
use Illuminate\Support\Facades\{Cache};

class RoleDao
{
    /**
     * 获取角色数据（缓存）
     * @return mixed
     */
    public static function getRoles()
    {
        $roles = Cache::remember('cache:roles:'.session( 'user.node_id' ), Role::CACHE_TIME, function()
        {
            return $roles = Role::select('id','name')
                -> where( 'is_admin', Role::SUPER_ADMIN_OFF )
                -> where( 'node_id', session( 'user.node_id' ))
                -> orderBy( 'id', 'asc' )
                -> get()
                -> toArray();
        });
        return $roles;
    }

    /**
     * 清除缓存
     */
    public static function delCache()
    {
        if( Cache::has( 'cache:roles:'.session( 'user.node_id' )))
        {
            return Cache::forget('cache:roles:'.session( 'user.node_id' ));
        }
    }
}