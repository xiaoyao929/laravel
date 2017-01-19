<?php
/**
 * 角色表
 */
namespace App\Model;

use Zizaco\Entrust\EntrustRole;

class Role extends EntrustRole
{
    const SUPER_ADMIN_ON  = 1;
    const SUPER_ADMIN_OFF = 0;
    const CACHE_TIME = 1440; //缓存时间
}