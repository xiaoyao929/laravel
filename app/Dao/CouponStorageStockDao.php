<?php

namespace App\Dao;

class CouponStorageStockDao
{
    private static $insertParam = [];
    private static $updateParam = [];
    /**
     * 拼装插入SQL数据
     * @param array $data //输入数据
     */
    public static function setInsertParParam( $data )
    {
        $user = session('user');
        if( isset( self::$insertParam[$data['coupon_type_id']] ))//是否存在数组
        {
            self::$insertParam[$data['coupon_type_id']]['amount_no_sale'] += (int)$data['amount'];
        }
        else
        {
            self::$insertParam[$data['coupon_type_id']] = [
                'node_id'          => $user['node_id'],
                'storage_id'       => $data['storage_id'],
                'storage_name'     => $data['storage_name'],
                'coupon_class_id'  => $data['coupon_class_id'],
                'coupon_class_name'=> $data['coupon_class_name'],
                'coupon_type_id'   => $data['coupon_type_id'],
                'coupon_type_name' => $data['coupon_type_name'],
                'amount_no_sale'   => (int)$data['amount'],
                'amount_saled'     => 0,
                'amount_used'      => 0,
                'amount_destroyed' => 0,
                'amount_audit'     => 0,
                'amount_transfers' => 0,
            ];
        }
    }

    /**
     * 拼装更新数据
     * @param $data //输入数据
     */
    public static function setUpdateParam( $data )
    {
        self::$updateParam[] = $data;
    }
    public static function getInsertParam()
    {
        return self::$insertParam;
    }
    public static function getUpdateParam()
    {
        return self::$updateParam;
    }
}