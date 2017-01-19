<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CouponType
 * @package App\Model
 * 优惠券 券种
 */
class CouponInfo extends Model
{
    protected $table = 'coupon_info';
    public $timestamps = false;
    const STATUS_INVENTORY = 1;  //1:库存中
    const STATUS_SALES     = 2;  //2:已经销售
    const STATUS_INVALID   = 3;  //3:已作废
    const STATUS_TRANSIT   = 4;  //4:调拨在途
    const STATUS_USED      = 5;  //5:已核销
    const STATUS_PENDING   = 6;  //6:待审核
    //券的核验状态
    const VA_STATUS_NO_USED = 0;    //未使用
    const VA_STATUS_ON_USED = 2;    //使用中
    const VA_STATUS_OFF_USED = 3;   //已使用
    //券激活状态
    const ACTIVATE_OFF = 0;      //未激活
    const ACTIVATE_ON  = 1;      //已激活
}