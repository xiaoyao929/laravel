<?php

namespace App\Http\Controllers\Sale;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\{Input,Validator,DB};
use Lib\PublicClass\{S,Log};

use App\Dao\{OrderDao,CustomerTypeDao,StorageDao,SupportDao,SystemStatusDao,OrderBatchDao};
use App\Model\{CouponType,CouponSale,CouponStorageStock,CouponSaleInfo,CouponInfo};
use Excel;

/**
 * 销售
 */
class SaleController extends Controller
{
    public function apply()
    {
        $type = CouponType::join( 'coupon_storage_stock as b', 'coupon_type.id', '=', 'b.coupon_type_id' )
            -> where( 'b.amount_no_sale', '>', 0 )
            -> where( 'coupon_type.node_id', session( 'user.node_id' ))
            -> where( 'storage_id', session( 'user.storage_id' ) )
            -> orderBy( 'coupon_type.id', 'desc' )
            -> select( 'coupon_type.id', 'coupon_type.name', 'coupon_type.detail_name', 'coupon_type.class_id',
                'coupon_type.class_name', 'coupon_type.price', 'coupon_type.custom_no' )
            -> get();
        foreach ( $type as $k=> $v )
        {
            $type[$k]-> price = $v-> price / 100;
        }
        return view('sale.sale_apply', [
            'type'=> $type-> toJson()
        ]);
    }
    public function applySave()
    {
        $json = file_get_contents("php://input");
        if( is_array( $json ) || is_object( $json ))
        {
            Log::log_write( '销售申请提交数据:'.json_encode( $json, JSON_UNESCAPED_UNICODE ), '', 'applySave' );
        }
        else
        {
            Log::log_write( '销售申请提交数据:'.$json, '', 'applySave' );
        }

        if( empty( $json )) S::error('40001');
        $data = json_decode( $json, true );
        if( empty( $data ) || empty( $data['customer_type'] ) || empty( $data['customer_id'] || empty( $data['product'] ))) S::error('40001');
        if( empty( $data['collection_price'] )) S::error('40007');

        switch ( $data['customer_type'] )
        {
            case 'internal':
                if( empty( $data['is_pay'] ) || empty( $data['recipients'] ))S::error('40001');
                return OrderDao::createInternalOrder( $data );
                break;
            case 'client':
                if( empty( $data['pay_type'] ))S::error('40001');
                return OrderDao::createClientOrder( $data );
                break;
            case 'company':
                if( empty( $data['pay_type'] ))S::error('40001');
                return OrderDao::createCompanyOrder( $data );
                break;
        }
    }

    public function applyBatchSave()
    {
        set_time_limit(0);
        if( !Input::hasFile('file2')) abort( 403, '缺少上传文件' );
        if( !Input::file('file2')-> isValid()) abort( 403, '文件上传失败' );

        $path = base_path('upload/temp');
        if( !is_dir( $path ))
        {
            if( !mkdir( $path )) abort( 403, '文件夹创建失败，缺少权限！' );
        }
        $file_name = $_FILES['file2']['name'];
        $extension = substr( $file_name, strrpos( $file_name, '.' )+1 );
        if( $extension != 'xls' ) abort( 403, '上传文件类型不符' );
        Input::file('file2')-> move( $path, 'temp.xls' );

        $res = [];
        Excel::load( $path.'/'.'temp.xls' , function( $reader ) use( &$res )
        {
            $reader = $reader-> getSheet(0);
            $res    = $reader-> toArray();
        });

        if( count( $res ) > 501 ) abort( 403, '上传文件超过最大行数限制' );

        //整理数据并检查用户信息是否正确
        if( !OrderBatchDao::formatData( $res )) abort( 403, OrderBatchDao::getError());
        //获取券类型信息并检查券信息是否存在
        if( !OrderBatchDao::getCouponsType()) abort( 403, OrderBatchDao::getError());
        //获取券详细信息并检查券是否正确
        if( !OrderBatchDao::getCouponsInfo()) abort( 403, OrderBatchDao::getError());
        //创建订单
        if( !OrderBatchDao::createOrders()) abort( 403, OrderBatchDao::getError());
        return view('sale.sale_batch_succe');
    }

    public function applyBatchTemp()
    {
        $data = [
            [ '开始券号', '结束券号', '券简称', '实收金额(元)', '客户类型', '部门编号/客户名称/联络人名称', '费用承担部门/个人手机号/单位电话', '领用人', '支付方式', '备注'],
            [ '2000000756', '2000000759', '汉堡券', '123.12', '内部部门',  '9002010', '人事部（如未填写此栏，则视为领用部门承担费用）', '王五', '无需填写', '此列为内部部门销售订单示例数据'],
            [ '2000000762', '2000000765', '汉堡券'],
            [ '2000000767', '2000000768', '汉堡券'],
            [ '2000000769', '2000000769', '汉堡券'],
            [ '2000001361', '2000001365', '可乐券'],
            [ '2000001370', '2000001375', '可乐券'],
            [ '2000000756', '2000000759', '汉堡券', '123.12', '个人',  '张三', '15900000000', '无需填写', '现金', '此列为个人销售订单示例数据'],
            [ '2000000762', '2000000765', '汉堡券'],
            [ '2000000767', '2000000768', '汉堡券'],
            [ '2000000769', '2000000769', '汉堡券'],
            [ '2000001361', '2000001365', '可乐券'],
            [ '2000001370', '2000001375', '可乐券'],
            [ '2000000756', '2000000759', '汉堡券', '123.12', '单位',  '李四', '021-12345678', '无需填写', '钞票', '此列为单位销售订单示例数据'],
            [ '2000000762', '2000000765', '汉堡券'],
            [ '2000000767', '2000000768', '汉堡券'],
            [ '2000000769', '2000000769', '汉堡券'],
            [ '2000001361', '2000001365', '可乐券'],
            [ '2000001370', '2000001375', '可乐券'],
        ];
        $filename = 'temp';

        Excel::create( $filename, function( $excel ) use (  $data )
        {
            $excel-> sheet('score', function( $sheet ) use (  $data )
            {
                $sheet-> rows( $data );
            });
        })-> export('xls');
    }

    public function auditList()
    {
        S::setUrlParam();
        $search        = Input::all();
        $userStorageId = session( 'user.storage_id' );
        $couponSale    = CouponSale::join( 'storage as b', 'coupon_sale.storage_id', '=', 'b.id' )
            -> whereRaw( "FIND_IN_SET( {$userStorageId}, lv_b.`full_id` )" )
            -> where( 'coupon_sale.status', CouponSale::STATUS_PENDING )
            -> where( 'coupon_sale.node_id', session( 'user.node_id' ))
            -> select( 'coupon_sale.*' )
            -> orderBy( 'seq', 'desc' );

        empty( $search['type'] )      || $couponSale-> where('coupon_sale.customer_type', $search['type']);
        empty( $search['name'] )      || $couponSale-> where(function ( $query ) use( $search ) {
                                            $query-> orWhere( 'coupon_sale.customer_name', 'like', "%{$search['name']}%" )
                                                -> orWhere( 'coupon_sale.sector_id', 'like', "%{$search['name']}%" );
                                        });
        empty( $search['from'] )      || $couponSale-> where('coupon_sale.request_time', '>=', $search['from']);
        empty( $search['to'] )        || $couponSale-> where('coupon_sale.request_time', '<=', $search['to']);
        empty( $search['storage_id'] )|| $couponSale-> where('coupon_sale.storage_id', $search['storage_id']);
        $list = $couponSale-> paginate(15);
        return view('sale.sale_audit', [
            'list'    => $list,
            'type'    => CustomerTypeDao::getInfo(),
            'storages'=> json_encode( StorageDao::getStorages(), JSON_UNESCAPED_UNICODE ),
            'search'  => $search
        ]);
    }
    public function auditShow()
    {
        $id = Input::get('id');
        if( empty( $id )) abort( 403, '缺少ID' );
        $user   = session('user');
        $re = CouponSale::join( 'storage as b', 'coupon_sale.storage_id', '=', 'b.id' )
            -> join( 'coupon_sale_info as c', 'coupon_sale.id', '=', 'c.id' )
            -> whereRaw( "FIND_IN_SET( {$user['storage_id']}, lv_b.`full_id` )" )
            -> where( 'coupon_sale.status', CouponSale::STATUS_PENDING )
            -> where( 'coupon_sale.node_id', $user['node_id'] )
            -> where( 'coupon_sale.id', $id )
            -> select( 'coupon_sale.*', 'c.coupon_class_id', 'c.coupon_class_name', 'c.coupon_type_id', 'c.coupon_type_name',
                'c.start_flow_no', 'c.end_flow_no', 'c.amount as info_amount', 'c.price as info_price', 'c.discount' )
            -> orderBy( 'c.coupon_type_id' )
            -> orderBy( 'c.start_flow_no' )
            -> get()
            -> toArray();
        if( count( $re ) < 1 ) abort( 403, 'ID不存在' );

        foreach ( $re as $v )
        {
            if( !isset( $coupon ))
            {
                $coupon = [
                    'id'               => $v['id'],
                    'seq'              => $v['seq'],
                    'customer_type'    => $v['customer_type'],
                    'customer_id'      => $v['customer_id'],
                    'customer_info'    => json_decode( $v['customer_info'], true ),
                    'pay_type'         => $v['pay_type'],
                    'pay_text'         => $v['pay_text'],
                    'memo'             => $v['memo'],
                    'sector_id'        => $v['sector_id'],
                    'storage_id'       => $v['storage_id'],
                    'storage_name'     => $v['storage_name'],
                    'bog_amount'       => $v['bog_amount'],
                    'gc_amount'        => $v['gc_amount'],
                    'amount'           => $v['amount'],
                    'price'            => $v['price'],
                    'sale_price'       => $v['sale_price'],
                    'request_user_id'  => $v['request_user_id'],
                    'request_user_name'=> $v['request_user_name'],
                    'request_time'     => $v['request_time'],
                ];
            }
            if( !isset( $coupon['coupons'][$v['coupon_type_id']] ) )
            {
                $coupon['coupons'][$v['coupon_type_id']] = [
                    'coupon_class_id'  => $v['coupon_class_id'],
                    'coupon_class_name'=> $v['coupon_class_name'],
                    'coupon_type_id'   => $v['coupon_type_id'],
                    'coupon_type_name' => $v['coupon_type_name'],
                    'price'            => $v['info_price'],
                    'discount'         => $v['discount'],
                ];
            }

            $coupon['coupons'][$v['coupon_type_id']]['flow_no'][] = [
                'start' => $v['start_flow_no'],
                'end'   => $v['end_flow_no'],
                'amount'=> $v['info_amount'],
            ];
        }

        return view('sale.sale_audit_show', ['coupon'=> $coupon, 'urlParam'=> S::getUrlParam()]);
    }
    public function auditSave()
    {
        $post = Input::all();
        $validator = Validator::make( $post, [
            'id'   => 'required'
        ], [
            'id.required'   => '最少选择一条记录'
        ]);
        if( $validator-> fails() )
        {
            return redirect('/sale/audit/list'.S::getUrlParam())-> withErrors( $validator );
        }
        $user = session('user');
        $data = CouponSale::join( 'storage as b', 'coupon_sale.storage_id', '=', 'b.id' )
            -> whereRaw( "FIND_IN_SET( {$user['storage_id']}, lv_b.`full_id` )" )
            -> where( 'coupon_sale.status', CouponSale::STATUS_PENDING )
            -> where( 'coupon_sale.node_id', $user['node_id'] )
            -> whereIn( 'coupon_sale.id', $post['id'] )
            -> select( 'coupon_sale.*' )
            -> get()
            -> toArray();
        if( empty( $data )) abort( 403, 'ID不存在' );
        if( count( $post['id'] ) != count( $data ))
        {
            return redirect('/sale/audit/list'.S::getUrlParam())-> with( promptMsg( '提交的单号中存在错误数据！', 3 ));
        }
        DB::beginTransaction();
        if( $post['action'] == 'pass' )//审核通过
        {
            $coupons = [];
            $re = CouponSaleInfo::whereIn( 'id', $post['id'] )
                -> where( 'node_id', $user['node_id'] )
                -> orderBy( 'seq', 'desc' )
                -> orderBy( 'coupon_type_id' )
                -> orderBy( 'start_flow_no' )
                -> get()
                -> toArray();

            $i   = 0;
            $seq = '';
            //整理数据
            foreach ( $re as $k=> $value )
            {
                if( $k == 0 )
                {
                    $seq = $value['seq'];
                    $coupons[$i] = [
                        'id'        => $value['id'],
                        'seq'       => $value['seq'],
                        'storage_id'=> $value['storage_id']
                    ];
                    $key = $i;
                    $i++;
                }
                else
                {
                    if( $value['seq'] != $seq )
                    {
                        $seq = $value['seq'];
                        $coupons[$i] = [
                            'id'        => $value['id'],
                            'seq'       => $value['seq'],
                            'storage_id'=> $value['storage_id']
                        ];
                        $key = $i;
                        $i++;
                    }
                }
                if( !isset( $coupons[$key]['type'][$value['coupon_type_id']] ))
                {
                    $coupons[$key]['type'][$value['coupon_type_id']]['amount'] = 0;
                }
                $amount = (int)$value['end_flow_no'] - (int)$value['start_flow_no'] + 1;
                $coupons[$key]['type'][$value['coupon_type_id']]['amount'] += $amount;

                $coupons[$key]['type'][$value['coupon_type_id']]['flow_no'][] = [
                    'start_flow_no'=> $value['start_flow_no'],
                    'end_flow_no'  => $value['end_flow_no'],
                    'amount'       => $amount,
                ];
                unset($amount);
            }

            foreach ( $coupons as $coupon )
            {
                $flow_no = [];
                $item = [
                    'node_id'=>  $user['node_id']
                ];

                foreach ( $coupon['type'] as $typeId=> $type )
                {
                    foreach ( $type['flow_no'] as $v )
                    {
                        $item['item'][] = [
                            'start_flow_no'=> $v['start_flow_no'],
                            'amount'       => $v['amount'],
                        ];
                        $flow_no[] = [
                            'start'=> $v['start_flow_no'],
                            'end'  => $v['end_flow_no'],
                        ];
                    }
                }
                $result = SupportDao::activate( $item );

                if( !isset($result['Status']['StatusCode']) || $result['Status']['StatusCode'] != '0000')
                {
                    return redirect('/sale/audit/list'.S::getUrlParam())-> with( promptMsg( "推送流水号{$coupon['seq']}时出现错误，错误原因：".$result['Status']['StatusText'], 4 ));
                }

                try
                {
                    //更改销售订单状态为审核通过
                    CouponSale::where( 'id', $coupon['id'] )-> update([
                        'status'           => CouponSale::STATUS_TRANSIT,
                        'approve_user_id'  => $user['id'],
                        'approve_user_name'=> $user['nickname'],
                        'approve_time'     => date('Y-m-d H:i:s')
                    ]);
                    //变更库存数量

                    foreach ( $coupon['type'] as $typeId=> $type )
                    {
                        CouponStorageStock::where( 'node_id', $user['node_id'] )
                            -> where( 'storage_id', $coupon['storage_id'] )
                            -> where( 'coupon_type_id', $typeId )
                            -> update([
                                'amount_audit'=> DB::raw( "amount_audit - {$type['amount']}" ),
                                'amount_saled'=> DB::raw( "amount_saled + {$type['amount']}" )
                            ]);
                    }

                    CouponInfo::where( 'node_id', $user['node_id'] )
                        -> where( function ( $query ) use( $flow_no ){
                            foreach ( $flow_no as $v )
                            {
                                $query-> orWhereBetween( 'coupon_flow_no', [ $v['start'], $v['end'] ]);
                            }
                        })
                        -> update([ 'status'=> CouponInfo::STATUS_SALES, 'activate'=> CouponInfo::ACTIVATE_ON ]);

                    DB::commit();
                }
                catch ( \Exception $e )
                {
                    DB::rollBack();
                    return redirect('/sale/audit/list'.S::getUrlParam())-> with( promptMsg( "保存流水号{$coupon['seq']}数据时出错", 4 ));
                }

                unset($item);
                unset($flow_no);
            }
            return redirect('/sale/audit/list'.S::getUrlParam())-> with( promptMsg( '提交成功', 1 ));
        }
        elseif ( $post['action'] == 'no_pass' )//审核未通过
        {
            $coupons = [];
            $flow_no = [];
            //获取所有订单下的券号段
            $re = CouponSaleInfo::whereIn( 'id', $post['id'] )
                -> where( 'node_id', $user['node_id'] )
                -> orderBy( 'coupon_type_id' )
                -> orderBy( 'start_flow_no' )
                -> get()
                -> toArray();

            foreach ( $re as $v )
            {
                $amount = (int)$v['end_flow_no'] - (int)$v['start_flow_no'] + 1;
                if( !isset( $coupons[$v['coupon_type_id']] ))
                {
                    $coupons[$v['coupon_type_id']] = [
                        'storage_id'    => $v['storage_id'],
                        'coupon_type_id'=> $v['coupon_type_id'],
                        'amount'        => $amount,
                    ];
                }
                else
                {
                    $coupons[$v['coupon_type_id']]['amount'] += $amount;
                }
                $flow_no[] = [
                    'start' => $v['start_flow_no'],
                    'end'   => $v['end_flow_no'],
                ];
            }

            try
            {
                //更改销售订单状态为审核通过
                CouponSale::whereIn( 'id', $post['id'] )-> update([
                    'status'           => CouponSale::STATUS_NO_THROUGH,
                    'approve_user_id'  => $user['id'],
                    'approve_user_name'=> $user['nickname'],
                    'approve_time'     => date('Y-m-d H:i:s')
                ]);

                foreach ( $coupons as $v )
                {
                    //变更库存数量
                    CouponStorageStock::where( 'node_id', $user['node_id'] )
                        -> where( 'storage_id', $v['storage_id'] )
                        -> where( 'coupon_type_id', $v['coupon_type_id'] )
                        -> update([
                            'amount_audit'  => DB::raw( "amount_audit - {$v['amount']}" ),
                            'amount_no_sale'=> DB::raw( "amount_no_sale + {$v['amount']}" )
                        ]);
                }
                CouponInfo::where( 'node_id', $user['node_id'] )
                    -> where( function ( $query ) use( $flow_no ){
                        foreach ( $flow_no as $v )
                        {
                            $query-> orWhereBetween( 'coupon_flow_no', [ $v['start'], $v['end'] ]);
                        }
                    })
                    -> update([ 'status'=> CouponInfo::STATUS_INVENTORY ]);

                DB::commit();
                return redirect('/sale/audit/list'.S::getUrlParam())-> with( promptMsg( '提交成功', 1 ));
            }
            catch ( \Exception $e )
            {
                DB::rollBack();
                return redirect('/sale/audit/list'.S::getUrlParam())-> with( promptMsg( '数据保存失败', 4 ));
            }
        }
    }
    public function searchList()
    {
        S::setUrlParam();
        $search = Input::all();
        if( !isset( $search['status'] )) $search['status'] = CouponSale::STATUS_TRANSIT;
        $user   = session( 'user' );
        $couponSale    = CouponSale::join( 'storage as b', 'coupon_sale.storage_id', '=', 'b.id' )
            -> whereRaw( "FIND_IN_SET( {$user['storage_id']}, lv_b.`full_id` )" )
            -> where( 'coupon_sale.node_id', $user['node_id'] )
            -> select( 'coupon_sale.*' )
            -> orderBy( 'seq', 'desc' );

        empty( $search['type'] )      || $couponSale-> where('coupon_sale.customer_type', $search['type']);
        empty( $search['name'] )      || $couponSale-> where(function ( $query ) use( $search ) {
            $query-> orWhere( 'coupon_sale.customer_name', 'like', "%{$search['name']}%" )
                -> orWhere( 'coupon_sale.sector_id', 'like', "%{$search['name']}%" );
        });
        empty( $search['from'] )      || $couponSale-> where('coupon_sale.request_time', '>=', $search['from']);
        empty( $search['to'] )        || $couponSale-> where('coupon_sale.request_time', '<=', $search['to']);
        empty( $search['storage_id'] )|| $couponSale-> where('coupon_sale.storage_id', $search['storage_id']);
        empty( $search['status'] )    || $couponSale-> where('coupon_sale.status', $search['status']);
        $list = $couponSale-> paginate(15);

        return view('sale.sale_list', [
            'list'    => $list,
            'status'  => SystemStatusDao::getCouponSale(),
            'type'    => CustomerTypeDao::getInfo(),
            'storages'=> json_encode( StorageDao::getStorages(), JSON_UNESCAPED_UNICODE ),
            'search'  => $search
        ]);
    }
    public function searchShow()
    {
        $id = Input::get('id');
        if( empty( $id )) abort( 403, '缺少ID' );
        $user   = session('user');
        $re = CouponSale::join( 'storage as b', 'coupon_sale.storage_id', '=', 'b.id' )
            -> join( 'coupon_sale_info as c', 'coupon_sale.id', '=', 'c.id' )
            -> whereRaw( "FIND_IN_SET( {$user['storage_id']}, lv_b.`full_id` )" )
            -> where( 'coupon_sale.node_id', $user['node_id'] )
            -> where( 'coupon_sale.id', $id )
            -> select( 'coupon_sale.*', 'c.coupon_class_id', 'c.coupon_class_name', 'c.coupon_type_id', 'c.coupon_type_name',
                'c.start_flow_no', 'c.end_flow_no', 'c.amount as info_amount', 'c.price as info_price', 'c.discount' )
            -> orderBy( 'c.coupon_type_id' )
            -> orderBy( 'c.start_flow_no' )
            -> get()
            -> toArray();
        if( count( $re ) < 1 ) abort( 403, 'ID不存在' );

        foreach ( $re as $v )
        {
            if( !isset( $coupon ))
            {
                $coupon = [
                    'id'               => $v['id'],
                    'seq'              => $v['seq'],
                    'customer_type'    => $v['customer_type'],
                    'customer_id'      => $v['customer_id'],
                    'customer_info'    => json_decode( $v['customer_info'], true ),
                    'pay_type'         => $v['pay_type'],
                    'pay_text'         => $v['pay_text'],
                    'memo'             => $v['memo'],
                    'sector_id'        => $v['sector_id'],
                    'storage_id'       => $v['storage_id'],
                    'storage_name'     => $v['storage_name'],
                    'bog_amount'       => $v['bog_amount'],
                    'gc_amount'        => $v['gc_amount'],
                    'amount'           => $v['amount'],
                    'price'            => $v['price'],
                    'status'           => $v['status'],
                    'sale_price'       => $v['sale_price'],
                    'request_user_id'  => $v['request_user_id'],
                    'request_user_name'=> $v['request_user_name'],
                    'request_time'     => $v['request_time'],
                ];
            }
            if( !isset( $coupon['coupons'][$v['coupon_type_id']] ) )
            {
                $coupon['coupons'][$v['coupon_type_id']] = [
                    'coupon_class_id'  => $v['coupon_class_id'],
                    'coupon_class_name'=> $v['coupon_class_name'],
                    'coupon_type_id'   => $v['coupon_type_id'],
                    'coupon_type_name' => $v['coupon_type_name'],
                    'price'            => $v['info_price'],
                    'discount'         => $v['discount'],
                ];
            }

            $coupon['coupons'][$v['coupon_type_id']]['flow_no'][] = [
                'start' => $v['start_flow_no'],
                'end'   => $v['end_flow_no'],
                'amount'=> $v['info_amount'],
            ];
        }

        return view('sale.sale_list_show', [
            'coupon'  => $coupon,
            'status'  => SystemStatusDao::getCouponSale(),
            'urlParam'=> S::getUrlParam()
        ]);
    }
}