<?php

namespace App\Http\Controllers\OpenApi;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\{DB};

use Lib\PublicClass\{S,Log};
use App\Model\{CouponInfo,NodeInfo,StatNoSale,StatNoSaleTime};


class DayController extends Controller
{
    public function noSale()
    {
        $ipString = S::getIp();
        $ipArray  = explode( ',', $ipString );
        $action   = false;
        $allowIp  = [ '127.0.0.1', '192.168.171.1' ];
        foreach ( $ipArray as $v )
        {
            if( in_array( $v, $allowIp )) $verify = true;
        }
        if( !$verify ) abort( 403, '拒绝访问' );
        $nodes = NodeInfo::where( 'status', '1' )-> get()-> toArray();
        $time  = date('Y-m-d H:i:s');
        foreach ( $nodes as $node )
        {
            $coupons = CouponInfo::where( 'node_id', $node['node_id'] )
                -> where( function ( $query ){
                    $query-> orWhere( 'status', CouponInfo::STATUS_INVENTORY )
                        -> orWhere( 'status', CouponInfo::STATUS_INVALID )
                        -> orWhere( 'status', CouponInfo::STATUS_TRANSIT )
                        -> orWhere( 'status', CouponInfo::STATUS_PENDING );
                })
                -> select( 'coupon_flow_no', 'coupon_class_id', 'coupon_class_name', 'coupon_type_id', 'coupon_type_name',
                    'storage_id', 'storage_name', 'status' )
                -> orderBy( 'coupon_flow_no', 'asc' )
                -> get()
                -> toArray();
            $data = [];
            foreach ( $coupons as $v )
            {
                if( !isset( $data[$v['storage_id']] ))
                {
                    $data[$v['storage_id']] = [
                        'storage_id'   =>$v['storage_id'],
                        'storage_name' =>$v['storage_name'],
                    ];
                }
                if( !isset( $data[$v['storage_id']]['data'][$v['coupon_type_id']] ))
                {
                    $data[$v['storage_id']]['data'][$v['coupon_type_id']] = [
                        'coupon_class_id'  => $v['coupon_class_id'],
                        'coupon_class_name'=> $v['coupon_class_name'],
                        'coupon_type_id'   => $v['coupon_type_id'],
                        'coupon_type_name' => $v['coupon_type_name'],
                    ];
                }
                $data[$v['storage_id']]['data'][$v['coupon_type_id']]['data'][] = [
                    'status'        => $v['status'],
                    'coupon_flow_no'=> $v['coupon_flow_no'],
                ];
            }

            $param           = [];
            $start           = '';
            $end             = '';
            $amountNoSale    = 0;
            $amountDestroyed = 0;
            $amountTransfers = 0;
            $amountAudit     = 0;

            DB::beginTransaction();

            try
            {
                $id = StatNoSaleTime::insertGetId(['time'=> $time]);
            }
            catch ( \Exception $e )
            {
                DB::rollBack();
                Log::log_write( '生成时间ID出错', '', 'noSaleDayError' );
                S::error('50001');
            }


            foreach ( $data as $storage )
            {
                foreach ( $storage['data'] as $type )
                {
                    foreach ( $type['data'] as $k=> $v )
                    {
                        if( $k == 0 )//第一条记录重新初始化数据
                        {
                            $amountNoSale    = 0;
                            $amountDestroyed = 0;
                            $amountTransfers = 0;
                            $amountAudit     = 0;

                            $start           = $v['coupon_flow_no'];
                            $end             = $v['coupon_flow_no'];
                        }
                        elseif ( $k + 1  == count( $type['data'] ))//最后一条记录
                        {
                            //先加数量
                            switch ( $v['status'] )
                            {
                                case '1':
                                    $amountNoSale++;    //库存
                                    break;
                                case '3':
                                    $amountDestroyed++; //作废
                                    break;
                                case '4':
                                    $amountTransfers++; //调拨
                                    break;
                                case '6':
                                    $amountAudit++;     // 审核
                                    break;
                            }
                            $param[] = [
                                'node_id'          => $node['node_id'],
                                'time_id'          => $id,
                                'storage_id'       => $storage['storage_id'],
                                'storage_name'     => $storage['storage_name'],
                                'coupon_class_id'  => $type['coupon_class_id'],
                                'coupon_class_name'=> $type['coupon_class_name'],
                                'coupon_type_id'   => $type['coupon_type_id'],
                                'coupon_type_name' => $type['coupon_type_name'],
                                'start_flow_no'    => $start,
                                'end_flow_no'      => $end,
                                'amount_no_sale'   => $amountNoSale,
                                'amount_destroyed' => $amountDestroyed,
                                'amount_audit'     => $amountAudit,
                                'amount_transfers' => $amountTransfers,
                                'time'             => $time,
                            ];
                            unset($start);
                            unset($end);
                            unset($amountNoSale);
                            unset($amountDestroyed);
                            unset($amountAudit);
                            unset($amountTransfers);
                            continue;
                        }
                        else
                        {
                            if(((int)$end + 1 ) != (int)$v['coupon_flow_no'] )//如果结束号+1不等于新号
                            {
                                $param[] = [
                                    'node_id'          => $node['node_id'],
                                    'time_id'          => $id,
                                    'storage_id'       => $storage['storage_id'],
                                    'storage_name'     => $storage['storage_name'],
                                    'coupon_class_id'  => $type['coupon_class_id'],
                                    'coupon_class_name'=> $type['coupon_class_name'],
                                    'coupon_type_id'   => $type['coupon_type_id'],
                                    'coupon_type_name' => $type['coupon_type_name'],
                                    'start_flow_no'    => $start,
                                    'end_flow_no'      => $end,
                                    'amount_no_sale'   => $amountNoSale,
                                    'amount_destroyed' => $amountDestroyed,
                                    'amount_audit'     => $amountAudit,
                                    'amount_transfers' => $amountTransfers,
                                    'time'             => $time,
                                ];
                                $start = $v['coupon_flow_no'];
                                $end   = $v['coupon_flow_no'];
                                $amountNoSale    = 0;
                                $amountDestroyed = 0;
                                $amountTransfers = 0;
                                $amountAudit     = 0;
                            }
                            else
                            {
                                $end = $v['coupon_flow_no'];
                            }
                        }
                        switch ( $v['status'] )
                        {
                            case '1':
                                $amountNoSale++;    //库存
                                break;
                            case '3':
                                $amountDestroyed++; //作废
                                break;
                            case '4':
                                $amountTransfers++; //调拨
                                break;
                            case '6':
                                $amountAudit++;     // 审核
                                break;
                        }
                    }
                }
            }
            unset($data);
            try
            {
                StatNoSale::insert( $param );
                DB::commit();
                Log::log_write( '存入未销售统计结果成功,时间:'.date('Y-m-d H:i:s'), '', 'noSaleDayError' );
                return S::jsonReturn('ok');
            }
            catch ( \Exception $e )
            {
                DB::rollBack();
                Log::log_write( '存入未销售统计结果时出错,数据:'.json_encode( $param, JSON_UNESCAPED_UNICODE ), '', 'noSaleDayError' );
                S::error('50001');
            }
        }
    }
}