<?php

namespace App\Dao;

use Illuminate\Support\Facades\{Cache};

class CustomerTypeDao
{
    public static function getInfo()
    {
        return Cache::rememberForever( 'cache:customer_type:'.session( 'user.node_id'), function() {
            return $customerType = [
                1=> [
                    'id'  => 1,
                    'type'=> 'internal',
                    'name'=> '内部领用',
                ],
                2=> [
                    'id'  => 2,
                    'type'=> 'client',
                    'name'=> '个人',
                ],
                3=> [
                    'id'  => 3,
                    'type'=> 'company',
                    'name'=> '公司',
                ],
            ];
        });
    }
    public static function getCertificate()
    {
        return Cache::rememberForever( 'cache:certificate_type:'.session( 'user.node_id'), function() {
            return [
                    1 => '身份证',
                    2 => '护照',
                    3 => '营业执照',
                    4 => '机构代码证',
                    5 => '其他',
            ];
        });
    }
}