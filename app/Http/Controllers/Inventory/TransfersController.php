<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\{Input,Validator,DB};
use Lib\PublicClass\{S};

use App\Dao\{StorageDao,CouponClassDao,CouponTypeDao,SystemStatusDao};
use App\Model\{CouponStorageStock,CouponTransfers,CouponInfo,Storage};

/**
 * 券调拨
 */
class TransfersController extends Controller
{
    /**
     * 调拨申请列表
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function apply()
    {
        $search = Input::all();
        $user   = session( 'user' );
        $couponStorageStock = CouponStorageStock::where( 'storage_id', $user['storage_id'] )
            -> where( 'node_id', $user['node_id'] )
            -> orderBy( 'storage_id' )
            -> orderBy( 'coupon_type_id' );

        empty( $search['class'] )     || $couponStorageStock-> where('coupon_class_id', $search['class']);
        empty( $search['name'] )      || $couponStorageStock-> where('coupon_type_id', $search['name']);
        $list = $couponStorageStock-> paginate(15);
        return view('inventory.transfers_apply', [
            'list'    => $list,
            'class'   => CouponClassDao::getCouponClass(),
            'type'    => CouponTypeDao::getCouponType(),
            'search'  => $search
        ]);
    }

    /**
     * 调拨申请编辑
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function applyEdit()
    {
        $id     = Input::get('id');
        $userStorageId = session( 'user.storage_id' );
        S::setUrlParam();
        $coupon = CouponStorageStock::join( 'storage as b', 'coupon_storage_stock.storage_id', '=', 'b.id' )
            -> whereRaw( "FIND_IN_SET( {$userStorageId}, lv_b.`full_id` )" )
            -> where( 'coupon_storage_stock.node_id', session( 'user.node_id' ))
            -> where( 'coupon_storage_stock.id', $id )
            -> select( 'coupon_storage_stock.*' )
            -> first();
        if( empty( $coupon )) abort( 403, 'ID不存在' );

        return view( 'inventory.transfers_apply_edit', [
            'urlParam'=> S::getUrlParam(),
            'coupon'  => $coupon-> toArray(),
            'storages'=> json_encode( StorageDao::getTransfersStorages(), JSON_UNESCAPED_UNICODE ),
        ]);
    }

    /**
     * 调拨申请保存
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function applySave()
    {
        $coupon = Input::all();
        $validator = Validator::make( $coupon, [
            'id'        => 'required',
            'start'     => ['required','numeric'],
            'end'       => ['required','numeric'],
            'storage_id'=> ['required','numeric']
        ], [
            'id.required'        => 'Id必须写',
            'start.required'     => '开始券号必须写',
            'end.required'       => '结束券号必须写',
            'storage_id.required'=> '接受仓库必须写',
            'start.numeric'      => '开始券号必须数字',
            'end.numeric'        => '结束券号必须数字',
            'storage_id.numeric' => '仓库ID必须数字',
        ]);

        $param = [
            'urlParam'=> S::getUrlParam(),
            'coupon'  => $coupon,
            'storages'=> json_encode( StorageDao::getTransfersStorages(), JSON_UNESCAPED_UNICODE ),
        ];

        if( $validator-> fails() )
        {
            return view( 'inventory.transfers_apply_edit', $param )-> withErrors( $validator );
        }
        $user   = session( 'user' );
        $couponStorageStock = CouponStorageStock::where( 'storage_id', $user['storage_id'] )
            -> where( 'node_id', $user['node_id'] )
            -> where( 'id', $coupon['id'] )
            -> first();
        if( empty( $couponStorageStock )) abort( 403, 'ID不存在' );
        $stock = $couponStorageStock-> toArray();

        if( $coupon['storage_id'] == $stock['storage_id'] )
        {
            $validator-> errors()-> add( 'error', '相同仓库不可调拨' );
            return view( 'inventory.transfers_apply_edit', $param )-> withErrors( $validator );
        }

        //判断调拨仓库限制
        $storageAction = false;
        $storage = Storage::whereRaw( "FIND_IN_SET( {$user['storage_id']}, `full_id` )" )
            -> where( 'id', $coupon['storage_id'] )
            -> first();

        if( !empty( $storage )) $storageAction = true; //所属下级仓库
        if( !$storageAction && $coupon['storage_id'] == $user['storage_parent_id'] ) $storageAction = true; //直属上级仓库
        if( !$storageAction )
        {
            $storage = Storage::where( 'level', $user['level'] )
                -> where( 'id', $coupon['storage_id'] )
                -> first();
            if( !empty( $storage )) $storageAction = true; //同级仓库
        }
        if( !$storageAction )
        {
            $validator-> errors()-> add( 'error', '调拨仓库只能是直属上级仓库，同级仓库，所属下级仓库' );
            return view( 'inventory.transfers_apply_edit', $param )-> withErrors( $validator );
        };

        $num = (int)$coupon['end'] - (int)$coupon['start'];
        if( $num < 0 ) abort( 403, '结束券号小于起始券号' );
        $num += 1; //通过券号计算的数量

        //判断输入的券号是否属于该类型下
        $coupons = CouponInfo::whereBetween( 'coupon_flow_no', [ $coupon['start'], $coupon['end'] ] )
            -> where( 'storage_id', $user['storage_id'] )
            -> where( 'coupon_type_id', $stock['coupon_type_id'] )
            -> where( 'status', CouponInfo::STATUS_INVENTORY )
            -> get()
            -> toArray();

        if( $num != count($coupons) )
        {
            $validator-> errors()-> add( 'error', '输入的券号有错误，请检查！' );
            return view( 'inventory.transfers_apply_edit', $param )-> withErrors( $validator );
        }

        if( $stock['amount_no_sale'] < $num )
        {
            $validator-> errors()-> add( 'error', '库存数量不足无法调拨' );
            return view( 'inventory.transfers_apply_edit', $param )-> withErrors( $validator );
        }

        DB::beginTransaction();
        $couponTransfers = new CouponTransfers();
        $couponTransfers-> node_id           = $user['node_id'];
        $couponTransfers-> seq               = getOperateSeq('调拨');
        $couponTransfers-> status            = CouponTransfers::STATUS_PENDING;
        $couponTransfers-> coupon_class_id   = $stock['coupon_class_id'];
        $couponTransfers-> coupon_class_name = $stock['coupon_class_name'];
        $couponTransfers-> coupon_type_id    = $stock['coupon_type_id'];
        $couponTransfers-> coupon_type_name  = $stock['coupon_type_name'];
        $couponTransfers-> amount            = $num;
        $couponTransfers-> start_flow_no     = $coupon['start'];
        $couponTransfers-> end_flow_no       = $coupon['end'];
        $couponTransfers-> from_storage_id   = $stock['storage_id'];
        $couponTransfers-> from_storage_name = $stock['storage_name'];
        $couponTransfers-> to_storage_id     = $coupon['storage_id'];
        $couponTransfers-> to_storage_name   = $coupon['storage_name'];
        $couponTransfers-> request_user_id   = $user['id'];
        $couponTransfers-> request_user_name = $user['nickname'];
        $couponTransfers-> request_time      = date('Y-m-d H:i:s');

        try
        {
            $couponTransfers-> save();
            CouponInfo::whereBetween( 'coupon_flow_no', [ $coupon['start'], $coupon['end'] ] )
                -> update([ 'status'=> CouponInfo::STATUS_PENDING ]);
            CouponStorageStock::where('id', $coupon['id'] )
                -> update([
                    'amount_no_sale'=> $stock['amount_no_sale'] - $num,
                    'amount_audit'  => $stock['amount_audit'] + $num
                ]);
            DB::commit();
            return redirect('/inventory/transfers/apply'.S::getUrlParam())-> with( promptMsg( '提交成功', 1 ));
        }
        catch ( \Exception $e )
        {
            DB::rollBack();
            $validator-> errors()-> add('error', $e-> getTraceAsString());
            return view( 'inventory.transfers_apply_edit', $param )-> withErrors( $validator );
        }
    }

    /**
     * 调拨审核列表
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function auditList()
    {
        $search        = Input::all();
        $userStorageId = session( 'user.storage_id' );
        $couponTransfers = CouponTransfers::join( 'storage as b', 'coupon_transfers.from_storage_id', '=', 'b.id' )
            -> whereRaw( "FIND_IN_SET( {$userStorageId}, lv_b.`full_id` )" )
            -> where( 'coupon_transfers.status', CouponTransfers::STATUS_PENDING )
            -> where( 'coupon_transfers.node_id', session( 'user.node_id' ))
            -> orderBy( 'seq', 'desc' )
            -> select( 'coupon_transfers.*' );

        empty( $search['class'] )     || $couponTransfers-> where('coupon_transfers.coupon_class_id', $search['class']);
        empty( $search['name'] )      || $couponTransfers-> where('coupon_transfers.coupon_type_id', $search['name']);
        empty( $search['from'] )      || $couponTransfers-> where('coupon_transfers.request_time', '>=', $search['from']);
        empty( $search['to'] )        || $couponTransfers-> where('coupon_transfers.request_time', '<=', $search['to']);
        empty( $search['from_id'] )   || $couponTransfers-> where('coupon_transfers.from_storage_id', $search['from_id']);
        empty( $search['to_id'] )     || $couponTransfers-> where('coupon_transfers.to_storage_id', $search['to_id']);
        $list = $couponTransfers-> paginate(15);
        return view('inventory.transfers_audit', [
            'list'             => $list,
            'class'            => CouponClassDao::getCouponClass(),
            'type'             => CouponTypeDao::getCouponType(),
            'storages'         => json_encode( StorageDao::getStorages(), JSON_UNESCAPED_UNICODE ),
            'storagesTransfers'=> json_encode( StorageDao::getTransfersStorages(), JSON_UNESCAPED_UNICODE ),
            'search'           => $search
        ]);
    }

    /**
     * 调拨审核详情
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function auditShow()
    {
        $id = Input::get('id');
        if( empty( $id )) abort( 403, '缺少ID' );
        S::setUrlParam();
        $userStorageId = session( 'user.storage_id' );
        $re = CouponTransfers::join( 'storage as b', 'coupon_transfers.from_storage_id', '=', 'b.id' )
            -> whereRaw( "FIND_IN_SET( {$userStorageId}, lv_b.`full_id` )" )
            -> where( 'coupon_transfers.node_id', session( 'user.node_id' ))
            -> where( 'coupon_transfers.status', CouponTransfers::STATUS_PENDING )
            -> where( 'coupon_transfers.id', $id )
            -> select( 'coupon_transfers.*' )
            -> first();
        if( empty( $re )) abort( 403, 'ID不存在' );

        return view('inventory.transfers_audit_show', ['coupon'=> $re-> toArray(), 'urlParam'=> S::getUrlParam()]);
    }

    /**
     * 调拨审核保存
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function auditSave()
    {
        $post = Input::all();
        S::setUrlParam();
        $validator = Validator::make( $post, [
            'id'   => 'required'
        ], [
            'id.required'   => '最少选择一条记录'
        ]);
        if( $validator-> fails() )
        {
            return redirect('/inventory/transfers/audit'.S::getUrlParam())-> withErrors( $validator );
        }
        $user = session('user');
        $data = CouponTransfers::join( 'storage as b', 'coupon_transfers.from_storage_id', '=', 'b.id' )
            -> whereRaw( "FIND_IN_SET( {$user['storage_id']}, lv_b.`full_id` )" )
            -> where( 'coupon_transfers.status', CouponTransfers::STATUS_PENDING )
            -> where( 'coupon_transfers.node_id', $user['node_id'] )
            -> whereIn( 'coupon_transfers.id', $post['id'] )
            -> select( 'coupon_transfers.*' )
            -> get()
            -> toArray();
        if( empty( $data )) abort( 403, 'ID不存在' );
        if( count( $post['id'] ) != count( $data ))
        {
            return redirect('/inventory/transfers/audit'.S::getUrlParam())-> with( promptMsg( '提交的单号中存在错误数据！', 3 ));
        }

        DB::beginTransaction();

        if( $post['action'] == 'pass' )//审核通过
        {
            try
            {
                CouponTransfers::whereIn( 'coupon_transfers.id', $post['id'] )-> update([
                    'status'           => CouponTransfers::STATUS_TRANSIT,
                    'approve_user_id'  => $user['id'],
                    'approve_user_name'=> $user['nickname'],
                    'approve_time'     => date('Y-m-d H:i:s')
                ]);
                foreach ( $data as $v )
                {
                    CouponStorageStock::where( 'node_id', $user['node_id'] )
                        -> where( 'storage_id', $v['from_storage_id'] )
                        -> where( 'coupon_type_id', $v['coupon_type_id'] )
                        -> update([
                            'amount_audit'    => DB::raw( "amount_audit - {$v['amount']}" ),
                            'amount_transfers'=> DB::raw( "amount_transfers + {$v['amount']}" )
                        ]);
                    //拼装coupon更新条件
                    if( !isset( $couponInfo ))
                        $couponInfo = CouponInfo::orWhereBetween( 'coupon_flow_no', [ $v['start_flow_no'], $v['end_flow_no'] ]);
                    else
                        $couponInfo-> orWhereBetween( 'coupon_flow_no', [ $v['start_flow_no'], $v['end_flow_no'] ]);
                }
                $couponInfo-> update([ 'status'=> CouponInfo::STATUS_TRANSIT ]);

                DB::commit();
                return redirect('/inventory/transfers/audit'.S::getUrlParam())-> with( promptMsg( '提交成功', 1 ));
            }
            catch ( \Exception $e )
            {
                DB::rollBack();
                return redirect('/inventory/transfers/audit'.S::getUrlParam())-> with( promptMsg( '数据保存失败', 4 ));
            }
        }
        elseif ( $post['action'] == 'no_pass' )//审核未通过
        {
            try
            {
                CouponTransfers::whereIn( 'coupon_transfers.id', $post['id'] )-> update([
                    'status'           => CouponTransfers::STATUS_NO_THROUGH,
                    'approve_user_id'  => $user['id'],
                    'approve_user_name'=> $user['nickname'],
                    'approve_time'     => date('Y-m-d H:i:s')
                ]);

                foreach ( $data as $v )
                {
                    CouponStorageStock::where( 'node_id', $user['node_id'] )
                        -> where( 'storage_id', $v['from_storage_id'] )
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
                return redirect('/inventory/transfers/audit'.S::getUrlParam())-> with( promptMsg( '提交成功', 1 ));
            }
            catch ( \Exception $e )
            {
                DB::rollBack();
                return redirect('/inventory/transfers/audit'.S::getUrlParam())-> with( promptMsg( '数据保存失败', 4 ));
            }
        }
    }

    /**
     * 调拨确认列表
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function confirm()
    {
        $search        = Input::all();
        $userStorageId = session( 'user.storage_id' );
        $couponTransfers = CouponTransfers::join( 'storage as b', 'coupon_transfers.to_storage_id', '=', 'b.id' )
            -> whereRaw( "FIND_IN_SET( {$userStorageId}, lv_b.`full_id` )" )
            -> where( 'coupon_transfers.status', CouponTransfers::STATUS_TRANSIT )
            -> where( 'coupon_transfers.node_id', session( 'user.node_id' ))
            -> orderBy( 'seq', 'desc' )
            -> select( 'coupon_transfers.*' );


        empty( $search['class'] )     || $couponTransfers-> where('coupon_transfers.coupon_class_id', $search['class']);
        empty( $search['name'] )      || $couponTransfers-> where('coupon_transfers.coupon_type_id', $search['name']);
        empty( $search['from'] )      || $couponTransfers-> where('coupon_transfers.request_time', '>=', $search['from']);
        empty( $search['to'] )        || $couponTransfers-> where('coupon_transfers.request_time', '<=', $search['to']);
        empty( $search['from_id'] )   || $couponTransfers-> where('coupon_transfers.from_storage_id', $search['from_id']);
        empty( $search['to_id'] )     || $couponTransfers-> where('coupon_transfers.to_storage_id', $search['to_id']);
        $list = $couponTransfers-> paginate(15);
        return view('inventory.transfers_confirm', [
            'list'             => $list,
            'class'            => CouponClassDao::getCouponClass(),
            'type'             => CouponTypeDao::getCouponType(),
            'storages'         => json_encode( StorageDao::getStorages(), JSON_UNESCAPED_UNICODE ),
            'storagesTransfers'=> json_encode( StorageDao::getTransfersStorages(), JSON_UNESCAPED_UNICODE ),
            'search'           => $search
        ]);
    }

    public function confirmShow()
    {
        $id = Input::get('id');
        if( empty( $id )) abort( 403, '缺少ID' );
        S::setUrlParam();
        $userStorageId = session( 'user.storage_id' );
        $re = CouponTransfers::join( 'storage as b', 'coupon_transfers.from_storage_id', '=', 'b.id' )
            -> whereRaw( "FIND_IN_SET( {$userStorageId}, lv_b.`full_id` )" )
            -> where( 'coupon_transfers.node_id', session( 'user.node_id' ))
            -> where( 'coupon_transfers.status', CouponTransfers::STATUS_TRANSIT )
            -> where( 'coupon_transfers.id', $id )
            -> select( 'coupon_transfers.*' )
            -> first();
        if( empty( $re )) abort( 403, 'ID不存在' );

        return view('inventory.transfers_confirm_show', ['coupon'=> $re-> toArray(), 'urlParam'=> S::getUrlParam()]);
    }

    /**
     * 调拨确认保存
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function confirmSave()
    {
        $post = Input::all();
        S::setUrlParam();
        $validator = Validator::make( $post, [
            'id'   => 'required'
        ], [
            'id.required'   => '最少选择一条记录'
        ]);
        if( $validator-> fails() )
        {
            return redirect('/inventory/transfers/confirm'.S::getUrlParam())-> withErrors( $validator );
        }
        $user = session('user');
        $data = CouponTransfers::join( 'storage as b', 'coupon_transfers.to_storage_id', '=', 'b.id' )
            -> whereRaw( "FIND_IN_SET( {$user['storage_id']}, lv_b.`full_id` )" )
            -> where( 'coupon_transfers.status', CouponTransfers::STATUS_TRANSIT )
            -> where( 'coupon_transfers.node_id', $user['node_id'] )
            -> whereIn( 'coupon_transfers.id', $post['id'] )
            -> select( 'coupon_transfers.*' )
            -> get()
            -> toArray();
        if( empty( $data )) abort( 403, 'ID不存在' );
        if( count( $post['id'] ) != count( $data ))
        {
            return redirect('/inventory/transfers/confirm'.S::getUrlParam())-> with( promptMsg( '提交的单号中存在错误数据！', 3 ));
        }

        DB::beginTransaction();

        if( $post['action'] == 'pass' )//确认通过
        {
            //查询库存表中是否存在该类型券的记录
            $storageIdArr = array_unique( array_column( $data, 'to_storage_id' ));
            $verifyArr    = [];
            foreach ( $storageIdArr as $v )
            {
                $verifyArr[$v] = CouponStorageStock::where( 'node_id', $user['node_id'] )
                    -> where( 'storage_id', $v )
                    -> pluck( 'coupon_type_id' )
                    -> toArray();
            }

            try
            {
                CouponTransfers::whereIn( 'coupon_transfers.id', $post['id'] )-> update([
                    'status'           => CouponTransfers::STATUS_AFFIRM_THROUGH,
                    'confirm_user_id'  => $user['id'],
                    'confirm_user_name'=> $user['nickname'],
                    'confirm_time'     => date('Y-m-d H:i:s')
                ]);
                foreach ( $data as $v )
                {
                    //来源仓库减少库存
                    CouponStorageStock::where( 'node_id', $user['node_id'] )
                        -> where( 'storage_id', $v['from_storage_id'] )
                        -> where( 'coupon_type_id', $v['coupon_type_id'] )
                        -> decrement( 'amount_transfers', $v['amount'] );

                    //目的仓库增加库存
                    if( in_array( $v['coupon_type_id'], $verifyArr[$v['to_storage_id']] ))//判断目的仓库是否存在记录
                    {
                        CouponStorageStock::where( 'node_id', $user['node_id'] )
                            -> where( 'storage_id', $v['to_storage_id'] )
                            -> where( 'coupon_type_id', $v['coupon_type_id'] )
                            -> increment( 'amount_no_sale', $v['amount'] );
                    }
                    else//不存在记录就要新增
                    {
                        $param = [
                            'node_id'          => $user['node_id'],
                            'storage_id'       => $v['to_storage_id'],
                            'storage_name'     => $v['to_storage_name'],
                            'coupon_class_id'  => $v['coupon_class_id'],
                            'coupon_class_name'=> $v['coupon_class_name'],
                            'coupon_type_id'   => $v['coupon_type_id'],
                            'coupon_type_name' => $v['coupon_type_name'],
                            'amount_no_sale'   => $v['amount']
                        ];
                        CouponStorageStock::insert($param);
                        $verifyArr[$v['to_storage_id']][] = $v['coupon_type_id'];
                    }
                    //券状态变更，拼装coupon更新条件
                    if( !isset( $couponInfo ))
                        $couponInfo = CouponInfo::orWhereBetween( 'coupon_flow_no', [ $v['start_flow_no'], $v['end_flow_no'] ]);
                    else
                        $couponInfo-> orWhereBetween( 'coupon_flow_no', [ $v['start_flow_no'], $v['end_flow_no'] ]);
                }
                $couponInfo-> update([
                    'status'      => CouponInfo::STATUS_INVENTORY,
                    'storage_id'  => $v['to_storage_id'],
                    'storage_name'=> $v['to_storage_name'],
                ]);
                DB::commit();
                return redirect('/inventory/transfers/confirm'.S::getUrlParam())-> with( promptMsg( '提交成功', 1 ));
            }
            catch ( \Exception $e )
            {
                DB::rollBack();
                return redirect('/inventory/transfers/confirm'.S::getUrlParam())-> with( promptMsg( '数据保存失败', 4 ));
            }
        }
        elseif ( $post['action'] == 'no_pass' )//确认未通过
        {
            try
            {
                //调拨记录保存
                CouponTransfers::whereIn( 'coupon_transfers.id', $post['id'] )-> update([
                    'status'           => CouponTransfers::STATUS_AFFIRM_NO_THROUGH,
                    'confirm_user_id'  => $user['id'],
                    'confirm_user_name'=> $user['nickname'],
                    'confirm_time'     => date('Y-m-d H:i:s')
                ]);

                foreach ( $data as $v )
                {
                    //库存回退
                    CouponStorageStock::where( 'node_id', $user['node_id'] )
                        -> where( 'storage_id', $v['from_storage_id'] )
                        -> where( 'coupon_type_id', $v['coupon_type_id'] )
                        -> update([
                            'amount_transfers'=> DB::raw( "amount_transfers - {$v['amount']}" ),
                            'amount_no_sale'  => DB::raw( "amount_no_sale + {$v['amount']}" )
                        ]);
                    //券状态变更，拼装coupon更新条件
                    if( !isset( $couponInfo ))
                        $couponInfo = CouponInfo::orWhereBetween( 'coupon_flow_no', [ $v['start_flow_no'], $v['end_flow_no'] ]);
                    else
                        $couponInfo-> orWhereBetween( 'coupon_flow_no', [ $v['start_flow_no'], $v['end_flow_no'] ]);
                }
                $couponInfo-> update([ 'status'=> CouponInfo::STATUS_INVENTORY ]);
                DB::commit();
                return redirect('/inventory/transfers/confirm'.S::getUrlParam())-> with( promptMsg( '提交成功', 1 ));
            }
            catch ( \Exception $e )
            {
                DB::rollBack();
                return redirect('/inventory/transfers/confirm'.S::getUrlParam())-> with( promptMsg( '数据保存失败', 4 ));
            }
        }
    }
    public function searchList()
    {
        $search = Input::all();
        $user   = session( 'user' );
        if( !isset( $search['status'] )) $search['status'] = CouponTransfers::STATUS_AFFIRM_THROUGH;

        $couponTransfers = CouponTransfers::join( 'storage as b', 'coupon_transfers.to_storage_id', '=', 'b.id' )
            -> where( function ($query) use( $user ){
                $query-> orWhereRaw( "FIND_IN_SET( {$user['storage_id']}, lv_b.`full_id` )" )
                    -> orWhere( 'from_storage_id', $user['storage_id'] );
            })
            -> where( 'coupon_transfers.node_id', $user['node_id'] )
            -> select( 'coupon_transfers.*' )
            -> orderBy( 'seq', 'desc' );

        empty( $search['class'] )     || $couponTransfers-> where('coupon_transfers.coupon_class_id', $search['class']);
        empty( $search['name'] )      || $couponTransfers-> where('coupon_transfers.coupon_type_id', $search['name']);
        empty( $search['from'] )      || $couponTransfers-> where('coupon_transfers.request_time', '>=', $search['from']);
        empty( $search['to'] )        || $couponTransfers-> where('coupon_transfers.request_time', '<=', $search['to']);
        empty( $search['from_id'] )   || $couponTransfers-> where('coupon_transfers.from_storage_id', $search['from_id']);
        empty( $search['to_id'] )     || $couponTransfers-> where('coupon_transfers.to_storage_id', $search['to_id']);
        empty( $search['status'] )    || $couponTransfers-> where('coupon_transfers.status', $search['status']);
        $list = $couponTransfers-> paginate(15);
        return view('inventory.transfers_search', [
            'list'             => $list,
            'status'           => SystemStatusDao::getCouponTransfers(),
            'class'            => CouponClassDao::getCouponClass(),
            'type'             => CouponTypeDao::getCouponType(),
            'storages'         => json_encode( StorageDao::getStorages(), JSON_UNESCAPED_UNICODE ),
            'storagesTransfers'=> json_encode( StorageDao::getTransfersStorages(), JSON_UNESCAPED_UNICODE ),
            'search'           => $search
        ]);
    }

    public function searchShow()
    {
        $id = Input::get('id');
        if( empty( $id )) abort( 403, '缺少ID' );
        S::setUrlParam();
        $user = session( 'user' );
        $re = CouponTransfers::join( 'storage as b', 'coupon_transfers.to_storage_id', '=', 'b.id' )
            -> where( function ($query) use( $user ){
                $query-> orWhereRaw( "FIND_IN_SET( {$user['storage_id']}, lv_b.`full_id` )" )
                    -> orWhere( 'from_storage_id', $user['storage_id'] );
            })
            -> where( 'coupon_transfers.node_id', $user['node_id'] )
            -> where( 'coupon_transfers.id', $id )
            -> select( 'coupon_transfers.*' )
            -> first();
        if( empty( $re )) abort( 403, 'ID不存在' );

        return view('inventory.transfers_search_show', [
            'coupon'  => $re-> toArray(),
            'status'  => SystemStatusDao::getCouponTransfers(),
            'urlParam'=> S::getUrlParam()
        ]);
    }
}