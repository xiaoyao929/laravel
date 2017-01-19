<?php

namespace App\Http\Controllers\Fallback;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\{Input,DB};
use Lib\PublicClass\{S};
use App\Dao\{CouponTypeDao,CouponClassDao,StorageDao,SupportDao,CustomerTypeDao,SystemStatusDao};
use App\Model\{CouponInfo,CouponFallback,CouponFallbackSale,CouponStorageStock};

/**
 * 退券
 */
class FallbackController extends Controller
{
    //退券列表
    public function list()
    {
        S::setUrlParam();
        $select = [
                'coupon_class'         => '',
                'coupon_type'          => '',
                'request_storage_id'   => '',
                'request_storage_name' => '',
                'customer_type'        => '',
                'customer_name'        => '',
                'fallback_seq'         => '',
                'sale_seq'             => '',
                'status'               => '',
                'from'                 => '',
                'to'                   => ''
        ];
        $sessionData = session('user');
        $search = array_merge($select,Input::all());

        $list = CouponFallback::join('coupon_fallback_sale as b','coupon_fallback.seq','=','b.fallback_seq')
                ->join('coupon_sale as c','b.sale_seq','=','c.seq')
                ->join('storage as d','coupon_fallback.request_storage_id','=','d.id')
                ->whereRaw( "FIND_IN_SET( {$sessionData['storage_id']}, lv_d.`full_id` )" )
                ->where('coupon_fallback.node_id',$sessionData['node_id'])
                ->select('coupon_fallback.*','b.status','b.fallback_amount','b.start_flow_no as fallback_start_flow_no','b.end_flow_no as fallback_end_flow_no','c.seq as sale_seq','c.customer_type','c.customer_name','c.price','c.sale_price')
                ->orderBy('coupon_fallback.request_time','desc');
        //券类别
        if(!empty($search['coupon_class'])){
            $list->where('coupon_fallback.coupon_class_id',$search['coupon_class']);
        }
        //券简称
        if(!empty($search['coupon_type'])){
            $list->where('coupon_fallback.coupon_type_id',$search['coupon_type']);
        }
        //申请仓库
        if(!empty($search['request_storage_id'])){
            $list->where('coupon_fallback.request_storage_id',$search['request_storage_id']);
        }
        //客户类型
        if(!empty($search['customer_type'])){
            $list->where('c.customer_type',$search['customer_type']);
        }
        //部门/客户名称
        if(!empty($search['customer_name'])){
            $list->where('c.customer_name',$search['customer_name']);
        }
        //退券单号
        if(!empty($search['fallback_seq'])){
            $list->where('coupon_fallback.seq',$search['fallback_seq']);
        }
        //销售单号
        if(!empty($search['sale_seq'])){
            $list->where('c.seq',$search['sale_seq']);
        }
        //状态
        if(!empty($search['status'])){
            $list->where('b.status',$search['status']);
        }
        //申请时间
        if(!empty($search['from']) && !empty($search['to'])){
            $list->whereBetween('coupon_fallback.request_time',[$search['from'],$search['to']]);
        }
        $list = $list->paginate(15);

        $param = [
                'list'        => $list,
                'status'      => SystemStatusDao::getcouponFallback(),    //审核状态
                'search'      => $search,
                'customerType'=> CustomerTypeDao::getInfo(),
                'couponClass' => CouponClassDao::getCouponClass(),
                'couponType'  => CouponTypeDao::getCouponType(),
                'storageList' => json_encode(StorageDao::getStorages(), JSON_UNESCAPED_UNICODE)
        ];
        return view('fallback.fallback_list',$param);
    }
    //退券申请
    public function edit()
    {
        $rowData = [];
        $reason = [
            '1'     => '券质量问题',
            '2'     => '客户无条件退券',
            '3'     => '其他'
        ];

        return view('fallback.fallback_edit',['rowData'=>$rowData,'reason'=>$reason,'couponType'=>CouponTypeDao::getCouponType()]);
    }
    //退券申请提交地址
    public function save()
    {
        $htmlCommitData = Input::all();
        $sessionData = session('user');
        $reason = [
                '1'     => '券质量问题',
                '2'     => '客户无条件退券',
                '3'     => '其他'
        ];
        $errStr = '';
        //券检测
        $couponList = CouponInfo::leftJoin('coupon_sale_info',function($query){
                        $query->on('coupon_info.coupon_flow_no','>=','coupon_sale_info.start_flow_no');
                        $query->on('coupon_info.coupon_flow_no','<=','coupon_sale_info.end_flow_no');
                    })
                    ->join('coupon_class as d','coupon_info.coupon_class_id','=','d.id')
                    ->join('storage as c','coupon_info.storage_id','=','c.id')
                    ->whereBetween('coupon_info.coupon_flow_no',[$htmlCommitData['start_flow_no'],$htmlCommitData['end_flow_no']])
                    ->where('coupon_info.status','2')   //已销售
                    ->where('d.id',1)                   //GC券
                    ->where('coupon_info.node_id',$sessionData['node_id'])
                    ->whereRaw("FIND_IN_SET( {$sessionData['storage_id']}, lv_c.`full_id` )")
                    ->where('coupon_info.coupon_type_id',$htmlCommitData['coupon_type_id'])
                    ->select('coupon_info.*','coupon_sale_info.seq as sale_seq')
                    ->get();
        $num = ($htmlCommitData['end_flow_no']-$htmlCommitData['start_flow_no']+1);
        //其一防止重复提交
        if(count($couponList) == '0' || count($couponList) != $num){
            $errStr = '输入的券号有错误，请检查！';
        }
        if($htmlCommitData['reason_type'] == '3' && empty($htmlCommitData['reason_content'])){
            $errStr = '请填写退券原因';
        }
        if(!empty($errStr)){
            return view('fallback.fallback_edit',['rowData'=>$htmlCommitData,'reason'=>$reason,'couponType'=>CouponTypeDao::getCouponType()])->withErrors( $errStr,3);;
        }
        //退券总表
        $couponFallback = new CouponFallback();
        $couponFallback->node_id                = $sessionData['node_id'];
        $couponFallback->seq                    = getOperateSeq('退券');
        $couponFallback->coupon_class_id        = $couponList[0]->coupon_class_id;
        $couponFallback->coupon_class_name      = $couponList[0]->coupon_class_name;
        $couponFallback->coupon_type_id         = $couponList[0]->coupon_type_id;
        $couponFallback->coupon_type_name       = $couponList[0]->coupon_type_name;
        $couponFallback->request_storage_id     = $sessionData['storage_id'];
        $couponFallback->request_storage_name   = $sessionData['storage_name'];
        $couponFallback->request_user_id        = $sessionData['id'];
        $couponFallback->request_user_name      = $sessionData['nickname'];
        $couponFallback->request_time           = date('Y-m-d H:i:s');
        $couponFallback->start_flow_no          = $htmlCommitData['start_flow_no'];
        $couponFallback->end_flow_no            = $htmlCommitData['end_flow_no'];
        $couponFallback->reason_type            = $htmlCommitData['reason_type'];
        $couponFallback->reason_content         = $htmlCommitData['reason_content'];
//        $couponFallback->status                 = 1;
        $couponFallback->memo                   = $htmlCommitData['memo'];
        $couponFallback->amount                 = $num;

        DB::beginTransaction();
        $result = $couponFallback->save();
        if($result){
            //分支表数据
            $insert = [];
            foreach ($couponList as $key => $value){
                if(!isset($insert[$value->sale_seq])){
                    $insert[$value->sale_seq] = [
                            'node_id'           => $sessionData['node_id'],
                            'fallback_seq'      => $couponFallback->seq,
                            'sale_seq'          => $value->sale_seq,
                            'status'            => 1,
                            'fallback_amount'   => 1,
                            'start_flow_no'     => $value->coupon_flow_no,      //销售单号对应退券的开始号段
                            'end_flow_no'       => $value->coupon_flow_no,      //销售单号对应退券的结束号段

                    ];
                }else{
                    $insert[$value->sale_seq]['fallback_amount'] += 1;
                    $insert[$value->sale_seq]['start_flow_no'] = ($insert[$value->sale_seq]['start_flow_no']-$value->coupon_flow_no) > 0 ? $value->coupon_flow_no : $insert[$value->sale_seq]['start_flow_no'];
                    $insert[$value->sale_seq]['end_flow_no'] = ($insert[$value->sale_seq]['end_flow_no']-$value->coupon_flow_no) > 0 ? $insert[$value->sale_seq]['end_flow_no'] : $value->coupon_flow_no;
                }
            }
            $result = CouponFallbackSale::insert($insert);
            if(!$result){
                DB::rollBack();
                abort(403,'保存异常');
            }else{
                //修改券状态为审核中
                try{
                    CouponInfo::where('node_id',$sessionData['node_id'])
                        ->whereBetween('coupon_flow_no',[$htmlCommitData['start_flow_no'],$htmlCommitData['end_flow_no']])
                        ->update(['status'=>6]);
                }catch(\ Exception $e){
                    DB::rollBack();
                    abort(403,'保存异常');
                }
                //券的库存变动
                try{
                    CouponStorageStock::where('node_id',$value['node_id'])
                            ->where('storage_id',$sessionData['storage_id'])
                            ->where('coupon_type_id',$couponList[0]->coupon_type_id)
                            ->update([
                                    'amount_audit'    => DB::raw( "amount_audit + {$num}" ),
                                    'amount_saled'    => DB::raw( "amount_saled - {$num}" ),
                            ]);
                }catch( \Exception $e){
                    DB::rollBack();
                    abort(403, '修改库存异常');
                }

            }
            DB::commit();
        }else{
            DB::rollBack();
            abort(403,'保存异常');
        }

        return redirect('/exchange/fallback/edit')->with(promptMsg('申请成功', 1));
    }

    //退券审核列表
    public function auditList()
    {
        S::setUrlParam();
        $select = [
                'coupon_type'          => '',
                'request_storage_id'   => '',
                'request_storage_name' => '',
                'customer_type'        => '',
                'customer_name'        => '',
                'fallback_seq'         => '',
                'from'                 => '',
                'to'                   => ''
        ];
        $sessionData = session('user');
        $search = array_merge($select,Input::all());
        
        $list = CouponFallback::join('coupon_fallback_sale as b','coupon_fallback.seq','=','b.fallback_seq')
                ->join('coupon_sale as c','b.sale_seq','=','c.seq')
                ->join('storage as d','coupon_fallback.request_storage_id','=','d.id')
                ->whereRaw( "FIND_IN_SET( {$sessionData['storage_id']}, lv_d.`full_id` )" )
                ->where('coupon_fallback.node_id',$sessionData['node_id'])
                ->where('b.status',CouponFallback::STATUS_PENDING)
                ->select('coupon_fallback.*','b.status','b.fallback_amount','b.start_flow_no as fallback_start_flow_no','b.end_flow_no as fallback_end_flow_no','c.seq as sale_seq','c.customer_type','c.customer_name','c.price','c.sale_price')
                ->orderBy('coupon_fallback.request_time','desc');

        //券简称
        if(!empty($search['coupon_type'])){
            $list->where('coupon_fallback.coupon_type_id',$search['coupon_type']);
        }
        //申请仓库
        if(!empty($search['request_storage_id'])){
            $list->where('coupon_fallback.request_storage_id',$search['request_storage_id']);
        }
        //客户类型
        if(!empty($search['customer_type'])){
            $list->where('c.customer_type',$search['customer_type']);
        }
        //部门/客户名称
        if(!empty($search['customer_name'])){
            $list->where('c.customer_name',$search['customer_name']);
        }
        //退券单号
        if(!empty($search['fallback_seq'])){
            $list->where('coupon_fallback.seq',$search['fallback_seq']);
        }
        //申请日期
        if(!empty($search['from']) && !empty($search['to'])){
            $list->whereBetween('coupon_fallback.request_time',[$search['from'],$search['to']]);
        }
        $list = $list->paginate(15);

        $param = [
                'search'       => $search,
                'list'         => $list,
                'couponType'   => CouponTypeDao::getCouponType(),
                'couponClass'  => CouponClassDao::getCouponClass(),
                'customerType' => CustomerTypeDao::getInfo(),
                'storageList'  => json_encode(StorageDao::getStorages(), JSON_UNESCAPED_UNICODE),
        ];

        return view('fallback.audit_list',$param);
    }
    //退券详情
    public function auditShow()
    {
        $htmlCommitData = Input::all();
        $sessionData = session('user');
        $from = Input::get('from',0);
        if(empty($htmlCommitData['id']) || empty($htmlCommitData['sale_seq'])){
            abort(403,'异常提交');
        }

        $detail = CouponFallback::join('coupon_fallback_sale as b','coupon_fallback.seq','=','b.fallback_seq')
                ->join('coupon_sale as c','b.sale_seq','=','c.seq')
                ->where('coupon_fallback.node_id',$sessionData['node_id'])
                ->where('coupon_fallback.id',$htmlCommitData['id'])
                ->where('b.sale_seq',$htmlCommitData['sale_seq'])
                ->select('coupon_fallback.*','b.status','b.approve_storage_name','b.approve_user_name','b.approve_time','c.seq as sale_seq','c.customer_type','c.customer_info','c.pay_type','c.memo as sale_memo','c.gc_amount','c.price','c.sale_price','c.bog_amount')
                ->first();

        //退券原因
        $reasonType = [
            '1'     => '券质量问题',
            '2'     => '客户无条件退券',
            '3'     => '其他'
        ];
        //客户信息
        $customerInfo = json_decode($detail->customer_info,1);
        $param = ['detail'          => $detail,
                  'customerInfo'    => $customerInfo,
                  'from'            => $from,
                  'certificateType' => CustomerTypeDao::getCertificate(),       //证件类型
                  'reasonType'      => $reasonType,
                  'status'          => SystemStatusDao::getcouponFallback(),    //审核状态
                  'customerType'    => CustomerTypeDao::getInfo(),
                  'urlParam'       => S::getUrlParam()
        ];
        return view('fallback.audit_show',$param);
    }
    //退券审核提交地址
    public function auditSave()
    {
        $htmlCommitData = Input::all();
        $sessionData = session('user');
        //数据提交验证
        if(!in_array($htmlCommitData['pass'],['0','1']) || !isset($htmlCommitData['pass']) || empty($htmlCommitData['select_list'])){
            return redirect('/exchange/fallback/audit/list'.S::getUrlParam())-> with( promptMsg( '错误提交', 3 ));
        }else {
            $allFallbackId = [];
            $allSaleId = [];
            foreach ($htmlCommitData['select_list'] as $key => $value){
                $d = explode('-',$value);
                $allSaleId[] = $d[0];
                $allFallbackId[] = $d[1];
            }
            $htmlCommitData['select_list'] = $allSaleId;

            $list = CouponFallback::join('coupon_fallback_sale as b','coupon_fallback.seq','=','b.fallback_seq')
                    ->where('coupon_fallback.node_id', $sessionData['node_id'])
                    ->whereIn('b.sale_seq',array_values($htmlCommitData['select_list']))
                    ->whereIn('coupon_fallback.id',array_values($allFallbackId))
                    ->where('b.status', 1)
                    ->select('coupon_fallback.*','b.id as b_id','b.fallback_amount','b.start_flow_no as fallback_start_flow_no','b.end_flow_no as fallback_end_flow_no')
                    ->get();

            //提交的数据合理性检测
            if (count($list) != count($htmlCommitData['select_list']) || count($htmlCommitData['select_list']) == '0') {
                return redirect('/exchange/fallback/audit/list' . S::getUrlParam())->with(promptMsg('部分已被审核，请重新提交', 3));
            }
            //通过
            if ($htmlCommitData['pass'] == '1') {
                $status = CouponFallback::STATUS_TRANSIT;
            } else {      //不通过
                $status = CouponFallback::STATUS_NO_THROUGH;
            }

            $saveData = [
                    'approve_storage_id'   => $sessionData['storage_id'],
                    'approve_storage_name' => $sessionData['storage_name'],
                    'approve_user_id'      => $sessionData['id'],
                    'approve_user_name'    => $sessionData['nickname'],
                    'approve_time'         => date('Y-m-d H:i:s'),
                    'status'               => $status
            ];
            //退券总表id
            $couponFallbackId = [];
            //退券关联的分支表id
            $bId = [];
            foreach ($list as $key => $value){
                $couponFallbackId[$value->seq] = $value->id;
                $bId[$value->b_id] = [
                    'start_flow_no'     => $value->start_flow_no,
                    'amount'            => $value->amount
                ];
            }

            $result   = false;
            DB::beginTransaction();

            /*
            //修改总表
            try {
                CouponFallback::where('node_id', $sessionData['node_id'])
                        ->whereIn('id',array_values($couponFallbackId))
                        ->update($saveData);
            } catch (\Exception $e) {
                DB::rollBack();
                abort(403, '写入数据库异常[01]');
            }
            */
            //修改分支表
            try {
                $result = CouponFallbackSale::where('node_id', $sessionData['node_id'])
                        ->whereIn('id',array_keys($bId))
                        ->update($saveData);
            } catch (\Exception $e) {
                DB::rollBack();
                abort(403, '写入数据库异常[1]');
            }
            if ($result == count($htmlCommitData['select_list'])) {
                //每种券影响的数量
                $couponNum = [];
                //审核通过时候发往支撑的数据
                $item = [];
                foreach ($list as $key => $value) {
                    $item[] = [
                        'start_flow_no' => $value->fallback_start_flow_no,
                        'amount'        => $value->fallback_amount,
                    ];

                    //存数据，用来修改库存
                    if(isset($couponNum[$value->coupon_type_id])){
                        $couponNum[$value->coupon_type_id]['amount'] += $value->fallback_amount;
                    }else{
                        $couponNum[$value->coupon_type_id] = [
                                'amount'        => $value->fallback_amount,
                                'storage_id'    => $value->request_storage_id,
                                'node_id'       => $value->node_id
                        ];
                    }
                }
                if($htmlCommitData['pass'] == '1'){         //审核通过
                    //修改券状态为已作废
                    try{
                        CouponInfo::where('node_id',$list[0]->node_id)->where(function ($query) use ($list){
                            foreach ($list as $key => $value){
                                $query->orWhereBetween('coupon_flow_no',[$value->fallback_start_flow_no,$value->fallback_end_flow_no]);
                            }
                        })->update(['status'=>3]);
                    }catch( \Exception $e){
                        DB::rollBack();
                        abort(403, '修改券状态异常');
                    }

                    //券库存计数表修改
                    foreach($couponNum as $key => $value){
                        try{
                            CouponStorageStock::where('node_id',$value['node_id'])
                                    ->where('storage_id',$value['storage_id'])
                                    ->where('coupon_type_id',$key)
                                    ->update([
                                            'amount_audit'    => DB::raw( "amount_audit - {$value['amount']}" ),
                                            'amount_destroyed'=> DB::raw( "amount_destroyed + {$value['amount']}" )
                                    ]);
                        }catch( \Exception $e){
                            DB::rollBack();
                            abort(403, '修改库存异常');
                        }
                    }

                    $rsd    = [
                            'node_id' => $sessionData['node_id'],
                            'item'    => $item
                    ];
                    $result = SupportDao::voidCoupon($rsd);
                    if ($result['Status']['StatusCode'] != '0000') {
                        DB::rollBack();
                        abort(403, '支撑异常返回');
                    }
                }else{      //审核不通过就恢复券状态
                    try{
                        $result = CouponInfo::where(function ($query) use ($list){
                            foreach ($list as $key => $value){
                                $query->orWhereBetween('coupon_flow_no',[$value->fallback_start_flow_no,$value->fallback_end_flow_no]);
                            }
                        })->update(['status'=>CouponInfo::STATUS_SALES]);
                    }catch( \Exception $e){
                        DB::rollBack();
                        abort(403, '写入数据库异常[3]');
                    }
                    //券库存计数表修改
                    foreach($couponNum as $key => $value){
                        try{
                            CouponStorageStock::where('node_id',$value['node_id'])
                                    ->where('storage_id',$value['storage_id'])
                                    ->where('coupon_type_id',$key)
                                    ->update([
                                            'amount_saled'    => DB::raw( "amount_saled + {$value['amount']}" ),
                                            'amount_audit'=> DB::raw( "amount_audit - {$value['amount']}" )
                                    ]);
                        }catch( \Exception $e){
                            DB::rollBack();
                            abort(403, '修改库存异常');
                        }
                    }

                }

            } else {
                DB::rollBack();
                abort(403, '写入数据库比对异常');
            }

            DB::commit();
        }
        return redirect('/exchange/fallback/audit/list')-> with( promptMsg( '操作成功', 1 ));
    }




}