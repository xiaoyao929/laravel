<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * 券作废流水表
 * Class CouponTransfers
 * @package App\Model
 */
class CouponInvalid extends Model
{
    protected $table = 'coupon_invalid';
    const STATUS_PENDING    = 1; //1：待审批
    const STATUS_NO_THROUGH = 2; //2：审批不通过
    const STATUS_TRANSIT    = 3; //3：审批通过
}