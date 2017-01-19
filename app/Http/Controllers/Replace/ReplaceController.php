<?php

namespace App\Http\Controllers\Replace;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\{Input,Validator,DB};
use Lib\PublicClass\{S,Log};

use App\Dao\{CouponTypeDao,CouponClassDao,StorageDao,SupportDao,SystemStatusDao};
use App\Model\{CouponReplace,CouponInfo,CouponStorageStock};

/**
 * 换券
 */
class ReplaceController extends Controller
{
    public function apply()
    {
        return view( 'replace.replace_apply', [
            'coupon'=> [],
            'type'  => CouponTypeDao::getCouponType()
        ]);
    }
    public function applySave()
    {
        $coupon = Input::all();
        $validator = Validator::make( $coupon, [
            'type'  => 'required',
            'from'  => ['required','numeric'],
            'to'    => ['required','numeric'],
            'reason'=> ['required']
        ], [
            'type.required'  => '券简称必须写',
            'from.required'  => '旧券号必须写',
            'to.required'    => '新券号必须写',
            'reason.required'=> '原因必须写',
            'start.numeric'  => '旧券号必须数字',
            'end.numeric'    => '新券号必须数字',
        ]);

        $param = [
            'coupon'  => $coupon,
            'type'    => CouponTypeDao::getCouponType(),
        ];
        if( $validator-> fails() )
        {
            return view( 'replace.replace_apply', $param )-> withErrors( $validator );
        }

        //验证原因必填
        if( $coupon['reason'] == '其他' && empty( $coupon['text'] ))
        {
            $validator-> errors()-> add( 'error', '原因选择其他时，理由必须填' );
            return view( 'replace.replace_apply', $param )-> withErrors( $validator );
        }

        $user = session( 'user' );
        $data = CouponInfo::where( 'node_id', $user['node_id'] )
            -> where( 'coupon_type_id', $coupon['type'] )
            -> where( function ( $query ) {
                $query-> orWhere( 'status', CouponInfo::STATUS_INVENTORY )
                -> orWhere( 'status', CouponInfo::STATUS_SALES );
            })
            -> whereIn( 'coupon_flow_no', [ $coupon['from'], $coupon['to'] ])
            -> get()
            -> toArray();

        if( count( $data ) != 2 )
        {
            $validator-> errors()-> add( 'error', '输入的券号有错误，请检查！' );
            return view( 'replace.replace_apply', $param )-> withErrors( $validator );
        }

        $couponReplace = new CouponReplace();

        foreach ( $data as $v )
        {
            if( $v['coupon_flow_no'] == $coupon['from'] )
            {
                if( $v['status'] != CouponInfo::STATUS_SALES )
                {
                    $validator-> errors()-> add( 'error', '旧券必须是已经销售的！' );
                    return view( 'replace.replace_apply', $param )-> withErrors( $validator );
                }
                $couponReplace-> from_flow_no      = $v['coupon_flow_no'];
                $couponReplace-> from_storage_id   = $v['storage_id'];
                $couponReplace-> from_storage_name = $v['storage_name'];
                $couponReplace-> coupon_class_id   = $v['coupon_class_id'];
                $couponReplace-> coupon_class_name = $v['coupon_class_name'];
                $couponReplace-> coupon_type_id    = $v['coupon_type_id'];
                $couponReplace-> coupon_type_name  = $v['coupon_type_name'];
            }
            elseif( $v['coupon_flow_no'] == $coupon['to'] )
            {
                if( $v['storage_id'] != $user['storage_id'] )
                {
                    $validator-> errors()-> add( 'error', '只能更换本仓库券！' );
                    return view( 'replace.replace_apply', $param )-> withErrors( $validator );
                }
                if( $v['status'] != CouponInfo::STATUS_INVENTORY )
                {
                    $validator-> errors()-> add( 'error', '新券必须是在库存中的！' );
                    return view( 'replace.replace_apply', $param )-> withErrors( $validator );
                }
                $couponReplace-> to_flow_no      = $v['coupon_flow_no'];
                $couponReplace-> to_storage_id   = $v['storage_id'];
                $couponReplace-> to_storage_name = $v['storage_name'];
            }
        }
        $couponReplace-> node_id           = $user['node_id'];
        $couponReplace-> seq               = getOperateSeq('换券');
        $couponReplace-> status            = CouponReplace::STATUS_PENDING;
        $couponReplace-> reason            = $coupon['reason'];
        $couponReplace-> text              = $coupon['text'];
        $couponReplace-> memo              = $coupon['memo'];
        $couponReplace-> request_user_id   = $user['id'];
        $couponReplace-> request_user_name = $user['nickname'];
        $couponReplace-> request_time      = date('Y-m-d H:i:s');
        DB::beginTransaction();
        try
        {
            //记录流水表
            $couponReplace-> save();
            //变更券详情状态
            $id = array_column ( $data, 'id' );
            CouponInfo::whereIn( 'id', $id )-> update([ 'status'=> CouponInfo::STATUS_PENDING ]);
            //修改库存数
            if( $couponReplace-> from_storage_id == $couponReplace-> to_storage_id )
            {
                CouponStorageStock::where( 'node_id', $user['node_id'] )
                    -> where( 'storage_id', $couponReplace-> to_storage_id )
                    -> where( 'coupon_type_id', $couponReplace-> coupon_type_id )
                    -> update([
                        'amount_audit'  => DB::raw( "amount_audit + 2" ),
                        'amount_no_sale'=> DB::raw( "amount_no_sale - 1" ),
                        'amount_saled'  => DB::raw( "amount_saled - 1" ),
                    ]);
            }
            else
            {
                //旧仓库销售数减一
                CouponStorageStock::where( 'node_id', $user['node_id'] )
                    -> where( 'storage_id', $couponReplace-> from_storage_id )
                    -> where( 'coupon_type_id', $couponReplace-> coupon_type_id )
                    -> update([
                        'amount_audit'  => DB::raw( "amount_audit + 1" ),
                        'amount_saled'  => DB::raw( "amount_saled - 1" ),
                    ]);
                //新券仓库库存数减一
                CouponStorageStock::where( 'node_id', $user['node_id'] )
                    -> where( 'storage_id', $couponReplace-> to_storage_id )
                    -> where( 'coupon_type_id', $couponReplace-> coupon_type_id )
                    -> update([
                        'amount_audit'  => DB::raw( "amount_audit + 1" ),
                        'amount_no_sale'=> DB::raw( "amount_no_sale - 1" ),
                    ]);
            }

            DB::commit();
            return redirect('/exchange/replace/apply'.S::getUrlParam())-> with( promptMsg( '提交成功', 1 ));
        }
        catch ( \Exception $e )
        {
            DB::rollBack();
            $validator-> errors()-> add('error', '数据保存失败');
            return view( 'replace.replace_apply', $param )-> withErrors( $validator );
        }
    }
    public function auditList()
    {
        S::setUrlParam();
        $search = Input::all();
        $user   = session( 'user' );
        $couponReplace = CouponReplace::join( 'storage as b', 'coupon_replace.to_storage_id', '=', 'b.id' )
            -> whereRaw( "FIND_IN_SET( {$user['storage_id']}, lv_b.`full_id` )" )
            -> where( 'coupon_replace.status', CouponReplace::STATUS_PENDING )
            -> where( 'coupon_replace.node_id', session( 'user.node_id' ))
            -> orderBy( 'coupon_replace.seq', 'desc' )
            -> select( 'coupon_replace.*' );

        empty( $search['class'] )     || $couponReplace-> where('coupon_replace.coupon_class_id', $search['class']);
        empty( $search['name'] )      || $couponReplace-> where('coupon_replace.coupon_type_id', $search['name']);
        empty( $search['from'] )      || $couponReplace-> where('coupon_replace.request_time', '>=', $search['from']);
        empty( $search['to'] )        || $couponReplace-> where('coupon_replace.request_time', '<=', $search['to']);
        empty( $search['storage_id'] )|| $couponReplace-> where('coupon_replace.to_storage_id', $search['storage_id']);
        $list = $couponReplace-> paginate(15);
        return view('replace.replace_audit', [
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
        $user   = session('user');
        $re = CouponReplace::join( 'storage as b', 'coupon_replace.to_storage_id', '=', 'b.id' )
            -> whereRaw( "FIND_IN_SET( {$user['storage_id']}, lv_b.`full_id` )" )
            -> where( 'coupon_replace.status', CouponReplace::STATUS_PENDING )
            -> where( 'coupon_replace.id', $id )
            -> where( 'coupon_replace.node_id', $user['node_id'])
            -> select( 'coupon_replace.*' )
            -> first();

        if( empty( $re )) abort( 403, 'ID不存在' );

        return view('replace.replace_audit_show', ['coupon'=> $re-> toArray(), 'urlParam'=> S::getUrlParam()]);
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
            return redirect('/exchange/replace/audit'.S::getUrlParam())-> withErrors( $validator );
        }
        $user = session('user');
        $data = CouponReplace::join( 'storage as b', 'coupon_replace.to_storage_id', '=', 'b.id' )
            -> whereRaw( "FIND_IN_SET( {$user['storage_id']}, lv_b.`full_id` )" )
            -> where( 'coupon_replace.status', CouponReplace::STATUS_PENDING )
            -> where( 'coupon_replace.node_id', $user['node_id'] )
            -> whereIn( 'coupon_replace.id', $post['id'] )
            -> select( 'coupon_replace.*' )
            -> get()
            -> toArray();

        if( empty( $data )) abort( 403, 'ID不存在' );
        if( count( $post['id'] ) != count( $data ))
        {
            return redirect('/exchange/replace/audit'.S::getUrlParam())-> with( promptMsg( '提交的单号中存在错误数据！', 3 ));
        }
        DB::beginTransaction();
        if( $post['action'] == 'pass' )//审核通过
        {
            //此处通知支撑
            $invalidItem  = [
                'node_id'=> $user['node_id'],
            ];
            $activateItem = [
                'node_id'=> $user['node_id'],
            ];

            $stock = [];
            $info  = [];
            //整理数据
            foreach ( $data as $v )
            {
                if( $v['support_invalid'] == CouponReplace::SUPPORT_INVALID_ON )
                {
                    $invalidItem['item'][] = [
                        'start_flow_no'=> $v['from_flow_no'],
                        'amount'       => 1,
                    ];
                }
                if( $v['support_activate'] == CouponReplace::SUPPORT_ACTIVATE_ON )
                {
                    $activateItem['item'][] = [
                        'start_flow_no'=> $v['to_flow_no'],
                        'amount'       => 1,
                    ];
                }
                //整理仓库变更数据
                if( !isset( $stock[$v['from_storage_id']][$v['coupon_type_id']] ))
                {
                    $stock[$v['from_storage_id']][$v['coupon_type_id']] = [
                        'amount_audit'=> 1,
                        'amount_saled'=> 1,
                    ];
                }
                else
                {
                    $stock[$v['from_storage_id']][$v['coupon_type_id']]['amount_audit'] ++;
                    $stock[$v['from_storage_id']][$v['coupon_type_id']]['amount_saled'] ++;
                }
                if( !isset( $stock[$v['to_storage_id']][$v['coupon_type_id']] ))
                {
                    $stock[$v['to_storage_id']][$v['coupon_type_id']] = [
                        'amount_audit'=> 1,
                        'amount_destroyed'=> 1,
                    ];
                }
                elseif( !isset( $stock[$v['to_storage_id']][$v['coupon_type_id']]['amount_destroyed'] ))
                {
                    $stock[$v['to_storage_id']][$v['coupon_type_id']]['amount_audit'] ++;
                    $stock[$v['to_storage_id']][$v['coupon_type_id']]['amount_destroyed'] = 1;
                }
                else
                {
                    $stock[$v['to_storage_id']][$v['coupon_type_id']]['amount_audit'] ++;
                    $stock[$v['to_storage_id']][$v['coupon_type_id']]['amount_destroyed'] ++;
                }

                if( !isset( $info[$v['from_storage_id']] ))
                {
                    $info[$v['from_storage_id']]['name'] = $v['from_storage_name'];
                }
                if( !isset( $info[$v['to_storage_id']] ))
                {
                    $info[$v['to_storage_id']]['name'] = $v['to_storage_name'];
                }
                //整理券详情变更数据
                if( $v['from_flow_no'] == $v['to_flow_no'] )
                {
                    $info[$v['from_storage_id']]['from'][] = $v['from_flow_no'];
                    $info[$v['from_storage_id']]['to'][]   = $v['to_flow_no'];
                }
                else
                {
                    $info[$v['to_storage_id']]['from'][] = $v['from_flow_no'];
                    $info[$v['from_storage_id']]['to'][] = $v['to_flow_no'];
                }
            }

            //有数据就通知支撑
            if( !empty( $invalidItem['item'] ))
            {
                $invalidResult  = SupportDao::voidCoupon( $invalidItem );//作废通知
                if( !isset($invalidResult['Status']['StatusCode']) || $invalidResult['Status']['StatusCode'] != '0000' )
                {
                    return redirect('/exchange/replace/audit'.S::getUrlParam())-> with( promptMsg( '失败原因:'.$invalidResult['Status']['StatusText'], 4 ));
                }
            }

            if( !empty( $activateItem['item'] ))
            {
                $activateResult = SupportDao::activate( $activateItem ); //激活通知
                if( !isset($activateResult['Status']['StatusCode']) || $activateResult['Status']['StatusCode'] != '0000' )
                {
                    //如果激活通知失败，先记录作废状态!
                    try{
                        CouponReplace::whereIn( 'id', $post['id'] )-> update([ 'support_invalid'=> CouponReplace::SUPPORT_INVALID_OFF ]);
                        DB::commit();
                        return redirect('/exchange/replace/audit'.S::getUrlParam())-> with( promptMsg( '失败原因:'.$activateResult['Status']['StatusText'], 4 ));
                    }
                    catch ( \Exception $e )
                    {
                        DB::rollBack();
                        Log::log_write( $e->getTraceAsString());
                        return redirect('/exchange/replace/audit'.S::getUrlParam())-> with( promptMsg( '数据保存失败', 4 ));
                    }
                }
            }
            try{
                //更改流水记录状态
                CouponReplace::whereIn( 'id', $post['id'] )
                    -> update([
                        'status'           => CouponReplace::STATUS_TRANSIT,
                        'support_invalid'  => CouponReplace::SUPPORT_INVALID_OFF,
                        'support_activate' => CouponReplace::SUPPORT_ACTIVATE_OFF,
                        'approve_user_id'  => $user['id'],
                        'approve_user_name'=> $user['nickname'],
                        'approve_time'     => date('Y-m-d H:i:s')
                    ]);
                //变更库存
                foreach ( $stock as $storageId=> $value )
                {
                    foreach ( $value as $typeId=> $v )
                    {
                        $param = [
                            'amount_audit'=> DB::raw( "amount_audit - {$v['amount_audit']}")
                        ];
                        if(isset( $v['amount_saled'] ))     $param['amount_saled']     =  DB::raw( "amount_saled + {$v['amount_saled']}");
                        if(isset( $v['amount_destroyed'] )) $param['amount_destroyed'] =  DB::raw( "amount_destroyed + {$v['amount_destroyed']}");
                        //变更库存数量
                        CouponStorageStock::where( 'node_id', $user['node_id'] )
                            -> where( 'storage_id', $storageId )
                            -> where( 'coupon_type_id', $typeId )
                            -> update( $param );
                    }
                }
                //变更券状态
                foreach ( $info as $storageId=> $value )
                {
                    if( !empty( $value['from'] ))
                    {
                        $param = [
                            'status'      => CouponInfo::STATUS_INVALID,
                            'storage_id'  => $storageId,
                            'storage_name'=> $value['name'],
                        ];
                        CouponInfo::whereIn( 'coupon_flow_no', $value['from'] )-> update($param);
                    }
                    if( !empty( $value['to'] ))
                    {
                        $param = [
                            'status'      => CouponInfo::STATUS_SALES,
                            'storage_id'  => $storageId,
                            'storage_name'=> $value['name'],
                        ];
                        CouponInfo::whereIn( 'coupon_flow_no', $value['to'] )-> update($param);
                    }
                }

                DB::commit();
                return redirect('/exchange/replace/audit'.S::getUrlParam())-> with( promptMsg( '提交成功', 1 ));
            }
            catch ( \Exception $e )
            {
                DB::rollBack();
                Log::log_write( $e->getTraceAsString());
                return redirect('/exchange/replace/audit'.S::getUrlParam())-> with( promptMsg( '数据保存失败', 4 ));
            }
        }
        elseif ( $post['action'] == 'no_pass' )//审核未通过
        {
            $stock = [];
            foreach ( $data as $v )
            {
                //整理仓库变更数据
                if( !isset( $stock[$v['from_storage_id']][$v['coupon_type_id']] ))
                {
                    $stock[$v['from_storage_id']][$v['coupon_type_id']] = [
                        'amount_audit'=> 1,
                        'amount_saled'=> 1,
                    ];
                }
                else
                {
                    $stock[$v['from_storage_id']][$v['coupon_type_id']]['amount_audit'] ++;
                    $stock[$v['from_storage_id']][$v['coupon_type_id']]['amount_saled'] ++;
                }
                if( !isset( $stock[$v['to_storage_id']][$v['coupon_type_id']] ))
                {
                    $stock[$v['to_storage_id']][$v['coupon_type_id']] = [
                        'amount_audit'=> 1,
                        'amount_no_sale'=> 1,
                    ];
                }
                elseif( !isset( $stock[$v['to_storage_id']][$v['coupon_type_id']]['amount_no_sale'] ))
                {
                    $stock[$v['to_storage_id']][$v['coupon_type_id']]['amount_audit'] ++;
                    $stock[$v['to_storage_id']][$v['coupon_type_id']]['amount_no_sale'] = 1;
                }
                else
                {
                    $stock[$v['to_storage_id']][$v['coupon_type_id']]['amount_audit'] ++;
                    $stock[$v['to_storage_id']][$v['coupon_type_id']]['amount_no_sale'] ++;
                }
            }
            try{
                //更改流水记录状态
                CouponReplace::whereIn( 'id', $post['id'] )
                    -> update([
                        'status'           => CouponReplace::STATUS_NO_THROUGH,
                        'approve_user_id'  => $user['id'],
                        'approve_user_name'=> $user['nickname'],
                        'approve_time'     => date('Y-m-d H:i:s')
                    ]);
                //变更库存
                foreach ( $stock as $storageId=> $value )
                {
                    foreach ( $value as $typeId=> $v )
                    {
                        $param = [
                            'amount_audit'=> DB::raw( "amount_audit - {$v['amount_audit']}")
                        ];
                        if(isset( $v['amount_saled'] ))   $param['amount_saled']   =  DB::raw( "amount_saled + {$v['amount_saled']}");
                        if(isset( $v['amount_no_sale'] )) $param['amount_no_sale'] =  DB::raw( "amount_no_sale + {$v['amount_no_sale']}");
                        //变更库存数量
                        CouponStorageStock::where( 'node_id', $user['node_id'] )
                            -> where( 'storage_id', $storageId )
                            -> where( 'coupon_type_id', $typeId )
                            -> update( $param );
                    }
                }
                //变更券状态
                $fromId = array_column( $data, 'from_flow_no' );
                $toId   = array_column( $data, 'to_flow_no' );
                CouponInfo::whereIn( 'coupon_flow_no', $fromId )-> update([ 'status'=> CouponInfo::STATUS_SALES ]);
                CouponInfo::whereIn( 'coupon_flow_no', $toId )-> update([ 'status'=> CouponInfo::STATUS_INVENTORY ]);

                DB::commit();
                return redirect('/exchange/replace/audit'.S::getUrlParam())-> with( promptMsg( '提交成功', 1 ));
            }
            catch ( \Exception $e )
            {
                DB::rollBack();
                Log::log_write( $e->getTraceAsString());
                return redirect('/exchange/replace/audit'.S::getUrlParam())-> with( promptMsg( '数据保存失败', 4 ));
            }
        }
    }
    public function searchList()
    {
        S::setUrlParam();
        $search = Input::all();
        if( !isset( $search['status'] )) $search['status'] = CouponReplace::STATUS_TRANSIT;
        $user   = session( 'user' );
        $couponReplace = CouponReplace::join( 'storage as b', 'coupon_replace.to_storage_id', '=', 'b.id' )
            -> whereRaw( "FIND_IN_SET( {$user['storage_id']}, lv_b.`full_id` )" )
            -> where( 'coupon_replace.node_id', session( 'user.node_id' ))
            -> orderBy( 'coupon_replace.seq', 'desc' )
            -> select( 'coupon_replace.*' );

        empty( $search['class'] )     || $couponReplace-> where('coupon_replace.coupon_class_id', $search['class']);
        empty( $search['name'] )      || $couponReplace-> where('coupon_replace.coupon_type_id', $search['name']);
        empty( $search['from'] )      || $couponReplace-> where('coupon_replace.request_time', '>=', $search['from']);
        empty( $search['to'] )        || $couponReplace-> where('coupon_replace.request_time', '<=', $search['to']);
        empty( $search['storage_id'] )|| $couponReplace-> where('coupon_replace.to_storage_id', $search['storage_id']);
        empty( $search['status'] )    || $couponReplace-> where('coupon_replace.status', $search['status']);
        empty( $search['flow_no'] )   || $couponReplace-> where(function ($query) use($search){
            $query-> orWhere( 'from_flow_no', $search['flow_no'] )
                -> orWhere( 'to_flow_no', $search['flow_no'] );
        });
        empty( $search['seq'] )       || $couponReplace-> where('coupon_replace.seq', 'like', "%{$search['seq']}%");
        $list = $couponReplace-> paginate(15);
        return view('replace.replace_list', [
            'list'    => $list,
            'status'  => SystemStatusDao::getCouponReplace(),
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
        $user = session( 'user' );
        $re   = CouponReplace::join( 'storage as b', 'coupon_replace.to_storage_id', '=', 'b.id' )
            -> whereRaw( "FIND_IN_SET( {$user['storage_id']}, lv_b.`full_id` )" )
            -> where( 'coupon_replace.id', $id )
            -> where( 'coupon_replace.node_id', session( 'user.node_id' ))
            -> select( 'coupon_replace.*' )
            -> first();
        if( empty( $re )) abort( 403, 'ID不存在' );

        return view('replace.replace_list_show', [
            'coupon'  => $re-> toArray(),
            'status'  => SystemStatusDao::getCouponReplace(),
            'urlParam'=> S::getUrlParam()
        ]);
    }
}