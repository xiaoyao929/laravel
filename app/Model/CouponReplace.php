<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * 券作废流水表
 * Class CouponTransfers
 * @package App\Model
 */
class CouponReplace extends Model
{
    protected $table = 'coupon_replace';
    const STATUS_PENDING       = 1; //1：待审批
    const STATUS_NO_THROUGH    = 2; //2：审批不通过
    const STATUS_TRANSIT       = 3; //3：审批通过
    const SUPPORT_INVALID_ON   = 1; //作废通知开启
    const SUPPORT_INVALID_OFF  = 2; //作废通知关闭
    const SUPPORT_ACTIVATE_ON  = 1; //激活通知开启
    const SUPPORT_ACTIVATE_OFF = 2; //激活通知关闭
}