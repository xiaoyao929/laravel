<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\{Input,Validator,DB};

use App\Model\{CouponMake,CouponPutIn,CouponInfo,CouponStorageStock};
use App\Dao\{CouponClassDao,CouponTypeDao,StorageDao,SystemStatusDao,CouponStorageStockDao};
use Lib\PublicClass\{S};

class PutInController extends Controller
{
    public function makeOver()
    {
        S::setUrlParam();
        $search = Input::all();
        $user   = session( 'user' );
        $couponMake    = CouponMake::where( 'request_storage_id', $user['storage_id'] )
            -> where('status', CouponMake::STATUS_COMPLETE)
            -> where( 'node_id', $user['node_id'] )
            -> orderBy( 'seq', 'desc' );
        empty( $search['class'] )     || $couponMake-> where('coupon_make.coupon_class_id', $search['class']);
        empty( $search['name'] )      || $couponMake-> where('coupon_make.coupon_type_id', $search['name']);
        empty( $search['seq'] )       || $couponMake-> where('coupon_make.seq', 'like', "%{$search['seq']}%");
        empty( $search['from'] )      || $couponMake-> where('coupon_make.request_time', '>=', $search['from']);
        empty( $search['to'] )        || $couponMake-> where('coupon_make.request_time', '<=', $search['to']);
        empty( $search['parent_id'] ) || $couponMake-> where('coupon_make.request_storage_id', $search['parent_id']);
        $list = $couponMake-> paginate(15);
        return view('inventory.make_over', [
            'list'    => $list,
            'class'   => CouponClassDao::getCouponClass(),
            'type'    => CouponTypeDao::getCouponType(),
            'storages'=> json_encode( StorageDao::getStorages(), JSON_UNESCAPED_UNICODE ),
            'search'  => $search
        ]);
    }
    public function makeShow()
    {
        $id = Input::get('id');
        if( empty( $id )) abort( 403, '缺少ID' );
        $user   = session( 'user' );
        $re = CouponMake::where( 'request_storage_id', $user['storage_id'] )
            -> where( 'status', CouponMake::STATUS_COMPLETE )
            -> where( 'node_id', $user['node_id'] )
            -> where( 'id', $id )
            -> first();
        if( empty( $re )) abort( 403, 'ID不存在' );

        return view('inventory.make_show', ['coupon'=> $re-> toArray(), 'urlParam'=> S::getUrlParam()]);
    }

    /**
     * 入库提交审核
     * @return \Illuminate\Http\RedirectResponse
     */
    public function makeSave()
    {
        $post = Input::all();
        $validator = Validator::make( $post, [
            'id'   => 'required'
        ], [
            'id.required'   => '最少选择一条记录'
        ]);
        if( $validator-> fails() )
        {
            return redirect('/inventory/make/list'.S::getUrlParam())-> withErrors( $validator );
        }
        $user       = session('user');
        $couponMake = CouponMake::where( 'request_storage_id', $user['storage_id'] )
            -> whereIn('id', $post['id'] )
            -> where( 'node_id', session( 'user.node_id' ))
            -> where( 'status', CouponMake::STATUS_COMPLETE )
            -> get()
            -> toArray();
        if( empty( $couponMake )) abort( 403, 'ID不存在' );
        //获取入库表内的ID，因为会存在审核不通过
        $arr        = CouponPutIn::whereIn( 'make_id', $post['id'] )-> pluck('make_id')-> toArray();
        $param      = [];
        $noUpdateId = [];
        foreach ( $couponMake as $v )
        {
            if( empty( $arr ) || !in_array( $v['id'], $arr ))
            {
                $param[] = [
                    'node_id'          => $user['node_id'],
                    'seq'              => getOperateSeq('入库'),
                    'make_seq'         => $v['seq'],
                    'make_id'          => $v['id'],
                    'storage_id'       => $user['storage_id'],
                    'storage_name'     => $user['storage_name'],
                    'coupon_class_id'  => $v['coupon_class_id'],
                    'coupon_class_name'=> $v['coupon_class_name'],
                    'coupon_type_id'   => $v['coupon_type_id'],
                    'coupon_type_name' => $v['coupon_type_name'],
                    'amount'           => $v['amount'],
                    'begin_time'       => $v['begin_time'],
                    'end_time'         => $v['end_time'],
                    'start_flow_no'    => $v['start_flow_no'],
                    'end_flow_no'      => $v['end_flow_no'],
                    'status'           => CouponPutIn::STATUS_PENDING,
                    'request_user_id'  => $user['id'],
                    'request_user_name'=> $user['nickname'],
                    'request_time'     => date('Y-m-d H:i:s'),
                    'created_at'       => date('Y-m-d H:i:s'),
                    'updated_at'       => date('Y-m-d H:i:s')
                ];
            }
            else
            {
                $noUpdateId[] = $v['id'];
            }
        }
        try
        {
            DB::beginTransaction();
            CouponPutIn::insert($param);
            CouponMake::whereIn('id', $post['id'] )-> update([ 'status'=> CouponMake::STATUS_PUT_IN_PENDING, 'updated_at'=> date('Y-m-d H:i:s') ]);
            if(!empty( $noUpdateId )) {
                CouponPutIn::whereIn('make_id', $noUpdateId)
                    -> update([
                        'reason'           => '',
                        'text'             => '',
                        'memo'             => '',
                        'status'    => CouponPutIn::STATUS_PENDING,
                        'updated_at'=> date('Y-m-d H:i:s'),
                        'updated_at'=> date('Y-m-d H:i:s')
                    ]);
            }
            DB::commit();
            return redirect('/inventory/make/list'.S::getUrlParam())-> with( promptMsg( '提交成功', 1 ));
        }
        catch ( \Exception $e )
        {
            DB::rollBack();
            return redirect('/inventory/make/list'.S::getUrlParam())-> with( promptMsg( '数据保存失败', 4 ));
        }
    }
    public function auditList()
    {
        S::setUrlParam();
        $search        = Input::all();
        $userStorageId = session( 'user.storage_id' );
        $couponPutIn   = CouponPutIn::join( 'storage as b', 'coupon_put_in.storage_id', '=', 'b.id' )
            -> whereRaw( "FIND_IN_SET( {$userStorageId}, lv_b.`full_id` )" )
            -> where( 'coupon_put_in.status', CouponPutIn::STATUS_PENDING )
            -> where( 'coupon_put_in.node_id', session( 'user.node_id' ))
            -> orderBy( 'coupon_put_in.seq', 'desc' )
            -> select( 'coupon_put_in.*' );
        empty( $search['class'] )     || $couponPutIn-> where('coupon_put_in.coupon_class_id', $search['class']);
        empty( $search['name'] )      || $couponPutIn-> where('coupon_put_in.coupon_type_id', $search['name']);
        empty( $search['makeseq'] )   || $couponPutIn-> where('coupon_put_in.make_seq', 'like', "%{$search['makeseq']}%");
        empty( $search['seq'] )       || $couponPutIn-> where('coupon_put_in.seq', 'like', "%{$search['seq']}%");
        empty( $search['from'] )      || $couponPutIn-> where('coupon_put_in.request_time', '>=', $search['from']);
        empty( $search['to'] )        || $couponPutIn-> where('coupon_put_in.request_time', '<=', $search['to']);
        empty( $search['parent_id'] ) || $couponPutIn-> where('coupon_put_in.storage_id', $search['parent_id']);
        $list = $couponPutIn-> paginate(15);

        return view('inventory.make_audit', [
            'list'    => $list,
            'class'   => CouponClassDao::getCouponClass(),
            'type'    => CouponTypeDao::getCouponType(),
            'storages'=> json_encode( StorageDao::getStorages(), JSON_UNESCAPED_UNICODE ),
            'search'  => $search
        ]);
    }
    public function auditSave()
    {
        set_time_limit(0);
        $post = Input::all();
        $validator = Validator::make( $post, [
            'id'   => 'required'
        ], [
            'id.required'   => '最少选择一条记录'
        ]);
        if( $validator-> fails() )
        {
            return redirect('/inventory/make/audits'.S::getUrlParam())-> withErrors( $validator );
        }

        $user = session('user');
        $data = CouponPutIn::leftJoin( 'coupon_type as b', 'coupon_put_in.coupon_type_id', '=', 'b.id' )
            -> join( 'storage as c', 'coupon_put_in.storage_id', '=', 'c.id' )
            -> whereRaw( "FIND_IN_SET( {$user['storage_id']}, lv_c.`full_id` )" )
            -> select( 'coupon_put_in.*', 'b.price' )
            -> whereIn( 'coupon_put_in.id', $post['id'] )
            -> where( 'coupon_put_in.node_id', session( 'user.node_id' ))
            -> where( function ( $query ){
                $query-> orWhere( 'coupon_put_in.status',  CouponPutIn::STATUS_PENDING )
                    -> orWhere( 'coupon_put_in.status',  CouponPutIn::STATUS_NO_THROUGH );
            })
            -> get()
            -> toArray();
        if( empty( $data )) abort( 403, 'ID不存在' );

        $makeId  = array_column( $data, 'make_id' );
        $upIdArr = array_column( $data, 'id' );

        //获取仓库库存记录中的券类型记录
        $re = CouponStorageStock::where( 'node_id', session( 'user.node_id' ))
            -> where( function ( $query ) use( $data ){
                foreach ( $data as $v )
                {
                    $query-> orWhere( function ( $query1 ) use( $v ) {
                        $query1-> where( 'storage_id', $v['storage_id'] )
                            -> where( 'coupon_type_id', $v['coupon_type_id'] );
                    });
                }
            })
            -> get()
            -> toArray();

        if( empty( $re )) $stock = false;
        else $stock = true;
        $stockArr = [];
        foreach ( $re as $v )
        {
            $stockArr[$v['storage_id']][] = $v['coupon_type_id'];
        }

        if( !empty( $post['action'] ) && $post['action'] == 'pass' )//审核通过
        {
            $param       = [];//拼装单个券数据
            foreach ( $data as $v )
            {
                if( $stock )//存在记录 判别更新还是新增
                {
                    if( in_array( $v['coupon_type_id'], $stockArr[$v['storage_id']] ))//存在记录就是更新
                    {
                        CouponStorageStockDao::setUpdateParam( $v );
                    }
                    else//不存在就是新增
                    {
                        CouponStorageStockDao::setInsertParParam( $v );
                    }
                }
                else //不存在记录 全部新增
                {
                    CouponStorageStockDao::setInsertParParam( $v );
                }
                for ( $i=(int)$v['start_flow_no']; $i<=(int)$v['end_flow_no']; $i++ )
                {
                    $param[] = [
                        'node_id'          => $user['node_id'],
                        'put_in_id'        => $v['id'],
                        'coupon_flow_no'   => $i,
                        'coupon_class_id'  => $v['coupon_class_id'],
                        'coupon_class_name'=> $v['coupon_class_name'],
                        'coupon_type_id'   => $v['coupon_type_id'],
                        'coupon_type_name' => $v['coupon_type_name'],
                        'begin_time'       => $v['begin_time'],
                        'end_time'         => $v['end_time'],
                        'coupon_price'     => $v['price'],
                        'status'           => CouponInfo::STATUS_INVENTORY,
                        'storage_id'       => $v['storage_id'],
                        'storage_name'     => $v['storage_name'],
                    ];
                }
            }

            DB::beginTransaction();
            try
            {
                CouponPutIn::whereIn( 'id', $upIdArr )-> update([
                    'approve_user_id'  => $user['id'],
                    'approve_user_name'=> $user['nickname'],
                    'approve_time'     => date('Y-m-d H:i:s'),
                    'status'           => CouponPutIn::STATUS_PUT_IN,
                    'updated_at'       => date('Y-m-d H:i:s')
                ]);
                CouponMake::whereIn( 'id', $makeId )-> update([ 'status'=> CouponMake::STATUS_PUT_IN, 'updated_at'=> date('Y-m-d H:i:s') ]);//更新制券表
                CouponInfo::insert($param);//记录卡券详情表
                $stockInsert = CouponStorageStockDao::getInsertParam();
                if( !empty( $stockInsert )) CouponStorageStock::insert($stockInsert);
                $stockUpdate = CouponStorageStockDao::getUpdateParam();
                if( !empty( $stockUpdate ))
                {
                    foreach ( $stockUpdate as $v )
                    {
                        CouponStorageStock::where( 'node_id', $user['node_id'] )
                            -> where( 'coupon_type_id', $v['coupon_type_id'] )
                            -> increment( 'amount_no_sale', (int)$v['amount'] );
                    }
                }

                DB::commit();
                return redirect('/inventory/make/audits'.S::getUrlParam())-> with( promptMsg( '提交成功', 1 ));
            }
            catch ( \Exception $e )
            {
                DB::rollBack();
                $validator-> errors()-> add('error', $e-> getTraceAsString());
                return redirect('/inventory/make/audits'.S::getUrlParam())->  with( promptMsg( '数据保存失败', 4 ));
            }
        }
        else//审核不通过
        {
            $verify = true;
            if( empty( $post['pass_select'] ))
            {
                $verify = false;
                $validator-> errors()-> add('error', '未选择不通过原因');
            }
            if( $post['pass_select'] == '其他' && empty( $post['pass_text'] ))
            {
                $verify = false;
                $validator-> errors()-> add('error', '选择其他时，必须填原因');
            }
            if( !$verify ) return redirect('/inventory/make/audits'.S::getUrlParam())-> withErrors( $validator );

            try
            {
                DB::beginTransaction();
                CouponPutIn::whereIn( 'id', $upIdArr )-> update([
                    'approve_user_id'  => $user['id'],
                    'approve_user_name'=> $user['nickname'],
                    'approve_time'     => date('Y-m-d H:i:s'),
                    'reason'           => $post['pass_select'],
                    'text'             => $post['pass_text'],
                    'memo'             => $post['pass_msg'],
                    'status'           => CouponPutIn::STATUS_NO_THROUGH,
                    'updated_at'       => date('Y-m-d H:i:s')
                ]);
                CouponMake::whereIn( 'id', $makeId )-> update([ 'status'=> CouponMake::STATUS_COMPLETE, 'updated_at'=> date('Y-m-d H:i:s') ]);
                DB::commit();
                return redirect('/inventory/make/audits'.S::getUrlParam())-> with( promptMsg( '提交成功', 1 ));
            }
            catch ( \Exception $e )
            {
                DB::rollBack();
                $validator-> errors()-> add('error', $e-> getTraceAsString());
                return redirect('/inventory/make/audits'.S::getUrlParam())->  with( promptMsg( '数据保存失败', 4 ));
            }
        }
    }
    public function auditShow()
    {
        $id = Input::get('id');
        if( empty( $id )) abort( 403, '缺少ID' );
        $userStorageId = session( 'user.storage_id' );
        $re = CouponPutIn::join( 'storage as b', 'coupon_put_in.storage_id', '=', 'b.id' )
            -> whereRaw( "FIND_IN_SET( {$userStorageId}, lv_b.`full_id` )" )
            -> where( 'coupon_put_in.status', CouponPutIn::STATUS_PENDING )
            -> where( 'coupon_put_in.node_id', session( 'user.node_id' ))
            -> where( 'coupon_put_in.id', $id )
            -> select( 'coupon_put_in.*' )
            -> first();
        if( empty( $re )) abort( 403, 'ID不存在' );

        return view('inventory.make_audit_show', ['coupon'=> $re-> toArray(), 'urlParam'=> S::getUrlParam()]);
    }
    public function searchList()
    {
        S::setUrlParam();
        $search        = Input::all();
        if( !isset( $search['status'] )) $search['status'] = CouponPutIn::STATUS_PUT_IN;
        $userStorageId = session( 'user.storage_id' );
        $couponPutIn   = CouponPutIn::join( 'storage as b', 'coupon_put_in.storage_id', '=', 'b.id' )
            -> whereRaw( "FIND_IN_SET( {$userStorageId}, lv_b.`full_id` )" )
            -> where( 'coupon_put_in.node_id', session( 'user.node_id' ))
            -> select( 'coupon_put_in.*' )
            -> orderBy( 'seq', 'desc' );
        empty( $search['class'] )     || $couponPutIn-> where('coupon_put_in.coupon_class_id', $search['class']);
        empty( $search['name'] )      || $couponPutIn-> where('coupon_put_in.coupon_type_id', $search['name']);
        empty( $search['seq'] )       || $couponPutIn-> where('coupon_put_in.seq', 'like', "%{$search['seq']}%");
        empty( $search['makeseq'] )   || $couponPutIn-> where('coupon_put_in.make_seq', 'like', "%{$search['makeseq']}%");
        empty( $search['from'] )      || $couponPutIn-> where('coupon_put_in.request_time', '>=', $search['from']);
        empty( $search['to'] )        || $couponPutIn-> where('coupon_put_in.request_time', '<=', $search['to']);
        empty( $search['parent_id'] ) || $couponPutIn-> where('coupon_put_in.storage_id', $search['parent_id']);
        empty( $search['status'] )    || $couponPutIn-> where('coupon_put_in.status', $search['status']);
        $list = $couponPutIn-> paginate(15);
        return view('inventory.make_search', [
            'list'    => $list,
            'status'  => SystemStatusDao::getCouponPutIn(),
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
        $re = CouponPutIn::join( 'storage as b', 'coupon_put_in.storage_id', '=', 'b.id' )
            -> whereRaw( "FIND_IN_SET( {$userStorageId}, lv_b.`full_id` )" )
            -> where( 'coupon_put_in.node_id', session( 'user.node_id' ))
            -> where( 'coupon_put_in.id', $id )
            -> select( 'coupon_put_in.*' )
            -> first();
        if( empty( $re )) abort( 403, 'ID不存在' );

        return view('inventory.make_search_show', [
            'coupon'=> $re-> toArray(),
            'status'  => SystemStatusDao::getCouponPutIn(),
            'urlParam'=> S::getUrlParam()
        ]);
    }
}