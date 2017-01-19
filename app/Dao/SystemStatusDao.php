<?php

namespace App\Dao;

use App\Model\{Status};
use Illuminate\Support\Facades\{Cache};

class SystemStatusDao
{
    const CACHE_TIME = 1440; //缓存时间(分钟)
    public static function getCouponPutIn()
    {
        $table = 'coupon_put_in';
        $data  = Cache::remember( "cache:system_status:{$table}", self::CACHE_TIME, function() use( $table )
        {
            return Status::where( 'table', $table )
                    -> select('status','text')
                    -> orderBy('status')
                    -> pluck( 'text', 'status' )
                    -> toArray();
        });
        return $data;
    }
    public static function getCouponTransfers()
    {
        $table = 'coupon_transfers';
        $data  = Cache::remember( "cache:system_status:{$table}", self::CACHE_TIME, function() use( $table )
        {
            return Status::where( 'table', $table )
                -> select('status','text')
                -> orderBy('status')
                -> pluck( 'text', 'status' )
                -> toArray();
        });
        return $data;
    }
    public static function getCouponInvalid()
    {
        $table = 'coupon_invalid';
        $data  = Cache::remember( "cache:system_status:{$table}", self::CACHE_TIME, function() use( $table )
        {
            return Status::where( 'table', $table )
                -> select('status','text')
                -> orderBy('status')
                -> pluck( 'text', 'status' )
                -> toArray();
        });
        return $data;
    }
    public static function getCouponSale()
    {
        $table = 'coupon_sale';
        $data  = Cache::remember( "cache:system_status:{$table}", self::CACHE_TIME, function() use( $table )
        {
            return Status::where( 'table', $table )
                -> select('status','text')
                -> orderBy('status')
                -> pluck( 'text', 'status' )
                -> toArray();
        });
        return $data;
    }
    public static function getCouponReplace()
    {
        $table = 'coupon_replace';
        $data  = Cache::remember( "cache:system_status:{$table}", self::CACHE_TIME, function() use( $table )
        {
            return Status::where( 'table', $table )
                -> select('status','text')
                -> orderBy('status')
                -> pluck( 'text', 'status' )
                -> toArray();
        });
        return $data;
    }
    /*
     * 制券状态值
     */
    public static function getCouponMake()
    {
        $table = 'coupon_make';
        $data  = Cache::remember( "cache:system_status:{$table}", self::CACHE_TIME, function() use( $table )
        {
            return Status::where( 'table', $table )
                    -> select('status','text')
                    -> orderBy('status')
                    -> pluck( 'text', 'status' )
                    -> toArray();
        });
        return $data;
    }

    /*
     * 券详情状态
     */
    public static function getCouponInfo()
    {
        $table = 'coupon_info';
        $data  = Cache::remember( "cache:system_status:{$table}", self::CACHE_TIME, function() use( $table )
        {
            return Status::where( 'table', $table )
                -> select('status','text')
                -> orderBy('status')
                -> pluck( 'text', 'status' )
                -> toArray();
        });
        return $data;
    }

    /*
     * 内部部门状态
     */
    public static function getInsideSector()
    {
        $table = 'inside_sector';
        $data  = Cache::remember( "cache:system_status:{$table}", self::CACHE_TIME, function() use( $table )
        {
            return Status::where( 'table', $table )
                    -> select('status','text')
                    -> orderBy('status')
                    -> pluck( 'text', 'status' )
                    -> toArray();
        });
        return $data;
    }
    /**
     * 客户状态
     * @param $type string  审核状态：audit   |   使用状态：status  |  客户类型： type 
     */
    public static function getCustomer($type){

        $table = 'customer_'.$type;
        $data  = Cache::remember( "cache:system_status:{$table}", self::CACHE_TIME, function() use( $table )
        {
            return Status::where( 'table', $table )
                    -> select('status','text')
                    -> orderBy('status')
                    -> pluck( 'text', 'status' )
                    -> toArray();
        });
        return $data;

    }
    /*
     * 退券状态
     */
    public static function getcouponFallback()
    {
        $table = 'coupon_fallback';
        $data  = Cache::remember( "cache:system_status:{$table}", self::CACHE_TIME, function() use( $table )
        {
            return Status::where( 'table', $table )
                    -> select('status','text')
                    -> orderBy('status')
                    -> pluck( 'text', 'status' )
                    -> toArray();
        });
        return $data;
    }






}