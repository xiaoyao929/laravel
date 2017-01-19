<?php

namespace App\Dao;

use Illuminate\Support\Facades\{DB};
use App\Model\{InsideSector,Customer,CouponType,CouponInfo,CouponSale,CouponSaleInfo,CouponStorageStock};

use Lib\PublicClass\{S};

class OrderDao
{
    /**
     * 创建内部订单
     * @param $data
     * @return $this
     */
    public static function createInternalOrder( $data )
    {
        $user = self::getCustomerInfo( $data['customer_id'], $data['customer_type'] );
        //验证券是否正确
        //验证券类型是否有重复
        $typeId = array_column( $data['product'], 'id' );
        if( count( $typeId ) !== count( array_unique( $typeId ))) S::error('40003');

        $session = session('user');
        $param   = [];
        $coupons = [];

        DB::beginTransaction();
        $seq        = getOperateSeq('销售');
        $couponSale = new CouponSale();
        $couponSale-> node_id       = $session['node_id'];
        $couponSale-> seq           = $seq;
        $couponSale-> storage_id    = $session['storage_id'];
        $couponSale-> storage_name  = $session['storage_name'];
        $couponSale-> status        = CouponSale::STATUS_PENDING;
        $couponSale-> amount        = 0;
        $couponSale-> gc_amount     = 0;
        $couponSale-> bog_amount    = 0;
        $couponSale-> price         = 0;
        $couponSale-> sale_price    = 0;
        $couponSale-> customer_type = CouponSale::CUSTOMER_TYPE_INTERNAL;
        $couponSale-> customer_id   = $data['customer_id'];
        $couponSale-> customer_name = $user['sector_name'];
        $couponSale-> sector_id     = $user['sector_id'];
        $couponSale-> customer_info = json_encode( [
            'company_id'  => $user['company_id'],
            'company_name'=> $user['company_name'],
            'sector_id'   => $user['sector_id'],
            'sector_name' => $user['sector_name'],
            'is_pay'      => $data['is_pay'],
            'pay_sector'  => $data['pay_sector'],
            'recipients'  => $data['recipients'],
        ], JSON_UNESCAPED_UNICODE );
        $couponSale-> sale_time         = date('Y-m-d H:i:s');
        $couponSale-> sale_price        = $data['collection_price']*100;
        $couponSale-> memo              = $data['memo'];
        $couponSale-> request_user_id   = $session['id'];
        $couponSale-> request_user_name = $session['nickname'];
        $couponSale-> request_time      = date('Y-m-d H:i:s');

        try
        {
            foreach ( $data['product'] as $v )
            {
                if( empty( $v['id'] ) || empty( $v['product'] )) S::error( '40001' );
                //获取优惠券信息
                $v['type_info']   = self::getCouponTypeInfo( $v['id'] );
                //验证券是否存在仓库并获取券详情
                $v['coupon_info'] = self::verifyCoupon( $v );
                $num = count($v['coupon_info']);
                $couponSale-> amount += $num;

                if( $v['type_info']['class_id'] == CouponType::TYPE_GC ) $couponSale-> gc_amount += $num;   //记录GC券数量
                if( $v['type_info']['class_id'] == CouponType::TYPE_BOG ) $couponSale-> bog_amount += $num; //记录BOG券数量

                $couponSale-> price  += (int)$v['type_info']['price'] * $num;
                if( empty( $v['discount'] )) $discount = 100;
                else $discount = (int)$v['discount'];

                CouponStorageStock::where( 'node_id', $session['node_id'] )
                    -> where( 'storage_id', $session['storage_id'] )
                    -> where( 'coupon_type_id', $v['type_info']['id'] )
                    -> update([
                        'amount_no_sale'=> DB::raw( "amount_no_sale - {$num}" ),
                        'amount_audit'  => DB::raw( "amount_audit + {$num}" )
                    ]);

                $coupons = array_merge( $coupons, array_column( $v['coupon_info'], 'id' ));

                foreach ( $v['product'] as $product )
                {
                    $amount  = (int)$product['end'] - (int)$product['start'] + 1;
                    $param[] = [
                        'node_id'          => $session['node_id'],
                        'seq'              => $seq,
                        'storage_id'       => $session['storage_id'],
                        'storage_name'     => $session['storage_name'],
                        'coupon_class_id'  => $v['type_info']['class_id'],
                        'coupon_class_name'=> $v['type_info']['class_name'],
                        'coupon_type_id'   => $v['type_info']['id'],
                        'coupon_type_name' => $v['type_info']['name'],
                        'start_flow_no'    => $product['start'],
                        'end_flow_no'      => $product['end'],
                        'amount'           => $amount,
                        'price'            => $v['type_info']['price'],
                        'discount'         => $discount,
                    ];
                }
            }

            $couponSale-> save();
            foreach ( $param as $k=> $v )
            {
                $param[$k]['id'] = $couponSale-> id;
            }
            CouponSaleInfo::insert($param);
            CouponInfo::whereIn( 'id', $coupons )-> update([ 'status'=> CouponInfo::STATUS_PENDING ]);
            DB::commit();
            return S::jsonReturn( '提交成功' );
        }
        catch ( \Exception $e )
        {
            DB::rollBack();
            S::error('50001');
        }
    }

    /**
     * 创建个人客户订单
     * @param $data
     * @return $this
     */
    public static function createClientOrder( $data )
    {
        $user = self::getCustomerInfo( $data['customer_id'], $data['customer_type'], $data );
        //验证券是否正确
        //验证券类型是否有重复
        $typeId = array_column( $data['product'], 'id' );
        if( count( $typeId ) !== count( array_unique( $typeId ))) S::error('40003');

        $session = session('user');
        $param   = [];
        $coupons = [];

        DB::beginTransaction();
        $seq        = getOperateSeq('销售');
        $couponSale = new CouponSale();
        $couponSale-> node_id       = $session['node_id'];
        $couponSale-> seq           = $seq;
        $couponSale-> storage_id    = $session['storage_id'];
        $couponSale-> storage_name  = $session['storage_name'];
        $couponSale-> status        = CouponSale::STATUS_PENDING;
        $couponSale-> amount        = 0;
        $couponSale-> gc_amount     = 0;
        $couponSale-> bog_amount    = 0;
        $couponSale-> price         = 0;
        $couponSale-> sale_price    = 0;
        $couponSale-> customer_type = CouponSale::CUSTOMER_TYPE_CLIENT;
        $couponSale-> customer_id   = $data['customer_id'];
        $couponSale-> pay_type      = $data['pay_type'];
        $couponSale-> pay_text      = $data['pay_text'];
        $couponSale-> customer_name = $user['name'];
        $couponSale-> customer_info = json_encode( [
            'name'                  => $user['name'],
            'contact_mobile'        => $user['contact_mobile'],
            'contact_addr'          => $user['contact_addr'],
            'contact_email'         => $user['contact_email'],
            'certificate_type'      => $user['certificate_type'],
            'certificate_other_type'=> $user['certificate_other_type'],
            'certificate_code'      => $user['certificate_code'],
        ], JSON_UNESCAPED_UNICODE );
        $couponSale-> sale_time         = date('Y-m-d H:i:s');
        $couponSale-> sale_price        = $data['collection_price']*100;
        $couponSale-> memo              = $data['memo'];
        $couponSale-> request_user_id   = $session['id'];
        $couponSale-> request_user_name = $session['nickname'];
        $couponSale-> request_time      = date('Y-m-d H:i:s');

        try
        {
            foreach ( $data['product'] as $v )
            {
                if( empty( $v['id'] ) || empty( $v['product'] )) S::error( '40001' );
                //获取优惠券信息
                $v['type_info']   = self::getCouponTypeInfo( $v['id'] );
                //验证券是否存在仓库并获取券详情
                $v['coupon_info'] = self::verifyCoupon( $v );
                $num = count($v['coupon_info']);
                $couponSale-> amount += $num;

                if( $v['type_info']['class_id'] == CouponType::TYPE_GC ) $couponSale-> gc_amount += $num;   //记录GC券数量
                if( $v['type_info']['class_id'] == CouponType::TYPE_BOG ) $couponSale-> bog_amount += $num; //记录BOG券数量

                $couponSale-> price  += (int)$v['type_info']['price'] * $num;
                if( empty( $v['discount'] )) $discount = 100;
                else $discount = (int)$v['discount'];

                CouponStorageStock::where( 'node_id', $session['node_id'] )
                    -> where( 'storage_id', $session['storage_id'] )
                    -> where( 'coupon_type_id', $v['type_info']['id'] )
                    -> update([
                        'amount_no_sale'=> DB::raw( "amount_no_sale - {$num}" ),
                        'amount_audit'  => DB::raw( "amount_audit + {$num}" )
                    ]);

                $coupons = array_merge( $coupons, array_column( $v['coupon_info'], 'id' ));

                foreach ( $v['product'] as $product )
                {
                    $amount  = (int)$product['end'] - (int)$product['start'] + 1;
                    $param[] = [
                        'node_id'          => $session['node_id'],
                        'seq'              => $seq,
                        'storage_id'       => $session['storage_id'],
                        'storage_name'     => $session['storage_name'],
                        'coupon_class_id'  => $v['type_info']['class_id'],
                        'coupon_class_name'=> $v['type_info']['class_name'],
                        'coupon_type_id'   => $v['type_info']['id'],
                        'coupon_type_name' => $v['type_info']['name'],
                        'start_flow_no'    => $product['start'],
                        'end_flow_no'      => $product['end'],
                        'amount'           => $amount,
                        'price'            => $v['type_info']['price'],
                        'discount'         => $discount,
                    ];
                }
            }

            $couponSale-> save();
            foreach ( $param as $k=> $v )
            {
                $param[$k]['id'] = $couponSale-> id;
            }
            CouponSaleInfo::insert($param);
            CouponInfo::whereIn( 'id', $coupons )-> update([ 'status'=> CouponInfo::STATUS_PENDING ]);
            DB::commit();
            return S::jsonReturn( '提交成功' );
        }
        catch ( \Exception $e )
        {
            DB::rollBack();
            S::error('50001');
        }
    }

    /**
     * 创建企业用户订单
     * @param $data
     * @return $this
     */
    public static function createCompanyOrder( $data )
    {
        $user = self::getCustomerInfo( $data['customer_id'], $data['customer_type'], $data );
        //验证券是否正确
        //验证券类型是否有重复
        $typeId = array_column( $data['product'], 'id' );
        if( count( $typeId ) !== count( array_unique( $typeId ))) S::error('40003');

        $session = session('user');
        $param   = [];
        $coupons = [];

        DB::beginTransaction();
        $seq        = getOperateSeq('销售');
        $couponSale = new CouponSale();
        $couponSale-> node_id       = $session['node_id'];
        $couponSale-> seq           = $seq;
        $couponSale-> storage_id    = $session['storage_id'];
        $couponSale-> storage_name  = $session['storage_name'];
        $couponSale-> status        = CouponSale::STATUS_PENDING;
        $couponSale-> amount        = 0;
        $couponSale-> gc_amount     = 0;
        $couponSale-> bog_amount    = 0;
        $couponSale-> price         = 0;
        $couponSale-> sale_price    = 0;
        $couponSale-> customer_type = CouponSale::CUSTOMER_TYPE_COMPANY;
        $couponSale-> customer_id   = $data['customer_id'];
        $couponSale-> pay_type      = $data['pay_type'];
        $couponSale-> pay_text      = $data['pay_text'];
        $couponSale-> customer_name = $user['name'];
        $couponSale-> customer_info = json_encode( [
            'name'                  => $user['name'],
            'contact_name'          => $user['contact_name'],
            'contact_tel'           => $user['contact_tel'],
            'contact_mobile'        => $user['contact_mobile'],
            'contact_addr'          => $user['contact_addr'],
            'contact_email'         => $user['contact_email'],
            'certificate_type'      => $user['certificate_type'],
            'certificate_other_type'=> $user['certificate_other_type'],
            'certificate_code'      => $user['certificate_code'],
        ], JSON_UNESCAPED_UNICODE );
        $couponSale-> sale_time         = date('Y-m-d H:i:s');
        $couponSale-> sale_price        = $data['collection_price']*100;
        $couponSale-> memo              = $data['memo'];
        $couponSale-> request_user_id   = $session['id'];
        $couponSale-> request_user_name = $session['nickname'];
        $couponSale-> request_time      = date('Y-m-d H:i:s');

        try
        {
            foreach ( $data['product'] as $v )
            {
                if( empty( $v['id'] ) || empty( $v['product'] )) S::error( '40001' );
                //获取优惠券信息
                $v['type_info']   = self::getCouponTypeInfo( $v['id'] );
                //验证券是否存在仓库并获取券详情
                $v['coupon_info'] = self::verifyCoupon( $v );
                $num = count($v['coupon_info']);
                $couponSale-> amount += $num;

                if( $v['type_info']['class_id'] == CouponType::TYPE_GC ) $couponSale-> gc_amount += $num;   //记录GC券数量
                if( $v['type_info']['class_id'] == CouponType::TYPE_BOG ) $couponSale-> bog_amount += $num; //记录BOG券数量

                $couponSale-> price  += (int)$v['type_info']['price'] * $num;
                if( empty( $v['discount'] )) $discount = 100;
                else $discount = (int)$v['discount'];

                CouponStorageStock::where( 'node_id', $session['node_id'] )
                    -> where( 'storage_id', $session['storage_id'] )
                    -> where( 'coupon_type_id', $v['type_info']['id'] )
                    -> update([
                        'amount_no_sale'=> DB::raw( "amount_no_sale - {$num}" ),
                        'amount_audit'  => DB::raw( "amount_audit + {$num}" )
                    ]);

                $coupons = array_merge( $coupons, array_column( $v['coupon_info'], 'id' ));

                foreach ( $v['product'] as $product )
                {
                    $amount  = (int)$product['end'] - (int)$product['start'] + 1;
                    $param[] = [
                        'node_id'          => $session['node_id'],
                        'seq'              => $seq,
                        'storage_id'       => $session['storage_id'],
                        'storage_name'     => $session['storage_name'],
                        'coupon_class_id'  => $v['type_info']['class_id'],
                        'coupon_class_name'=> $v['type_info']['class_name'],
                        'coupon_type_id'   => $v['type_info']['id'],
                        'coupon_type_name' => $v['type_info']['name'],
                        'start_flow_no'    => $product['start'],
                        'end_flow_no'      => $product['end'],
                        'amount'           => $amount,
                        'price'            => $v['type_info']['price'],
                        'discount'         => $discount,
                    ];
                }
            }

            $couponSale-> save();
            foreach ( $param as $k=> $v )
            {
                $param[$k]['id'] = $couponSale-> id;
            }
            CouponSaleInfo::insert($param);
            CouponInfo::whereIn( 'id', $coupons )-> update([ 'status'=> CouponInfo::STATUS_PENDING ]);
            DB::commit();
            return S::jsonReturn( '提交成功' );
        }
        catch ( \Exception $e )
        {
            DB::rollBack();
            S::error('50001');
        }
    }

    /**
     * 获取用户信息
     * @param $id
     * @param $type
     * @return mixed
     */
    private static function getCustomerInfo( $id, $type, $data=null )
    {
        switch ( $type )
        {
            case 'internal':
                $re = InsideSector::where( 'status', InsideSector::STATUS_TRANSIT )
                    -> where( 'request_storage_id', session( 'user.storage_id' ))
                    -> where( 'node_id', session( 'user.node_id' ))
                    -> where( 'id', $id )
                    -> first();
                if( empty( $re )) S::error('40006');
                break;
            case 'client':
                $re = Customer::where( 'status', Customer::STATUS_ON )
                    -> where( 'request_storage_id', session( 'user.storage_id' ))
                    -> where( 'node_id', session( 'user.node_id' ))
                    -> where( 'customer_type', Customer::TYPE_CLIENT )
                    -> where( 'id', $id )
                    -> first();
                if( empty( $re )) S::error('40006');
                if( empty( $re-> certificate_type ) || empty( $re-> certificate_code ))
                {
                    if( !empty( $data['certificate_type'] ))
                    {
                        switch ( $data['certificate_type'] )
                        {
                            case '身份证':
                                $re-> certificate_type = Customer::CERTIFICATE_TYPE_ID_CARD;
                                break;
                            case '护照':
                                $re-> certificate_type = Customer::CERTIFICATE_TYPE_PASSPORT;
                                break;
                            case '营业执照':
                                $re-> certificate_type = Customer::CERTIFICATE_TYPE_BUSINESS_LICENSE;
                                break;
                            case '机构代码证':
                                $re-> certificate_type = Customer::CERTIFICATE_TYPE_ORGANIZATION_CODE;
                                break;
                            case '其他':
                                $re-> certificate_type = Customer::CERTIFICATE_TYPE_OTHER;
                                break;
                        }

                        empty( $data['certificate_other_type'] ) || $re-> certificate_other_type = $data['certificate_other_type'];
                        empty( $data['certificate_code'] )       || $re-> certificate_code = $data['certificate_code'];

                        try
                        {
                            $re-> save();
                        }
                        catch ( \Exception $e )
                        {
                            S::error('50001');
                        }
                    }
                }
                break;
            case 'company':
                $re = Customer::where( 'status', Customer::STATUS_ON )
                    -> where( 'request_storage_id', session( 'user.storage_id' ))
                    -> where( 'node_id', session( 'user.node_id' ))
                    -> where( 'customer_type', Customer::TYPE_COMPANY )
                    -> where( 'id', $id )
                    -> first();

                if( empty( $re )) S::error('40006');
                if( empty( $re-> certificate_type ) || empty( $re-> certificate_code ))
                {
                    if( !empty( $data['certificate_type'] ))
                    {
                        switch ( $data['certificate_type'] )
                        {
                            case '身份证':
                                $re-> certificate_type = Customer::CERTIFICATE_TYPE_ID_CARD;
                                break;
                            case '护照':
                                $re-> certificate_type = Customer::CERTIFICATE_TYPE_PASSPORT;
                                break;
                            case '营业执照':
                                $re-> certificate_type = Customer::CERTIFICATE_TYPE_BUSINESS_LICENSE;
                                break;
                            case '机构代码证':
                                $re-> certificate_type = Customer::CERTIFICATE_TYPE_ORGANIZATION_CODE;
                                break;
                            case '其他':
                                $re-> certificate_type = Customer::CERTIFICATE_TYPE_OTHER;
                                break;
                        }

                        empty( $data['certificate_other_type'] ) || $re-> certificate_other_type = $data['certificate_other_type'];
                        empty( $data['certificate_code'] )       || $re-> certificate_code = $data['certificate_code'];

                        try
                        {
                            $re-> save();
                        }
                        catch ( \Exception $e )
                        {
                            S::error('50001');
                        }
                    }
                }
                break;
        }
        if( empty( $re )) S::error('20001');
        return $re-> toArray();
    }

    /**
     * 验证该类型下 优惠券是否存在仓库中
     * @param $product
     * @return array
     */
    private static function verifyCoupon( $product )
    {
        $flowNo = $product['product'];
        $num    = 0;
        //计算卡券总数量
        foreach ( $flowNo as $v )
        {
            if( $v['start'] > $v['end'] )S::error('40004');
            $num += (int)$v['end'] - (int)$v['start'] + 1;
        }

        $re = CouponInfo::where( 'coupon_type_id', $product['id'] )
            -> where( 'node_id', session( 'user.node_id' ))
            -> where( 'storage_id', session( 'user.storage_id' ))
            -> where( 'status', CouponInfo::STATUS_INVENTORY )
            -> where( function ( $query ) use( $flowNo ){
                foreach ( $flowNo as $v )
                {
                    $query-> orWhereBetween( 'coupon_flow_no', [ $v['start'], $v['end'] ]);
                }
            })
            -> select( 'id', 'coupon_flow_no', 'coupon_price' )
            -> get();
        if( $num !== $re-> count() || $re-> count() == 0 ) S::error('40002');
        return  $re-> toArray();
    }

    /**
     * 验证券是否存在仓库并获取券详情
     * @param $id
     * @return mixed
     */
    private static function getCouponTypeInfo( $id )
    {
        $re = CouponType::join( 'coupon_storage_stock as b', 'coupon_type.id', '=', 'b.coupon_type_id' )
            -> where( 'coupon_type.id', $id )
            -> where( 'b.storage_id', session( 'user.storage_id' ))
            -> where( 'b.node_id', session( 'user.node_id' ))
            -> select( 'coupon_type.id', 'coupon_type.name', 'coupon_type.detail_name', 'coupon_type.class_id', 'coupon_type.class_name',
                'coupon_type.price', 'b.amount_no_sale' )
            -> first();
        if( empty( $re )) S::error('40002');
        return $re-> toArray();
    }
}