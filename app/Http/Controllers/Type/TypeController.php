<?php

namespace App\Http\Controllers\Type;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\{Input,Validator};
use Lib\PublicClass\{S};
use App\Dao\{SupportDao,PosGroupDao,CouponTypeDao,CouponClassDao};

use App\Model\{CouponType,CouponClass};

class TypeController extends Controller
{
    /**
     * 券种列表
     */
    public function list()
    {
        S::setUrlParam();
        $sessionData = session('user');
        $list = CouponType::leftJoin('pos_group as b','coupon_type.node_id','=','b.node_id')
                ->where('coupon_type.node_id',$sessionData['node_id'])
                ->orderBy('coupon_type.id','desc')
                ->select('coupon_type.*','b.name as type_name');

        $select = [
            'class_id'  => '',
            'name'      => '',
            'custom_no' => '',
            'status'    => '-1'
        ];
        $search = array_merge($select,Input::all());
        //类别
        if(!empty($search['class_id'])){
            $list->where('coupon_type.class_id',$search['class_id']);
        }
        //券简称
        if(!empty($search['name'])){
            $list->where('coupon_type.name',$search['name']);
        }
        //自定义编号
        if(!empty($search['custom_no'])){
            $list->where('coupon_type.custom_no',$search['custom_no']);
        }
        //券种状态
        if($search['status'] != '-1'){
            $list->where('coupon_type.status',$search['status']);
        }

        $list = $list->paginate(15);

        //状态
        $status = ['-1'=>'全部','0'=>'停用','1'=>'正常'];

        return view('type.types',[
            'list'  => $list,
            'class' => CouponClassDao::getCouponClass(),//券种类别
            'status'=> $status,
            'search'=> $search
        ]);
    }
    /**
     * 券种详情
     */
    public function show()
    {
        $id = Input::get('id');
        $detail = CouponType::join('pos_group as c','coupon_type.pos_group','=','c.group_id')
                ->where('coupon_type.id',$id)
                ->select('coupon_type.*','c.name as group_name')
                ->first();

        return view('type.type_show',['detail'=>$detail,'urlParam'=>S::getUrlParam()]);
    }

    /**
     * 添加券种类型
     */
    public function edit()
    {
        $id    = Input::get('id','');

        $rowType  = [];
        if(!empty($id)){
            $rowType = CouponType::where('id',$id)->first()->toArray();
        }

        return view( 'type.type_edit', [
            'row_type'=> $rowType,
            'urlParam'=> S::getUrlParam(),
            'class'   => CouponClassDao::getCouponClass(),
            'group'   => PosGroupDao::getPosGroup()//获取终端组
        ]);
    }

    /**
     * 保存券种信息
     */
    public function save()
    {
        $rowType = Input::all();
        $sessionData = session('user');
        
        //券类别id和名称
        $idAndName = explode('_',$rowType['class_id']);
        $rowType['class_id'] = $idAndName[0];
        $rowType['class_name'] = $idAndName[1];

        $validator = Validator::make(
            $rowType,
            [
                'class_id'    => 'required',
                'detail_name' => 'required',
                'name'        => 'required',
            ],
            [
                'class_id.required'    => '请选择券种类别',
                'detail_name.required' => '请填写券种详称',
                'name.required'        => '请填写券种简称',
            ]
        );

        $param = [
            'row_type'=> $rowType,
            'class'   => CouponClassDao::getCouponClass(),//券种类别
            'group'   => PosGroupDao::getPosGroup(),//终端组,
            'urlParam'=> S::getUrlParam()
        ];

        if( $validator-> fails() )
        {
            return view( 'type.type_edit', $param )-> withErrors( $validator );
        }else{
            $msg = '保存异常';
            $result = true;
            //区分BOG券与GC券  1-GC券  2-BOG券
            if($rowType['class_id'] == '1' && !is_numeric($rowType['price']) && $rowType['price'] <= 0){
                $msg = '请填写单价';
                $result = false;
            }
            if( $result )
            {
                //新增
                $rowType['node_id'] = $sessionData['node_id'];
                $result = SupportDao::createType($rowType);
                if(isset($result['Status']) && $result['Status']['StatusCode'] == '0000' && isset($result['ActivityID'])){
                    $dbBusiCoupon = new CouponType();
                    $dbBusiCoupon->node_id = $sessionData['node_id'];
                    $dbBusiCoupon->activity_id = '1';
                    $dbBusiCoupon->status = '1';
                    $dbBusiCoupon->class_id = $rowType['class_id'];
                    $dbBusiCoupon->class_name = $rowType['class_name'];
                    $dbBusiCoupon->detail_name = $rowType['detail_name'];
                    $dbBusiCoupon->name = $rowType['name'];
                    $dbBusiCoupon->price = $rowType['class_id'] == '1' ?  $rowType['price']*100 : '0';
                    $dbBusiCoupon->pos_group = $rowType['group_id'];
                    $dbBusiCoupon->custom_no = $rowType['custom_no'];
                    $dbBusiCoupon->memo = $rowType['memo'];
                    $dbBusiCoupon->activity_id = $result['ActivityID'];

                    $result = $dbBusiCoupon->save();
                }else{
                    $msg = '保存失败';
                    $result = false;
                }
            }

            if($result){
                CouponTypeDao::delCache();
                return redirect('/type/list'.S::getUrlParam())-> with( promptMsg( '保存成功', 1 ));
            }else{
                return view( 'type.type_edit', $param )-> withErrors( $msg );
            }
        }
    }

    /**
     * 券状态修改
     */
    public function state(){
        $id    = Input::get('id');
        $state = Input::get('state');


        $validator = Validator::make( Input::all(), [
                'id'   => 'required',
                'state'=> 'required',
        ], [
                'id.required'   => 'id必须写',
                'state.required'=> '变更状态必须写',
        ]);
        if( $validator-> fails() )
        {
            return redirect('/type/list'.S::getUrlParam())-> withErrors( $validator );
        }

        $couponType =   CouponType::where('id', $id)->first();
        if(empty($couponType)) abort(403,'记录不存在');

        $couponType->status = $state == '1'?'0':'1';

        try {
            $couponType->save();
        }catch ( \Exception $e )
        {
            return redirect('/type/list'.S::getUrlParam())-> with( promptMsg( '数据库存在失败-'.$e->getTraceAsString(), 4 ));
        }
        CouponTypeDao::delCache();
        return redirect('/type/list'.S::getUrlParam())-> with(promptMsg( '修改成功',1 ));
    }
}