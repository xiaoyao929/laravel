<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CouponPutIn
 * @package App\Model
 * 优惠券仓库库存计数表
 */
class CouponStorageStock extends Model
{
    protected $table = 'coupon_storage_stock';
    public $timestamps = false;
}