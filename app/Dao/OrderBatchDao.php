<?php

namespace App\Dao;

use Illuminate\Support\Facades\{DB};
use App\Model\{InsideSector,Customer,CouponType,CouponInfo,CouponSale,CouponSaleInfo,CouponStorageStock};

use Lib\PublicClass\{S};

class OrderBatchDao
{
    private static $data          = [];//整理后的批量数据
    private static $totalNum      = 0; //券总数量
    private static $couponType    = [];//券类型集合
    private static $couponTypeRow = [];//券类型数据集合
    private static $coupons       = [];//券号段集合
    private static $couponsRow    = [];//券号段数据集合

    private static $payTypeArr    = ['现金', '转账', '支付宝', '微信', '支票' ];
    private static $error         = '';

    public static function formatData( $arr ):bool
    {
        $i    = 0;
        foreach ( $arr as $k=>  $v )
        {
            if( $k == 0 ) continue;
            if( empty( $v[0] ) || empty( $v[1] ) || empty( $v[2])) continue;
            $page = $k+1;
            if( !in_array( $v[2], self::$couponType )) self::$couponType[] = $v[2];
            if( !empty( $v[4] ) )
            {
                $i ++;
                switch ( $v[4] )
                {
                    case '内部部门':
                        if( empty( $v[5] )) return self::error( "上传文件第{$page}行，部门编号缺失！" );
                        if( empty( $v[7] ) ) return self::error( "上传文件第{$page}行，领用人缺失！" );

                        $user = CustomerDao::getInternalByCode( $v[5] );
                        $type = CouponSale::CUSTOMER_TYPE_INTERNAL;

                        if( count( $user ) < 1 ) return self::error( "上传文件第{$page}行，部门编号不存在！" );
                        if( count( $user ) > 1 ) return self::error( "上传文件第{$page}行，部门编号存在重复！" );
                        $user    = $user[0];
                        $name    = $user['sector_name'];
                        $payType = null;
                        $payText = null;

                        $customerInfo = [
                            'company_id'  => $user['company_id'],
                            'company_name'=> $user['company_name'],
                            'sector_id'   => $user['sector_id'],
                            'sector_name' => $user['sector_name'],
                            'recipients'  => $v[7],
                        ];

                        if( empty( $v[6] ))
                        {
                            $customerInfo['is_pay'] = 1;
                        }
                        else
                        {
                            $customerInfo['is_pay']     = 2;
                            $customerInfo['pay_sector'] = $v[6];
                        }

                        break;
                    case '个人':
                        if( empty( $v[3] )) return self::error( "上传文件第{$page}行，实收金额缺失！" );
                        if( empty( $v[5] ) || empty( $v[6] )) return self::error( "上传文件第{$page}行，用户名称或手机号缺失！" );
                        if( empty( $v[8] )) return self::error( "上传文件第{$page}行，支付信息不存在！" );

                        $user = CustomerDao::getClientByNameAndMobile( trim( $v[5] ), trim( $v[6] ));
                        $type = CouponSale::CUSTOMER_TYPE_CLIENT;

                        if( count( $user ) < 1 ) return self::error( "上传文件第{$page}行，个人用户信息不存在！" );
                        if( count( $user ) > 1 ) return self::error( "上传文件第{$page}行，个人用户信息重复！" );

                        if( in_array( $v[8], self::$payTypeArr ))
                        {
                            $payType = $v[8];
                            $payText = null;
                        }
                        else
                        {
                            $payType = '其他';
                            $payText = $v[8];
                        }

                        $user = $user[0];
                        $name = $user['name'];
                        $customerInfo = [
                            'name'                  => $user['name'],
                            'contact_mobile'        => $user['contact_mobile'],
                            'contact_addr'          => $user['contact_addr'],
                            'contact_email'         => $user['contact_email'],
                            'certificate_type'      => $user['certificate_type'],
                            'certificate_other_type'=> $user['certificate_other_type'],
                            'certificate_code'      => $user['certificate_code'],
                        ];
                        break;
                    case '单位':
                        if( empty( $v[3] )) return self::error( "上传文件第{$page}行，实收金额缺失！" );
                        if( empty( $v[5] ) || empty( $v[6] )) return self::error( "上传文件第{$page}行，联络人名称或联系电话缺失！" );
                        if( empty( $v[8] )) return self::error( "上传文件第{$page}行，支付信息不存在！" );

                        $user = CustomerDao::getCompanyByNameAndTel( $v[5], $v[6] );
                        $type = CouponSale::CUSTOMER_TYPE_COMPANY;

                        if( count( $user ) < 1 ) return self::error( "上传文件第{$page}行，单位信息不存在！" );
                        if( count( $user ) > 1 ) return self::error( "上传文件第{$page}行，单位信息重复！" );

                        if( in_array( $v[8], self::$payTypeArr ))
                        {
                            $payType = $v[8];
                            $payText = null;
                        }
                        else
                        {
                            $payType = '其他';
                            $payText = $v[8];
                        }

                        $user = $user[0];
                        $name = $user['name'];
                        $customerInfo = [
                            'name'                  => $user['name'],
                            'contact_name'          => $user['contact_name'],
                            'contact_tel'           => $user['contact_tel'],
                            'contact_mobile'        => $user['contact_mobile'],
                            'contact_addr'          => $user['contact_addr'],
                            'contact_email'         => $user['contact_email'],
                            'certificate_type'      => $user['certificate_type'],
                            'certificate_other_type'=> $user['certificate_other_type'],
                            'certificate_code'      => $user['certificate_code'],
                        ];
                        break;
                    default:
                        return self::error( "上传文件第{$page}行，客户类型不在范围内！" );
                }

                self::$data[$i] = [
                    'page'         => $page,
                    'customer_type'=> $type,
                    'customer_id'  => array_get( $user, 'id' ),
                    'customer_name'=> $name,
                    'sector_id'    => array_get( $user, 'sector_id' ),
                    'sale_price'   => empty( $v[3] ) ? 0 : $v[3]*100,
                    'pay_type'     => $payType,
                    'pay_text'     => $payText,
                    'customer_info'=> $customerInfo,
                    'memo'         => $v[9],
                ];
                unset( $type );
                unset( $user );
                unset( $name );
                unset( $customerInfo );
                unset( $payType );
                unset( $payText );
            }

            $amount = (int)$v[1] - (int)$v[0] + 1;
            if( $amount < 1 ) return self::error( "上传文件第{$page}行，结束券号小于起始券号！" );
            $key = array_search( $v[2], self::$couponType );
            self::$data[$i]['flow_no'][$key][] = [
                'start'   => (int)$v[0],
                'end'     => (int)$v[1],
                'discount'=> empty( $v[3] )? 100: $v[3],
                'amount'  => $amount,
            ];
            self::$coupons[$key][] = [
                'start' => (int)$v[0],
                'end'   => (int)$v[1]
            ];

            self::$totalNum += $amount;
            unset( $amount );
        }

        return true;
    }

    public static function getCouponsType():bool
    {
        $re = CouponType::whereIn( 'name', self::$couponType )
        -> select( 'id', 'name', 'detail_name', 'class_id', 'class_name', 'price' )
        -> get();
        if( count( self::$couponType ) != $re-> count()) return self::error( '券简称不存在，请检查！' );
        $re = $re-> toArray();

        foreach ( $re as $v )
        {
            $key = array_search( $v['name'], self::$couponType );
            self::$couponTypeRow[$key] = $v;
        }
        return true;
    }

    public static function getCouponsInfo():bool
    {
        $user          = session( 'user' );
        $coupons       = self::$coupons;
        $couponTypeRow = self::$couponTypeRow;
        $couponInfo = CouponInfo::where( 'node_id', $user['node_id'] )
            -> where( 'storage_id', $user['storage_id'] )
            -> where( 'status', CouponInfo::STATUS_INVENTORY )
            -> where( function ( $query1 ) use( $coupons, $couponTypeRow ) {
                foreach ( $coupons as $key=> $values )
                {
                    $query1-> orWhere(function ( $query2 ) use( $key, $values, $couponTypeRow ) {
                        $query2-> where( 'coupon_type_id', $couponTypeRow[$key]['id'] )
                            -> where( function ( $query3 ) use( $values ){
                                foreach ( $values as $v )
                                {
                                    $query3-> orWhereBetween( 'coupon_flow_no', [ $v['start'], $v['end'] ]);
                                }
                            });
                    });
                }
            })-> get();
        if( self::$totalNum != $couponInfo-> count()) return self::error( '券号有错误，请检查！' );
        self::$couponsRow = $couponInfo-> toArray();
        return true;
    }

    public static function createOrders():bool
    {
        $user           = session( 'user' );
        $insertSale     = [];
        $insertSaleInfo = [];
        $seqArr         = [];
        $storageStock   = [];

        //整理出插入销售总表和详情表的数据
        foreach ( self::$data as $k=> $value )
        {
            $totalAmount     = 0;//总数量
            $totalPrice      = 0;//总原价
            $totalSalePrice  = $value['sale_price'];//总优惠价格
            $bogAmount       = 0;//bog券总数
            $gcAmount        = 0;//gc券总数
            $seq             = getOperateSeq('销售');
            $seqArr[]        = $seq;

            foreach ( $value['flow_no'] as $key=> $flow_no )
            {
                $amount = 0;
                $type     = self::$couponTypeRow[$key];
                foreach ( $flow_no as $v )
                {
                    if( !isset( $discount )) $discount = $v['discount'];
                    $totalAmount += $v['amount'];
                    $amount      += $v['amount'];
                    //详情表数据
                    $insertSaleInfo[] = [
                        'seq'              => $seq,
                        'node_id'          => $user['node_id'],
                        'storage_id'       => $user['storage_id'],
                        'storage_name'     => $user['storage_name'],
                        'coupon_class_id'  => $type['class_id'],
                        'coupon_class_name'=> $type['class_name'],
                        'coupon_type_id'   => $type['id'],
                        'coupon_type_name' => $type['name'],
                        'start_flow_no'    => $v['start'],
                        'end_flow_no'      => $v['end'],
                        'amount'           => $v['amount'],
                        'price'            => $type['price'],
                        'discount'         => $discount,
                    ];
                }
                if( $type['class_id'] == 1 )
                {
                    $gcAmount       += $amount;
                    $totalPrice     += floor( (int)$type['price'] * $amount );
                }
                else
                {
                    $bogAmount      += $amount;
                    $totalPrice     += 0;
                }
                //组装库存加减数据
                if( !isset( $storageStock[$key] ))
                {
                    $storageStock[$key] = [
                        'type_id'=> $type['id'],
                        'amount' => $amount,
                    ];
                }
                else
                {
                    $storageStock[$key]['amount'] += $amount;
                }

                unset( $discount );
                unset( $amount );
                unset( $type );
            }

            if( $value['customer_type'] == 2 )
            {
                if( $totalPrice >= 50000 )
                {
                    if( empty( $value['customer_info']['certificate_type'] ) || empty( $value['customer_info']['certificate_code'] ))
                    {
                        return self::error("上传文件第{$value['page']}行，个人大于5W，证件号需要填");
                    }
                }
            }
            elseif ( $value['customer_type'] == 3 )
            {
                if( $totalPrice >= 5000 )
                {
                    if( empty( $value['customer_info']['certificate_type'] ) || empty( $value['customer_info']['certificate_code'] ))
                    {
                        return self::error("上传文件第{$value['page']}行，单位大于5K，证件号需要填！");
                    }
                }
            }

            //总表数据
            $insertSale[] = [
                'node_id'          => $user['node_id'],
                'seq'              => $seq,
                'storage_id'       => $user['storage_id'],
                'storage_name'     => $user['storage_name'],
                'bog_amount'       => $bogAmount,
                'gc_amount'        => $gcAmount,
                'amount'           => $totalAmount,
                'price'            => $totalPrice,
                'sale_price'       => $totalSalePrice,
                'status'           => CouponSale::STATUS_PENDING,
                'customer_name'    => $value['customer_name'],
                'sector_id'        => $value['sector_id'],
                'customer_type'    => $value['customer_type'],
                'customer_id'      => $value['customer_id'],
                'customer_info'    => json_encode( $value['customer_info'], JSON_UNESCAPED_UNICODE ),
                'pay_type'         => $value['pay_type'],
                'pay_text'         => $value['pay_text'],
                'memo'             => $value['memo'],
                'sale_time'        => date('Y-m-d H:i:s'),
                'request_user_id'  => $user['id'],
                'request_user_name'=> $user['nickname'],
                'request_time'     => date('Y-m-d H:i:s'),
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ];
            unset( $totalAmount );
            unset( $totalPrice );
            unset( $totalSalePrice );
            unset( $bogAmount );
            unset( $gcAmount );
            unset( $seq );
        }

        //开启事务 存数据
        DB::beginTransaction();
        try
        {
            //存入总表
            CouponSale::insert( $insertSale );
            //获取主表ID 并按ID排序内容是流水号
            $re = CouponSale::whereIn( 'seq', $seqArr )-> pluck( 'seq', 'id' )-> toArray();
            foreach ( $insertSaleInfo as $k=> $v )
            {
                $insertSaleInfo[$k]['id'] = array_search( $v['seq'], $re );
            }
            //存入详情表
            CouponSaleInfo::insert($insertSaleInfo);
            //循环变更库存
            foreach ( $storageStock as $v )
            {
                CouponStorageStock::where( 'node_id', $user['node_id'] )
                    -> where( 'storage_id', $user['storage_id'] )
                    -> where( 'coupon_type_id', $v['type_id'] )
                    -> update([
                        'amount_no_sale'=> DB::raw( "amount_no_sale - {$v['amount']}" ),
                        'amount_audit'  => DB::raw( "amount_audit + {$v['amount']}" )
                    ]);
            }
            //变更券状态
            $couponId = array_column( self::$couponsRow, 'id' );
            CouponInfo::whereIn( 'id', $couponId )-> update([ 'status'=> CouponInfo::STATUS_PENDING ]);
            DB::commit();
            return true;
        }
        catch ( \Exception $e )
        {
            DB::rollBack();
            return self::error('数据库存入失败');
        }
    }

    public static function getError():string
    {
        return self::$error;
    }
    private static function error( $msg )
    {
        self::$error = $msg;
        return false;
    }
    public static function show()
    {
        var_dump( self::$couponTypeRow );
    }
}