<?php

namespace App\Dao;

use App\Model\{CouponType};
use Illuminate\Support\Facades\{Cache};

class CouponTypeDao
{
    /**
     * 获取优惠券券种
     */
    public static function getCouponType( $mode='all' )
    {
        if( $mode == 'all' )
        {
            $data = Cache::rememberForever('cache:coupon_type:'.session( 'user.node_id'),function()
            {
                return CouponType::where('node_id',session( 'user.node_id'))
                    -> select('id','activity_id','name','detail_name')
                    -> get()
                    -> toArray();
            });
        }
        elseif ( $mode == 'make' )
        {
            $data = Cache::rememberForever('cache:coupon_type:make:'.session( 'user.node_id'),function()
            {
                return CouponType::where('node_id',session( 'user.node_id'))
                    -> where('status',CouponType::STATUS_ON)
                    -> select('id','activity_id','name','detail_name')
                    -> get()
                    -> toArray();
            });
        }

        return $data;
    }
    /**
     * 清除缓存
     * @return mixed
     */
    public static function delCache()
    {
        if( Cache::has( 'cache:coupon_type:'.session( 'user.node_id') ))
        {
            Cache::forget('cache:coupon_type:'.session( 'user.node_id'));
        }
        if( Cache::has( 'cache:coupon_type:make:'.session( 'user.node_id') ))
        {
            Cache::forget('cache:coupon_type:make:'.session( 'user.node_id'));
        }
    }
}