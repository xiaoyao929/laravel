<?php

namespace App\Dao;

use App\Model\{CouponClass};
use Illuminate\Support\Facades\{Cache};

class CouponClassDao
{
    /**
     * 获取优惠券类型
     */
    public static function getCouponClass()
    {
        $data = Cache::rememberForever('cache:coupon_class:'.session( 'user.node_id'),function()
        {
            return CouponClass::where('node_id',session( 'user.node_id'))
                    -> where('status',CouponClass::STATUS_ON)
                    -> get()
                    -> toArray();
        });

        return $data;

    }




}