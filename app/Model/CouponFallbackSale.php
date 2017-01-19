<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CouponType
 * @package App\Model
 * 退券分支表
 */
class CouponFallbackSale extends Model
{
    protected $table = 'coupon_fallback_sale';
    public $timestamps = false;
    const STATUS_PENDING    = 1; //1：待审批
    const STATUS_TRANSIT    = 2; //3：审批通过
    const STATUS_NO_THROUGH = 3; //2：审批不通过
}