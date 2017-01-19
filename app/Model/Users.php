<?php
/**
 * 用户表
 */
namespace App\Model;
use Illuminate\Database\Eloquent\Model;
use Zizaco\Entrust\Traits\EntrustUserTrait;
use Illuminate\Database\Eloquent\SoftDeletes;


class Users extends Model
{
    const SUPER_ADMIN_ON  = 1;
    const SUPER_ADMIN_OFF = 0;
    const STATUS_ON  = 0;
    const STATUS_OFF = 1;
    use EntrustUserTrait { restore as private restoreA; }
    use SoftDeletes { restore as private restoreB; }
    protected $dates = ['deleted_at'];
    protected $table = 'users';
    /**
     * 解决 EntrustUserTrait 和 SoftDeletes 冲突
     */
    public function restore()
    {
        $this-> restoreA();
        $this-> restoreB();
    }
}