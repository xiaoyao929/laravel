<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CouponType
 * @package App\Model
 * 优惠券 券种
 */
class CouponType extends Model
{
    protected $table = 'coupon_type';
    const STATUS_ON  = 1;
    const STATUS_OFF = 0;
    const TYPE_GC    = 1;
    const TYPE_BOG   = 2;
}