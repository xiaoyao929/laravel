<?php
/**
 * 角色权限表
 */
namespace App\Model;
use Illuminate\Database\Eloquent\Model;


class PermissionRole extends Model
{
    protected $table = 'permission_role';
    public $timestamps = false;
}