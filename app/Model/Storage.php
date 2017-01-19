<?php
/**
 * 仓库表
 */
namespace App\Model;
use Illuminate\Database\Eloquent\Model;

class Storage extends Model
{
    const STATUS_ON  = 1;
    const STATUS_OFF = 0;
    protected $table = 'storage';
    const CACHE_TIME = 1440; //缓存时间(分钟)
}