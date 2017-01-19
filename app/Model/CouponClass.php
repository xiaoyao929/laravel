<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CouponType
 * @package App\Model
 * 优惠券 券种
 */
class CouponClass extends Model
{
    protected $table = 'coupon_class';
    public $timestamps = false;
    const STATUS_ON  = 1;
    const STATUS_OFF = 0;
}