<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CouponType
 * @package App\Model
 * 优惠券 券种
 */
class CouponSale extends Model
{
    protected $table = 'coupon_sale';
    const STATUS_PENDING    = 1; //1：待审批
    const STATUS_NO_THROUGH = 2; //2：审批不通过
    const STATUS_TRANSIT    = 3; //3：审批通过

    const CUSTOMER_TYPE_INTERNAL = 1;//内部员工
    const CUSTOMER_TYPE_CLIENT   = 2;  //个人用户
    const CUSTOMER_TYPE_COMPANY  = 3; //公司用户
}