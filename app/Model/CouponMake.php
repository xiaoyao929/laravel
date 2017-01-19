<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CouponRequest
 * @package App\Model
 * 优惠券 制券
 */
class CouponMake extends Model
{
    protected $table = 'coupon_make';
    const STATUS_PENDING        = 1; //1：待审批
    const STATUS_NO_THROUGH     = 2; //2：审批不通过
    const STATUS_MAKING         = 3; //3：审批通过，制券中
    const STATUS_COMPLETE       = 4; //4：制券完成（等待印刷）
    const STATUS_PUT_IN_PENDING = 5; //5：入库待审批
    const STATUS_PUT_IN         = 6; //6：全部入库
}