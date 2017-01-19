<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * 券调拨流水表
 * Class CouponTransfers
 * @package App\Model
 */
class CouponTransfers extends Model
{
    protected $table = 'coupon_transfers';
    const STATUS_PENDING           = 1; //1：待审批
    const STATUS_NO_THROUGH        = 2; //2：审批不通过
    const STATUS_TRANSIT           = 3; //3：审批通过，在途
    const STATUS_AFFIRM_THROUGH    = 4; //4：收货确认，入库完毕
    const STATUS_AFFIRM_NO_THROUGH = 5; //5：收获确认未通过，返回原库
}