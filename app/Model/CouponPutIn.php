<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CouponPutIn
 * @package App\Model
 * 优惠券入库表
 */
class CouponPutIn extends Model
{
    protected $table = 'coupon_put_in';
    const STATUS_PENDING    = 1; //1：待审批
    const STATUS_NO_THROUGH = 2; //2：审批不通过
    const STATUS_PUT_IN     = 3; //3：全部入库
}