<?php

namespace App\Dao;

use App\Model\{Storage,Sequence};
use Illuminate\Support\Facades\{Redis,Config};

class StorageDao
{
    /**
     * 生成多级仓库数据
     * @return array
     */
    public static function getStorages()
    {
        $prefix = Config::get('cache.prefix');
        $user   = session( 'user' );
        $data   = json_decode( Redis::hGet( $prefix.':cache:storages:'.session( 'user.node_id'), $user['storage_id'] ), true );
        if( empty( $data ))
        {
            $data = Storage::select( 'id', 'name as text', 'parent_id' )
                -> whereRaw( "FIND_IN_SET( {$user['storage_id']}, `full_id` )" )
                -> where( 'node_id', $user['node_id'] )
                -> where( 'status', Storage::STATUS_ON )
                -> orderBy( 'level' )
                -> orderBy( 'id' )
                -> get()
                -> toArray();
            Redis::hSet( $prefix.':cache:storages:'.session( 'user.node_id'), $user['storage_id'], json_encode( $data, JSON_UNESCAPED_UNICODE ));
        }

        return unlimitedForLayer( $data, 'parent_id', $data[0]['parent_id'], 'nodes' );
    }

    public static function getTransfersStorages()
    {
        $prefix = Config::get('cache.prefix');
        $user   = session( 'user' );
        $data   = json_decode( Redis::hGet( $prefix.':cache:storages_transfers:'.session( 'user.node_id'), $user['storage_id'] ), true );

        if( empty( $data ))
        {
            $parent_id = $user['storage_parent_id'] == 0 ? $user['storage_id'] : $user['storage_parent_id'];
            $data = Storage::select( 'id', 'name as text', 'parent_id' )
                -> whereRaw( "FIND_IN_SET( {$parent_id}, `full_id` )" )
                -> where( 'node_id', $user['node_id'])
                -> where( 'status', Storage::STATUS_ON )
                -> orderBy( 'level' )
                -> orderBy( 'id' )
                -> get()
                -> toArray();
            Redis::hSet( $prefix.':cache:storages_transfers:'.session( 'user.node_id'), $user['storage_id'], json_encode( $data, JSON_UNESCAPED_UNICODE ));
        }

        return unlimitedForLayer( $data, 'parent_id', $data[0]['parent_id'], 'nodes' );
    }

    /**
     * 清除缓存
     * @return mixed
     */
    public static function delCache()
    {
        $user   = session( 'user' );
        $prefix = Config::get('cache.prefix');
        if( Redis::exists( $prefix.':cache:storages:'. $user['node_id'] ))
        {
            Redis::del( $prefix.':cache:storages:'. $user['node_id'] );
        }
        if( Redis::exists( $prefix.':cache:storages_transfers:'. $user['node_id'] ))
        {
            Redis::del( $prefix.':cache:storages_transfers:'. $user['node_id'] );
        }
    }

    /**
     * 设置默认序列号参数
     * @return bool
     */
    public static function setDefaultSeq( $id )
    {
        $data = [
            [
                'name'=> 'db_seq',
                'memo'=> '调拨流水号',
                'type'=> 1,
                'storage_id'=> $id
            ],
            [
                'name'=> 'hq_seq',
                'memo'=> '换券流水号',
                'type'=> 1,
                'storage_id'=> $id
            ],
            [
                'name'=> 'rk_seq',
                'memo'=> '入库流水号',
                'type'=> 1,
                'storage_id'=> $id
            ],
            [
                'name'=> 'tq_seq',
                'memo'=> '退券流水号',
                'type'=> 1,
                'storage_id'=> $id
            ],
            [
                'name'=> 'xs_seq',
                'memo'=> '销售流水号',
                'type'=> 1,
                'storage_id'=> $id
            ],
            [
                'name'=> 'zf_seq',
                'memo'=> '作废流水号',
                'type'=> 1,
                'storage_id'=> $id
            ],
            [
                'name'=> 'zq_seq',
                'memo'=> '制券流水号',
                'type'=> 1,
                'storage_id'=> $id
            ],
            [
                'name'=> 'zzkh_seq',
                'memo'=> '客户流水号',
                'type'=> 0,
                'storage_id'=> $id
            ]
        ];

        Sequence::insert($data);
    }
}