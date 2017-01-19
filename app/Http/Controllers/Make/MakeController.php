<?php

namespace App\Http\Controllers\Make;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\{Input,DB};
use Lib\PublicClass\{S};
use Validator;

use App\Model\{CouponMake,CouponType};
use App\Dao\{StorageDao,SupportDao,SystemStatusDao,CouponClassDao,CouponTypeDao};

class MakeController extends Controller
{
    /**
     * 制券记录列表
     */
    public function list(){
        S::setUrlParam();
        $sessionData = session('user');
        $select = [
                'class_id'           => '',
                'type_id'            => '',
                'seq'                => '',
                'from'               => '',
                'to'                 => '',
                'request_storage_id' => '',
                'storage_name'       => '',
                'status'             => '6'
        ];
        $search = array_merge($select,Input::all());
        //审核状态
        $status = SystemStatusDao::getCouponMake();
        //券种类别
        $class = CouponClassDao::getCouponClass();
        //券简称
        $couponType = CouponTypeDao::getCouponType('make');
        $param = [
                'list'       => (object)null,
                'status'     => $status,
                'class'      => $class,
                'search'     => $search,
                'couponType' => $couponType,
                'select'     => json_encode(StorageDao::getStorages(), JSON_UNESCAPED_UNICODE)
        ];
        //申请日期,提前检测
        $searchTime = false;
        if(!empty($search['from']) && !empty($search['to'])){
            if($search['from'] >= $search['to']){
                return view('make.make_list',$param)->withErrors( '申请日期错误' );
            }else{
                $searchTime = true;
            }
        }else{
            if(empty($search['from']) && !empty($search['to']) || !empty($search['from']) && empty($search['to'])){
                return view('make.make_list',$param)->withErrors( '申请日期错误' );
            }
        }

        $list = CouponMake::join('storage as b','coupon_make.request_storage_id','=','b.id')
                ->where('coupon_make.node_id',$sessionData['node_id'])
                ->whereRaw( "FIND_IN_SET( {$sessionData['storage_id']}, lv_b.`full_id` )" )
                ->select('coupon_make.*')
                ->orderBy('coupon_make.request_time','desc');

        //券类别
        if(!empty($search['class_id'])){
            $list->where('coupon_make.coupon_class_id',$search['class_id']);
        }
        //券简称
        if(!empty($search['type_id'])){
            $list->where('coupon_make.coupon_type_id',$search['type_id']);
        }
        //制券单号
        if(!empty($search['seq'])){
            $list->where('coupon_make.seq',$search['seq']);
        }
        //申请日期
        if($searchTime){
            $list->whereBetween('coupon_make.request_time',[date('YmdHis',strtotime($search['from'])),date('YmdHis',strtotime($search['to']))]);
        }
        //审核状态
        if(!empty($search['status'])){
            $list->where('coupon_make.status',$search['status']);
        }
        //申请仓库
        if(!empty($search['request_storage_id'])){
            $list->where('coupon_make.request_storage_id',$search['request_storage_id']);
        }
        $list = $list->paginate(15);

        $param['list'] = $list;
        return view('make.make_list',$param);
    }

    /**
     * 制券申请 （新增/修改）
     */
    public function edit(){
        //券种
        $typeNames = CouponTypeDao::getCouponType('make');
        return view('make.make_edit',['typeName'=>$typeNames,'rowMake'=>[]]);
    }
    /**
     * 制券申请提交
     */
    public function save(){
        $htmlCommitData = Input::all();
        $htmlCommitData['amount'] = (int)$htmlCommitData['amount'];

        $couponType = explode('_',$htmlCommitData['coupon_type']);
        $typeId     = $couponType['0'];
        //券种
        $typeNames = CouponTypeDao::getCouponType('make');

        $type      = CouponType::join('coupon_class as b','coupon_type.class_id','=','b.id')
            -> where( 'b.status','1' )
            -> where( 'coupon_type.id', $typeId )
            -> select( 'coupon_type.*' )
            -> first();

        if( empty( $type ))
        {
            return view('make.make_edit',['rowMake'=>$htmlCommitData,'typeName'=>$typeNames])->withErrors( '券种不存在' );
        }

        //数量检测
        if($htmlCommitData['amount'] < 1){
            return view('make.make_edit',['rowMake'=>$htmlCommitData,'typeName'=>$typeNames])->withErrors( '制券数量错误' );
        }

        $CouponMakeModel = new CouponMake();
        //默认数据
        $CouponMakeModel->status                 = '1';                  //审核状态
        $CouponMakeModel->print_flag             = '0';                  //印刷状态
        //额外的基本信息（申请人、所属仓库等）
        $sessionData = session('user');
        $CouponMakeModel->request_user_id        = $sessionData['id'];           //用户id
        $CouponMakeModel->request_user_name      = $sessionData['nickname'];     //用户姓名
        $CouponMakeModel->request_storage_id     = $sessionData['storage_id'];   //所属仓库id
        $CouponMakeModel->request_storage_name   = $sessionData['storage_name']; //所属仓库名称
        $CouponMakeModel->node_id                = $sessionData['node_id'];      //所属商户id
        $CouponMakeModel->request_time           = date('Y-m-d H:i:s');          //申请时间
        //制券申请的数据
        $CouponMakeModel->coupon_type_id    = $couponType['0'];               //券种id
        $CouponMakeModel->coupon_type_name  = $couponType['1'];               //券种名称
        $CouponMakeModel->coupon_class_id   = $type-> class_id;               //券类别id
        $CouponMakeModel->coupon_class_name = $type-> class_name;             //券类别名称
        $CouponMakeModel->amount            = $htmlCommitData['amount'];      //申请制券的数量
        $CouponMakeModel->memo              = $htmlCommitData['memo'];        //备注
        $CouponMakeModel->begin_time        = $htmlCommitData['start_time'].' 00:00:00';                      //有效期开始
        $CouponMakeModel->end_time          = $htmlCommitData['end_time'].' 23:59:59';         //有效期结束
        $CouponMakeModel->seq               = getOperateSeq('制券');                            //制券单号
        //获取券号段
        $couponCode = getCardSeq($htmlCommitData['amount']);
        $CouponMakeModel->start_flow_no    = $couponCode['start'];     //券开始号段
        $CouponMakeModel->end_flow_no      = $couponCode['end'];     //券结束号段

        //入库
        $result = $CouponMakeModel->save();
        if($result){
            return redirect('/make/edit'.S::getUrlParam())-> with( promptMsg( '申请成功', 1 ));
        }else{
            return redirect('/make/edit'.S::getUrlParam())-> with( promptMsg( '申请失败', 3 ));
        }

    }

    /**
     * 审核列表
     */
    public function auditList(){
        S::setUrlParam();
        $select = [
                'class_id'           => '',
                'type_id'            => '',
                'seq'                => '',
                'from'               => '',
                'to'                 => '',
                'request_storage_id' => '',
                'storage_name'       => ''
        ];
        $sessionData = session('user');
        $search = array_merge($select,Input::all());

        //券种类别
        $class = CouponClassDao::getCouponClass();
        //券简称
        $couponType = CouponTypeDao::getCouponType('make');

        $param = [
                'list'       => (object)null,
                'class'      => $class,
                'search'     => $search,
                'couponType' => $couponType,
                'select'     => json_encode(StorageDao::getStorages(), JSON_UNESCAPED_UNICODE)
        ];

        //申请日期,提前检测
        $searchTime = false;
        if(!empty($search['from']) && !empty($search['to'])){
            if($search['from'] >= $search['to']){
                return view('make.audit_list',$param)->withErrors( '申请日期错误' );
            }else{
                $searchTime = true;
            }
        }else{
            if(empty($search['from']) && !empty($search['to']) || !empty($search['from']) && empty($search['to'])){
                return view('make.audit_list',$param)->withErrors( '申请日期错误' );
            }
        }

        $list = CouponMake::join('storage as b','coupon_make.request_storage_id','=','b.id')
                ->where('coupon_make.node_id',$sessionData['node_id'])
                ->where('coupon_make.status','1')
                ->whereRaw( "FIND_IN_SET( {$sessionData['storage_id']}, lv_b.`full_id` )" )
                ->select('coupon_make.*')
                ->orderBy('coupon_make.request_time','desc');

        //券类别
        if(!empty($search['class_id'])){
            $list->where('coupon_make.coupon_class_id',$search['class_id']);
        }
        //券简称
        if(!empty($search['type_id'])){
            $list->where('coupon_make.coupon_type_id',$search['type_id']);
        }
        //制券单号
        if(!empty($search['seq'])){
            $list->where('coupon_make.seq',$search['seq']);
        }
        //申请日期
        if($searchTime){
            $list->whereBetween('coupon_make.request_time',[date('Y-m-d H:i:s',strtotime($search['from'])),date('Y-m-d H:i:s',strtotime($search['to']))]);
        }

        //申请仓库
        if(!empty($search['request_storage_id'])){
            $list->where('coupon_make.request_storage_id',$search['request_storage_id']);
        }
        $list = $list->paginate(15);

        $param['list'] = $list;

        return view('make.audit_list',$param);
    }
    /**
     * 查看单个制券(审核同用)
     */
    public function auditShow()
    {
        $id = Input::get('id','');
        if(empty($id)) abort(403,'错误请求');

        $response = ['urlParam' => S::getUrlParam()];
        $sessionData = session('user');
        $detail = CouponMake::join('storage as b','coupon_make.request_storage_id','=','b.id')
                ->where('coupon_make.node_id',$sessionData['node_id'])
                ->where('coupon_make.id',$id)
                ->whereRaw( "FIND_IN_SET( {$sessionData['storage_id']}, lv_b.`full_id` )" )
                ->select('coupon_make.*')
                ->first();

        if(count($detail) == 0) abort(403,'记录不存在');

        //列表页过来的标记
        if(Input::get('from_list') == '1'){
            $detail->from_list = '1';
            //审核状态
            $response['status'] = SystemStatusDao::getCouponMake();
        }else{
            $detail->from_list = '0';
        }
        $response['detail'] =  $detail;

        return view('make.audit_show',$response);
    }
    /**
     * 制券审核的提交地址
     */
    public function auditSave(){
        $htmlCommitData = Input::all();
        $sessionData = session('user');
        //数据提交验证
        if(!in_array($htmlCommitData['pass'],['0','1']) || !isset($htmlCommitData['pass']) || empty($htmlCommitData['select_list'])){
            return redirect('/make/audit/list'.S::getUrlParam())-> with( promptMsg( '错误提交', 3 ));
        }else{
            DB::beginTransaction();
            if($htmlCommitData['pass'] == '1'){     //通过
                //获取制券信息发往支撑制券
                $selectList = CouponMake::join('coupon_type as b','coupon_make.coupon_type_id','=','b.id')->whereIn('coupon_make.id',array_values($htmlCommitData['select_list']))->select('coupon_make.*','b.activity_id','b.price')->get();
                $sendStatus = false;
                foreach ($selectList as $key => $value) {
                    $data = [
                        'id'                => $value->id,
                        'start_flow_no'     => $value->start_flow_no,
                        'amount'            => $value->amount,
                        'begin_time'        => $value->begin_time,
                        'end_time'          => $value->end_time,
                        'node_id'           => $value->node_id,
                        'activity_id'       => $value->activity_id,
                        'price'             => $value->price,
                    ];
                    $rest = SupportDao::applyMake($data);
                    if(!isset($rest['Status']['StatusCode']) || $rest['Status']['StatusCode'] != '0000'){
                        $sendStatus = true;
                        break ;
                    }
                }
                if($sendStatus){
                    return redirect('/make/audit/list'.S::getUrlParam())-> with( promptMsg( '支撑接口调用异常', 3 ));
                }

                //开始更新券信息
                $updata = [
                        'approve_storage_id'   => $sessionData['storage_id'],
                        'approve_storage_name' => $sessionData['storage_name'],
                        'approve_user_id'      => $sessionData['id'],
                        'approve_user_name'    => $sessionData['nickname'],
                        'approve_time'         => date('Y-m-d H:i:s'),
                        'status'               => '3',
                ];
                //更新制券表信息
                try {
                    CouponMake::whereIn('id',array_values($htmlCommitData['select_list']))->update($updata);
                }catch ( \Exception $e )
                {
                    DB::rollBack();
                    abort(403,'操作失败[1]');
                }
            }else{                                  //不通过
                $updata = [
                        'approve_storage_id'   => $sessionData['storage_id'],
                        'approve_storage_name' => $sessionData['storage_name'],
                        'approve_user_id'      => $sessionData['id'],
                        'approve_user_name'    => $sessionData['nickname'],
                        'approve_time'         => date('Y-m-d H:i:s'),
                        'status'               => '2',
                ];
                //更新制券表信息
                try {
                    CouponMake::whereIn('id',array_values($htmlCommitData['select_list']))->update($updata);
                }catch ( \Exception $e )
                {
                    DB::rollBack();
                    abort(403,'操作失败[2]');
                }

            }
            DB::commit();

            return redirect('/make/audit/list'.S::getUrlParam())-> with( promptMsg( '操作成功', 1 ));
        }

    }


}