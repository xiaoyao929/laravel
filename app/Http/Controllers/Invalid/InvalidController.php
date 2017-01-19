<?php

namespace App\Http\Controllers\Invalid;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\{Input,Validator,DB};
use Lib\PublicClass\{S};

use App\Dao\{StorageDao,CouponClassDao,CouponTypeDao,SystemStatusDao,SupportDao};
use App\Model\{CouponInfo,CouponStorageStock,CouponInvalid};

/**
 * 券作废
 */
class InvalidController extends Controller
{
    public function apply()
    {
        return view('invalid.invalid_apply', [ 'type'=> CouponTypeDao::getCouponType(), 'coupon'=> [] ]);
    }
    public function applySave()
    {
        $coupon    = Input::all();
        $validator = Validator::make( $coupon, [
            'type'  => 'required',
            'start' => ['required','numeric'],
            'end'   => ['required','numeric'],
            'reason'=> 'required',
        ], [
            'start.required' => '开始券号必须写',
            'type.required'  => '券类型必须写',
            'end.required'   => '结束券号必须写',
            'reason.required'=> '作废原因必须写',
            'start.numeric'  => '开始券号必须数字',
            'end.numeric'    => '结束券号必须数字',
        ]);

        $param = [
            'coupon'  => $coupon,
            'type'=> CouponTypeDao::getCouponType(),
        ];
        if( $validator-> fails() )
        {
            return view( 'invalid.invalid_apply', $param )-> withErrors( $validator );
        }

        if( $coupon['reason'] == '其他' && empty( $coupon['text'] ))
        {
            $validator-> errors()-> add('error', '原因为其他时，必须填写！');
            return view( 'invalid.invalid_apply', $param )-> withErrors( $validator );
        }

        $num = (int)$coupon['end'] - (int)$coupon['start'];
        if( $num < 0 ) abort( 403, '结束券号小于起始券号' );
        $num += 1; //通过券号计算的数量

        $user = session( 'user' );
        $coupons = CouponInfo::where( 'storage_id', $user['storage_id'] )
            -> where( 'node_id', $user['node_id'] )
            -> where( 'coupon_type_id', $coupon['type'] )
            -> where( 'status', CouponInfo::STATUS_INVENTORY )
            -> whereBetween( 'coupon_flow_no', [ $coupon['start'], $coupon['end'] ])
            -> get()
            -> toArray();

        if( $num != count($coupons) )
        {
            $validator-> errors()-> add( 'error', '输入的券号有错误，请检查！' );
            return view( 'invalid.invalid_apply', $param )-> withErrors( $validator );
        }
        DB::beginTransaction();

        $insert = [
            'node_id'          => $user['node_id'],
            'seq'              => getOperateSeq('作废'),
            'storage_id'       => $coupons[0]['storage_id'],
            'storage_name'     => $coupons[0]['storage_name'],
            'coupon_class_id'  => $coupons[0]['coupon_class_id'],
            'coupon_class_name'=> $coupons[0]['coupon_class_name'],
            'coupon_type_id'   => $coupons[0]['coupon_type_id'],
            'coupon_type_name' => $coupons[0]['coupon_type_name'],
            'start_flow_no'    => $coupon['start'],
            'end_flow_no'      => $coupon['end'],
            'amount'           => $num,
            'reason'           => $coupon['reason'],
            'text'             => $coupon['text'],
            'memo'             => $coupon['memo'],
            'status'           => CouponInvalid::STATUS_PENDING,
            'request_user_id'  => $user['id'],
            'request_user_name'=> $user['nickname'],
            'request_time'     => date('Y-m-d H:i:s'),
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s')
        ];

        try
        {
            CouponInvalid::insert( $insert );
            CouponStorageStock::where( 'node_id', $user['node_id'] )
                -> where( 'coupon_type_id', $coupon['type'] )
                -> where( 'storage_id', $insert['storage_id'] )
                -> update([
                    'amount_no_sale'=> DB::raw( "amount_no_sale - {$num}" ),
                    'amount_audit'  => DB::raw( "amount_audit + {$num}" )
                ]);
            CouponInfo::where( 'storage_id', $user['storage_id'] )
                -> where( 'node_id', $user['node_id'] )
                -> where( 'coupon_type_id', $coupon['type'] )
                -> whereBetween( 'coupon_flow_no', [ $coupon['start'], $coupon['end'] ])
                -> update([ 'status'=> CouponInfo::STATUS_PENDING ]);
            DB::commit();
            return redirect('/invalid/apply')-> with( promptMsg( '提交成功', 1 ));
        }
        catch ( \Exception $e )
        {
            DB::rollBack();
            $validator-> errors()-> add('error', $e-> getTraceAsString());
            return view( 'invalid.invalid_apply', $param )-> withErrors( $validator );
        }
    }
    public function auditList()
    {
        S::setUrlParam();
        $search        = Input::all();
        $userStorageId = session( 'user.storage_id' );
        $couponInvalid = CouponInvalid::join( 'storage as b', 'coupon_invalid.storage_id', '=', 'b.id' )
            -> whereRaw( "FIND_IN_SET( {$userStorageId}, lv_b.`full_id` )" )
            -> where( 'coupon_invalid.status', CouponInvalid::STATUS_PENDING )
            -> where( 'coupon_invalid.node_id', session( 'user.node_id' ))
            -> orderBy( 'seq', 'desc' )
            -> select( 'coupon_invalid.*' );

        empty( $search['class'] )     || $couponInvalid-> where('coupon_invalid.coupon_class_id', $search['class']);
        empty( $search['name'] )      || $couponInvalid-> where('coupon_invalid.coupon_type_id', $search['name']);
        empty( $search['from'] )      || $couponInvalid-> where('coupon_invalid.request_time', '>=', $search['from']);
        empty( $search['to'] )        || $couponInvalid-> where('coupon_invalid.request_time', '<=', $search['to']);
        empty( $search['storage_id'] )|| $couponInvalid-> where('coupon_invalid.storage_id', $search['storage_id']);
        $list = $couponInvalid-> paginate(15);
        return view('invalid.invalid_audit', [
            'list'    => $list,
            'class'   => CouponClassDao::getCouponClass(),
            'type'    => CouponTypeDao::getCouponType(),
            'storages'=> json_encode( StorageDao::getStorages(), JSON_UNESCAPED_UNICODE ),
            'search'  => $search
        ]);
    }
    public function auditShow()
    {
        $id = Input::get('id');
        if( empty( $id )) abort( 403, '缺少ID' );
        $userStorageId = session( 'user.storage_id' );
        $re = CouponInvalid::join( 'storage as b', 'coupon_invalid.storage_id', '=', 'b.id' )
            -> whereRaw( "FIND_IN_SET( {$userStorageId}, lv_b.`full_id` )" )
            -> where( 'coupon_invalid.node_id', session( 'user.node_id' ))
            -> where( 'coupon_invalid.status', CouponInvalid::STATUS_PENDING )
            -> where( 'coupon_invalid.id', $id )
            -> select( 'coupon_invalid.*' )
            -> first();
        if( empty( $re )) abort( 403, 'ID不存在' );

        return view('invalid.invalid_audit_show', ['coupon'=> $re-> toArray(), 'urlParam'=> S::getUrlParam()]);
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
            return redirect('/invalid/audit'.S::getUrlParam())-> withErrors( $validator );
        }
        $user = session('user');
        $data = CouponInvalid::join( 'storage as b', 'coupon_invalid.storage_id', '=', 'b.id' )
            -> whereRaw( "FIND_IN_SET( {$user['storage_id']}, lv_b.`full_id` )" )
            -> where( 'coupon_invalid.status', CouponInvalid::STATUS_PENDING )
            -> where( 'coupon_invalid.node_id', $user['node_id'] )
            -> whereIn( 'coupon_invalid.id', $post['id'] )
            -> select( 'coupon_invalid.*' )
            -> get()
            -> toArray();
        if( empty( $data )) abort( 403, 'ID不存在' );
        if( count( $post['id'] ) != count( $data ))
        {
            return redirect('/invalid/audit'.S::getUrlParam())-> with( promptMsg( '提交的单号中存在错误数据！', 3 ));
        }

        DB::beginTransaction();

        if( $post['action'] == 'pass' )//审核通过
        {
            $item = [
                'node_id'=> $user['node_id'],
            ];
            //此处通知支撑
            foreach ( $data as $v )
            {
                $amount = (int)$v['end_flow_no'] - (int)$v['start_flow_no'] + 1;
                $item['item'][] = [
                    'start_flow_no'=> $v['start_flow_no'],
                    'amount'       => $amount,
                ];
            }
            $result = SupportDao::voidCoupon( $item );
            if( !isset($result['Status']['StatusCode']) || $result['Status']['StatusCode'] != '0000')
            {
                return redirect('/invalid/audit'.S::getUrlParam())-> with( promptMsg( '失败原因:'.$result['Status']['StatusText'], 4 ));
            }

            try
            {
                CouponInvalid::whereIn( 'id', $post['id'] )-> update([
                    'status'           => CouponInvalid::STATUS_TRANSIT,
                    'approve_user_id'  => $user['id'],
                    'approve_user_name'=> $user['nickname'],
                    'approve_time'     => date('Y-m-d H:i:s')
                ]);
                foreach ( $data as $v )
                {
                    CouponStorageStock::where( 'node_id', $user['node_id'] )
                        -> where( 'storage_id', $v['storage_id'] )
                        -> where( 'coupon_type_id', $v['coupon_type_id'] )
                        -> update([
                            'amount_audit'    => DB::raw( "amount_audit - {$v['amount']}" ),
                            'amount_destroyed'=> DB::raw( "amount_destroyed + {$v['amount']}" )
                        ]);
                    //拼装coupon更新条件
                    if( !isset( $couponInfo ))
                        $couponInfo = CouponInfo::orWhereBetween( 'coupon_flow_no', [ $v['start_flow_no'], $v['end_flow_no'] ]);
                    else
                        $couponInfo-> orWhereBetween( 'coupon_flow_no', [ $v['start_flow_no'], $v['end_flow_no'] ]);
                }
                $couponInfo-> update([ 'status'=> CouponInfo::STATUS_INVALID ]);

                DB::commit();
                return redirect('/invalid/audit'.S::getUrlParam())-> with( promptMsg( '提交成功', 1 ));
            }
            catch ( \Exception $e )
            {
                DB::rollBack();
                return redirect('/invalid/audit'.S::getUrlParam())-> with( promptMsg( '数据保存失败', 4 ));
            }
        }
        elseif ( $post['action'] == 'no_pass' )//审核未通过
        {
            try
            {
                CouponInvalid::whereIn( 'id', $post['id'] )-> update([
                    'status'           => CouponInvalid::STATUS_NO_THROUGH,
                    'approve_user_id'  => $user['id'],
                    'approve_user_name'=> $user['nickname'],
                    'approve_time'     => date('Y-m-d H:i:s')
                ]);

                foreach ( $data as $v )
                {
                    CouponStorageStock::where( 'node_id', $user['node_id'] )
                        -> where( 'storage_id', $v['storage_id'] )
                        -> where( 'coupon_type_id', $v['coupon_type_id'] )
                        -> update([
                            'amount_audit'  => DB::raw( "amount_audit - {$v['amount']}" ),
                            'amount_no_sale'=> DB::raw( "amount_no_sale + {$v['amount']}" )
                        ]);
                    //拼装coupon更新条件
                    if( !isset( $couponInfo ))
                        $couponInfo = CouponInfo::orWhereBetween( 'coupon_flow_no', [ $v['start_flow_no'], $v['end_flow_no'] ]);
                    else
                        $couponInfo-> orWhereBetween( 'coupon_flow_no', [ $v['start_flow_no'], $v['end_flow_no'] ]);
                }
                $couponInfo-> update([ 'status'=> CouponInfo::STATUS_INVENTORY ]);
                DB::commit();
                return redirect('/invalid/audit'.S::getUrlParam())-> with( promptMsg( '提交成功', 1 ));
            }
            catch ( \Exception $e )
            {
                DB::rollBack();
                return redirect('/invalid/audit'.S::getUrlParam())-> with( promptMsg( '数据保存失败', 4 ));
            }
        }
    }
    public function searchList()
    {
        S::setUrlParam();
        $search        = Input::all();
        $userStorageId = session( 'user.storage_id' );
        if( !isset( $search['status'] )) $search['status'] = CouponInvalid::STATUS_TRANSIT;
        $couponInvalid = CouponInvalid::join( 'storage as b', 'coupon_invalid.storage_id', '=', 'b.id' )
            -> whereRaw( "FIND_IN_SET( {$userStorageId}, lv_b.`full_id` )" )
            -> where( 'coupon_invalid.node_id', session( 'user.node_id' ))
            -> select( 'coupon_invalid.*' )
            -> orderBy( 'seq', 'desc' );

        empty( $search['class'] )     || $couponInvalid-> where('coupon_invalid.coupon_class_id', $search['class']);
        empty( $search['name'] )      || $couponInvalid-> where('coupon_invalid.coupon_type_id', $search['name']);
        empty( $search['from'] )      || $couponInvalid-> where('coupon_invalid.request_time', '>=', $search['from']);
        empty( $search['to'] )        || $couponInvalid-> where('coupon_invalid.request_time', '<=', $search['to']);
        empty( $search['storage_id'] )|| $couponInvalid-> where('coupon_invalid.storage_id', $search['storage_id']);
        empty( $search['status'] )    || $couponInvalid-> where('coupon_invalid.status', $search['status']);
        $list = $couponInvalid-> paginate(15);
        return view('invalid.invalid_search', [
            'list'    => $list,
            'status'  => SystemStatusDao::getCouponInvalid(),
            'class'   => CouponClassDao::getCouponClass(),
            'type'    => CouponTypeDao::getCouponType(),
            'storages'=> json_encode( StorageDao::getStorages(), JSON_UNESCAPED_UNICODE ),
            'search'  => $search
        ]);
    }
    public function searchShow()
    {
        $id = Input::get('id');
        if( empty( $id )) abort( 403, '缺少ID' );
        $userStorageId = session( 'user.storage_id' );
        $re = CouponInvalid::join( 'storage as b', 'coupon_invalid.storage_id', '=', 'b.id' )
            -> whereRaw( "FIND_IN_SET( {$userStorageId}, lv_b.`full_id` )" )
            -> where( 'coupon_invalid.node_id', session( 'user.node_id' ))
            -> where( 'coupon_invalid.id', $id )
            -> select( 'coupon_invalid.*' )
            -> first();
        if( empty( $re )) abort( 403, 'ID不存在' );

        return view('invalid.invalid_search_show', [
            'coupon'  => $re-> toArray(),
            'status'  => SystemStatusDao::getCouponInvalid(),
            'urlParam'=> S::getUrlParam()
        ]);
    }
}