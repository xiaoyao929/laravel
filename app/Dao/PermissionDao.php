<?php

namespace App\Dao;

use App\Model\{Permission};
use Illuminate\Support\Facades\{Cache};

class PermissionDao
{
    /**
     * 生成多级仓库数据
     * @return array
     */
    public static function getPermissions()
    {
        $data = Cache::rememberForever('cache:permission:list', function()
        {
            return $data = Permission::pluck('name')
                -> toArray();
        });

        return $data;
    }

    /**
     * 清除缓存
     * @return mixed
     */
    public static function delCache()
    {
        if( Cache::has( 'cache:permission:list' ))
        {
            return Cache::forget('cache:permission:list');
        }
    }
}