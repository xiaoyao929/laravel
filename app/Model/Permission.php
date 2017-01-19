<?php
/**
 * 权限表
 */
namespace App\Model;

use Zizaco\Entrust\EntrustPermission;

class Permission extends EntrustPermission
{
    const CACHE_TIME = 0; //缓存时间
}