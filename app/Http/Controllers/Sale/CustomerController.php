<?php

namespace App\Http\Controllers\Sale;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\{Input,Validator,Config,DB};
use Lib\PublicClass\{S};

use App\Dao\{TracesDao,SystemStatusDao,CustomerTypeDao};
use App\Model\{Customer,Traces};

/**
 * 客户
 */
class CustomerController extends Controller
{
    /*
     * 客户列表
     */
    public function list()
    {
        S::setUrlParam();
        $sessionData = session('user');
        $select = [
                'customer_type' => '',
                'name'          => '',
                'contact_tel'   => '',
                'status'        => '1',
                'audit_status'  => ''
        ];

        $search = array_merge($select,Input::all());

        $list = Customer::leftJoin(DB::raw('(select `customer_id`,`status` from `lv_coupon_sale` where `status` = 1 group by `customer_id`) as `lv_b`'),'customer.id','=','b.customer_id')
                ->join('storage as c','customer.request_storage_id','=','c.id')
                ->where('customer.node_id',$sessionData['node_id'])
                ->whereRaw( "FIND_IN_SET( {$sessionData['storage_id']}, lv_c.`full_id` )" )
                ->select('customer.*','b.status as sale_status')
                ->orderBy('customer.updated_at','desc');

        //客户类型
        if(!empty($search['customer_type'])){
            $list->where('customer.customer_type',$search['customer_type']);
        }
        //客户/公司名称
        if(!empty($search['name'])){
            $list->where('customer.name',$search['name']);
        }
        //联系电话
        if(!empty($search['contact_tel'])){
            $list->where('customer.contact_tel',$search['contact_tel']);
        }
        //状态
        if(!empty($search['status'])){
            $list->where('customer.status',$search['status']);
        }
        //审核状态
        if(!empty($search['audit_status'])){
            $list->where('customer.audit_status',$search['audit_status']);
        }

        $list = $list->paginate(15);
        //客户类型
        $customerType = SystemStatusDao::getCustomer('type');
        //使用状态
        $status = SystemStatusDao::getCustomer('status');
        //审核状态
        $auditStatus = SystemStatusDao::getCustomer('audit');

        $param = [
                'list'         => $list,
                'search'       => $search,
                'customerType' => $customerType,
                'status'       => $status,
                'auditStatus'  => $auditStatus
        ];

        return view('customer.customer_list',$param);
    }

    /**
     * 客户新增和编辑的展示页
     */
    public function edit()
    {
        $id = Input::get('id','');
        //结果输出数据
        $rowData = [];
        //客户类型
        $customerType = SystemStatusDao::getCustomer('type');
        //使用状态
        $status = SystemStatusDao::getCustomer('status');
        //证件类型
        $certificateType = CustomerTypeDao::getCertificate();

        if(!empty($id)){
            $rowData = Customer::where('id',$id)->first()->toArray();
            if(empty($rowData)){
                abort(403,'记录不存在');
            }
        }
        $param = [
            'rowData'           => $rowData,
            'customerType'      => $customerType,
            'status'            => $status,
            'certificateType'   => $certificateType,
        ];
        return view('customer.customer_edit',$param);
    }

    /**
     * 客户编辑和新增
     */
    public function save()
    {
        $htmlCommitData = Input::all();
        $sessionData = session('user');
        //客户类型
        $customerType = SystemStatusDao::getCustomer('type');
        //证件类型
        $certificateType = CustomerTypeDao::getCertificate();

        $param = [
                'rowData'         => $htmlCommitData,
                'customerType'    => $customerType,
                'certificateType' => $certificateType
        ];

        $validator = Validator::make(
                $htmlCommitData,
                [
                        'customer_type'     => 'required',
                        'name'              => 'required',
                        'contact_addr'      => 'required',
                        'contact_email'     => 'required|email',
                ],
                [
                        'customer_type.required'    => '客户类型错误',
                        'name.required'             => '请填写客户名称',
                        'contact_addr.required'     => '请填写联系地址',
                        'contact_email.required'    => '请填写联系人邮箱',
                        'contact_email.email'       => '邮箱错误',
                ]
        );
        if( $validator-> fails() )
        {
            return view( 'customer.customer_edit', $param )-> withErrors( $validator );
        }else{
            //按客户类型验证数据
            if(in_array($htmlCommitData['customer_type'],['1','2'])){
                //个人
                if($htmlCommitData['customer_type'] == '1'){
                    //手机
                    if(empty($htmlCommitData['contact_mobile'])){
                        return view( 'customer.customer_edit', $param )-> withErrors( '请填写联系人手机',3);
                    }else{
                        if(preg_match("/^1[3458]{1}\d{9}$/",$htmlCommitData['contact_mobile']) != 1){
                            return view( 'customer.customer_edit', $param )-> withErrors( '手机号错误',3);
                        }
                    }
                }else{      //单位
                    if(empty($htmlCommitData['contact_name'])){
                        return view( 'customer.customer_edit', $param )-> withErrors( '请填写联系人',3);
                    }
                    if(empty($htmlCommitData['contact_tel'])){
                        return view( 'customer.customer_edit', $param )-> withErrors( '请填写联系电话',3);
                    }
                }
                //其他证件类型
                if($htmlCommitData['certificate_type'] == '5' && empty($htmlCommitData['certificate_other_type'])){
                    return view( 'customer.customer_edit', $param )-> withErrors( '请填写证件类型',3);
                }
            }else{
                abort(403,'错误提交');
            }
            //检测客户是否重复
            $isExist = Customer::where('node_id',$sessionData['node_id'])
                    ->where('request_storage_id',$sessionData['storage_id'])
                    ->where('name',$htmlCommitData['name']);
            //个人
            if($htmlCommitData['customer_type'] == '1'){
                $isExist->where('contact_mobile',$htmlCommitData['contact_mobile']);
            }else{
                $isExist->where('contact_name',$htmlCommitData['contact_name']);
            }
            //修改的时候排除自身
            if($htmlCommitData['action'] == '2' && !empty($htmlCommitData['id'])){
                $isExist->where('id','!=',$htmlCommitData['id']);
            }
            $isExist = $isExist->first();
            if(count($isExist) > 0){
                return view( 'customer.customer_edit', $param )-> withErrors( '客户已存在',3);
            }

            $data = [
                //客户信息
                'customer_type'             => $htmlCommitData['customer_type'],
                'name'                      => $htmlCommitData['name'],
                'contact_name'              => $htmlCommitData['contact_name'],
                'contact_tel'               => $htmlCommitData['contact_tel'],
                'contact_mobile'            => $htmlCommitData['contact_mobile'],
                'contact_addr'              => $htmlCommitData['contact_addr'],
                'contact_email'             => $htmlCommitData['contact_email'],
//                'certificate_type'          => $htmlCommitData['certificate_type'],
                'certificate_other_type'    => $htmlCommitData['certificate_other_type'],
                'certificate_code'          => $htmlCommitData['certificate_code'],
                //基本信息
//                'seq'                       => getCustomerSeq(),
//                'node_id'                   => $sessionData['node_id'],
                'request_storage_name'      => $sessionData['storage_name'],
                'request_storage_id'        => $sessionData['storage_id'],
                'request_user_id'           => $sessionData['id'],
                'request_user_name'         => $sessionData['nickname'],
                'request_time'              => date('Y-m-d H:i:s'),
                'status'                    => Customer::STATUS_OFF,
                'created_at'                => date('Y-m-d H:i:s'),
                'updated_at'                => date('Y-m-d H:i:s'),
            ];
            //客户证件类型
            if(!empty($htmlCommitData['certificate_type'])){
                $data['certificate_type'] = $htmlCommitData['certificate_type'];
            }

            $result = false;
            //   1 -添加   2-修改
            if(in_array($htmlCommitData['action'],['1','2'])){
                //修改
                if($htmlCommitData['action'] == '2' && !empty($htmlCommitData['id'])){
                    $data['audit_status'] = Customer::AUDIT_STATUS_EDIT_PENDING;

                    try{
                        Customer::where('id',$htmlCommitData['id'])->update($data);
                        $result = true;
                    }catch(\Exception $e){
                        abort(403,'修改异常');
                    }
                    //修改成功后记录流水
                    TracesDao::index($htmlCommitData['id'],2,2,$data);
                }else{                                  //添加
                    $data['seq'] = getCustomerSeq();
                    $data['node_id'] = $sessionData['node_id'];
                    $data['audit_status'] = Customer::AUDIT_STATUS_PENDING;
                    $result = Customer::insertGetId($data);
                    //新增成功后记录流水
                    if($result){
                        TracesDao::index($result,1,2,$data);
                    }
                }

            }else{
                abort(403,'错误提交');
            }
            if($result){
                return redirect('/client/customer/edit')->with(promptMsg('申请成功', 1));
            }else{
                return view('customer.customer_edit',$param)-> withErrors('申请失败', 3 );
            }
        }

    }

    /**
     * 上传文件导入数据，含有添加和修改操作
     */
    public function fileSave()
    {
        $sessionData = session('user');
        $file = $_FILES['csvFile'];
        //请求的错误提示信息
        $errStr = '';
        if($file['error'] != 0){
            $errStr = '文件错误';
            goto end;
        }
        if(stristr($file['name'], 'csv') != 'csv'){
            $errStr = '文件格式错误';
            goto end;
        }
end:
        //错误终止
        if(!empty($errStr)) {
            $errP = [
                    'rowData'           => [],
                    'customerType'      => SystemStatusDao::getCustomer('type'),
                    'certificateType'   => CustomerTypeDao::getCertificate(),
            ];
            return view('customer.customer_edit', $errP)->withErrors($errStr, 3);
        }

        $resource = fopen($file['tmp_name'],'rw');
        //行数
        $nu = 0;
        //正常的数据
        $succAdd = [];
        //错误数据
        $errData = [];
        //时间
        $time = date('Y-m-d H:i:s');
        if($resource !== false) {
            //证件类型
            $certificateType = [
                '1'     => '身份证',
                '2'     => '护照',
                '3'     => '营业执照',
                '4'     => '机构代码证',
                '5'     => '其他'
            ];
            while (($data = fgetcsv($resource, 1000, ',')) !== false) {
                if ($nu != 0) {
                    $type = iconv('GBK', 'UTF-8', $data[0]);
                    //基本信息
                    $ins = [
                        'node_id'               => $sessionData['node_id'],
                        'name'                  => iconv('GBK', 'UTF-8', $data[1]),
                        'contact_name'          => iconv('GBK', 'UTF-8', $data[2]),
                        'contact_tel'           => iconv('GBK', 'UTF-8', $data[3]),
                        'contact_mobile'        => iconv('GBK', 'UTF-8', $data[4]),
                        'contact_addr'          => iconv('GBK', 'UTF-8', $data[5]),
                        'contact_email'         => iconv('GBK', 'UTF-8', $data[8]),
                        'certificate_type'      => iconv('GBK', 'UTF-8', $data[6]),
                        'certificate_code'      => iconv('GBK', 'UTF-8', $data[7]),
                        //申请人信息
                        'audit_status'          => 2,
                        'status'                => 2,
                        'request_storage_name'  => $sessionData['storage_name'],
                        'request_storage_id'    => $sessionData['storage_id'],
                        'request_user_id'       => $sessionData['id'],
                        'request_user_name'     => $sessionData['nickname'],
                        'request_time'          => $time,
                        'created_at'            => $time,
                        'updated_at'            => $time,
                        'err'                   => '',
                        'num'                   => $nu
                    ];
                    if($type == '个人' || $type == '单位'){
                        //客户名称
                        if(empty($ins['name'])){
                            $ins['err'] = '[请填写客户名称]';
                        }
                        //邮箱验证
                        if (preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',$ins['contact_email']) != 1) {
                            $ins['err'] .= '[邮箱格式错误]';
                        }
                        //联系地址
                        if(empty($ins['contact_addr'])){
                            $ins['err'] .= '[请填写联系地址]';
                        }
                        if($type == '个人'){
                            //客户类型
                            $ins['customer_type'] = '1';
                            //手机号验证
                            if(empty($ins['contact_mobile']) || preg_match("/^1[3458]{1}\d{9}$/",$ins['contact_mobile']) != 1){
                                $ins['err'] .= '[联系人手机错误]';
                            }

                        }else{
                            //客户类型
                            $ins['customer_type'] = '2';
                            //联系人
                            if(empty($ins['contact_name'])){
                                $ins['err'] .= '[请填写联系人]';
                            }
                            //联系电话
                            if(empty($ins['contact_tel'])){
                                $ins['err'] .= '[请填写联系电话]';
                            }

                        }

                        if(empty($ins['err'])){
                            $ins['seq'] = getCustomerSeq();
                            //证件类型
                            $certificate_type = array_search($ins['certificate_type'],$certificateType);
                            if(!$certificate_type && !empty($ins['certificate_type'])){
                                $ins['certificate_other_type'] = $ins['certificate_type'];
                                $ins['certificate_type'] = 5;
                            }else{
                                if($certificate_type){
                                    $ins['certificate_type'] = $certificate_type;
                                }else{
                                    $ins['certificate_type'] = null;
                                }
                            }
//                            unset($ins['err']);
//                            unset($ins['num']);
                            $succAdd[$nu] = $ins;

                        }else{
                            $ins['customer_type'] = $type;
                            $errData[$nu] = $ins;
                        }

                    }else{
                        $ins['err'] = '客户类型错误';
                        $ins['customer_type'] = $type;
                        $errData[$nu] = $ins;
                    }
                }
                $nu++;
            }
            unset($nu);
            @fclose($resource);

        }else{
            $errP = [
                    'rowData'           => [],
                    'customerType'      => SystemStatusDao::getCustomer('type'),
                    'certificateType'   => CustomerTypeDao::getCertificate(),
            ];
            return view('customer.customer_edit', $errP)->withErrors('文件打开错误', 3);
        }
        if(!empty($succAdd)){
            //表格文件中重复的
            $existInTable = [];
            foreach ($succAdd as $key => $value){
                if(!isset($existInTable[$key])){
                    foreach ($succAdd as $key2 => $value2){
                        if($key != $key2){
                            //名字一样
                            if($value['name'] == $value2['name'] && $value['customer_type'] == $value2['customer_type']){
                                //个人
                                if($value['customer_type'] == '1' && $value['contact_mobile'] == $value2['contact_mobile']){
                                    $value['err'] = '[表格内数据重复]';
                                    $value2['err'] = '[表格内数据重复]';
                                    $existInTable[$key] = $value;
                                    $existInTable[$key2] = $value2;
                                }
                                //公司
                                if($value['customer_type'] == '2' && $value['contact_name'] == $value2['contact_name']){
                                    $value['err'] = '[表格内数据重复]';
                                    $value2['err'] = '[表格内数据重复]';
                                    $existInTable[$key] = $value;
                                    $existInTable[$key2] = $value2;
                                }
                            }
                        }
                    }
                }

            }
            if(!empty($existInTable)){
                $errData = $errData + $existInTable;
                $succAdd = array_diff_key($succAdd,$existInTable);
            }
            //入库前做唯一性检测
            $name = array_column($succAdd,'name','num');                        //公司名称 /个人共用
            $contactName = array_column($succAdd,'contact_name','num');         //联系人
            $contactMobile = array_column($succAdd,'contact_mobile','num');     //手机号
            $selectList = Customer::where('node_id',$sessionData['node_id'])
                    ->where('request_storage_id',$sessionData['storage_id'])
                    ->orWhereIn('name',array_values($name))
                    ->orWhereIn('contact_name',array_values($contactName))
                    ->orWhereIn('contact_mobile',array_values($contactMobile))
                    ->get();
            $isExist = [];
            if(count($selectList) > 0){
                foreach ($selectList as $key => $value){
                    foreach ($name as $key2 => $value2){
//                        $inName = array_search($value->name,$name);
                        //名字已存在
//                        if($inName){
                        if($value->name == $value2){
                            //客户类型也一样
                            if($succAdd[$key2]['customer_type'] == $value->customer_type){
                                //个人
                                if($value->customer_type == '1' && $succAdd[$key2]['contact_mobile'] ==$value->contact_mobile){
                                    $succAdd[$key2]['err'] = "[客户已存在]";
                                    $isExist[$key2] = $succAdd[$key2];
                                }
                                //公司
                                if($value->customer_type == '2' && $succAdd[$key2]['contact_name'] ==$value->contact_name){
                                    $succAdd[$key2]['err'] = "[客户已存在]";
                                    $isExist[$key2] = $succAdd[$key2];
                                }
                            }
                        }
                    }
                    //
                }
            }
            if(!empty($isExist)){
                $succAdd = array_diff_key($succAdd,$isExist);
                $errData = $errData + $isExist;
            }
            if(!empty($succAdd)){
                //删除没用的列
                foreach ($succAdd as $key => $value){
                    if(isset($succAdd[$key]['num'])){
                        unset($succAdd[$key]['err']);
                        unset($succAdd[$key]['num']);
                    }
                }
                //开始入库
                DB::beginTransaction();
                try{
                    Customer::insert($succAdd);
                }catch(\Exception $e){
                    DB::rollBack();
                    abort(403,'保存异常');
                }
                DB::commit();
                //记录到流水表里
                $seq = array_column($succAdd,'seq');
                $list = Customer::where('node_id',$sessionData['node_id'])->where('request_time',$time)->whereIn('seq',$seq)->get();
                TracesDao::index($list,1,2,$succAdd,true);
            }

        }
        //文件下载地址
        $csvUrl = '';
        if(!empty($errData)) {
            $root   = getcwd();
            $csvUrl = '/upload/' . $sessionData['node_id'] . '/csv/' . time() . '.csv';
            //文件处理
            if (is_dir($root . '/upload/' . $sessionData['node_id'])) {
                if (!is_dir($root . '/upload/' . $sessionData['node_id'] . '/csv')) {
                    mkdir('/upload/' . $sessionData['node_id'] . '/csv', 0777);
                }
            } else {
                mkdir($root . '/upload/' . $sessionData['node_id'], 0777);
                mkdir($root . '/upload/' . $sessionData['node_id'] . '/csv', 0777);
            }
            //标题行
            $str = "客户类型,客户名称,联系人,联系电话,联系人手机,联系地址,证件类型,证件号码,联系人邮箱,错误原因\r\n";
            foreach ($errData as $key => $value) {
                $str .= "{$value['customer_type']},{$value['name']},{$value['contact_name']},{$value['contact_tel']},{$value['contact_mobile']},{$value['contact_addr']},{$value['certificate_type']},{$value['certificate_code']},{$value['contact_email']},{$value['err']}\r\n";
            }
            $fp = fopen($root . $csvUrl, 'a');
            fwrite($fp, iconv('UTF-8', 'GBK', $str));
            @fclose($fp);
        }
        $param = [
                'returnErr' => $errData,
                'csvUrl'    => $csvUrl,
                'count'     => count($errData) + count($succAdd),
                'fileCount' => count($errData)
        ];
        return view('customer.customer_file_save_result',$param);

    }

    /**
     * 客户审核列表
     */
    public function auditList()
    {
        S::setUrlParam();
        $sessionData = session('user');
        $select = [
                'customer_type'   => '',
                'name' => '',
                'contact_tel'  => '',
        ];
        $search = array_merge($select,Input::all());

        $list = Customer::join('storage as b','customer.request_storage_id','=','b.id')
                ->where('customer.node_id',$sessionData['node_id'])
                ->whereRaw( "FIND_IN_SET( {$sessionData['storage_id']}, lv_b.`full_id` )" )
                ->whereIn('audit_status',['2','3'])
                ->select('customer.*')
                ->orderBy('customer.updated_at','desc');
        
        //客户类型
        if(!empty($search['customer_type'])){
            $list->where('customer.customer_type',$search['customer_type']);
        }
        //客户/公司名称
        if(!empty($search['name'])){
            $list->where('customer.name',$search['name']);
        }
        //联系电话
        if(!empty($search['contact_tel'])){
            $list->where('customer.contact_tel',$search['contact_tel']);
        }

        $list = $list->paginate(15);

        //客户类型
        $customerType = SystemStatusDao::getCustomer('type');
        //审核状态
        $auditStatus = SystemStatusDao::getCustomer('audit');

        return view('customer.audit_list',['customerType'=>$customerType,'list'=>$list,'search'=>$search,'auditStatus'=>$auditStatus]);
    }
    /**
     * 客户详情
     */
    public function auditShow()
    {
        $id = Input::get('id','');
        $from = Input::get('from','0');
        $sessionData = session('user');

        $detail = Customer::where('node_id',$sessionData['node_id'])
                ->where('id',$id)
                ->first();
        //客户类型
        $customerType = [
                Customer::TYPE_CLIENT     => '个人',
                Customer::TYPE_COMPANY     => '单位'
        ];
        //使用状态
        $status = [
                Customer::STATUS_ON      => '正常',
                Customer::STATUS_OFF     => '停用',
        ];
        //审核状态
        $auditStatus = [
                Customer::AUDIT_STATUS_TRANSIT        => '审核通过',
                Customer::AUDIT_STATUS_PENDING        => '待审核',
                Customer::AUDIT_STATUS_EDIT_PENDING   => '修改待审核',
                Customer::AUDIT_STATUS_NO_THROUGH     => '审核未通过',
        ];
        //证件类型
        $certificateType = [
            ''      => '',
            '1'     => '身份证',
            '2'     => '护照',
            '3'     => '营业执照',
            '4'     => '机构代码证',
            '5'     => '其他'
        ];
        $param = [
                'detail'          => $detail,
                'from'            => $from,
                'customerType'    => $customerType,
                'status'          => $status,
                'auditStatus'     => $auditStatus,
                'certificateType' => $certificateType,
                'urlParam'        => S::getUrlParam()
        ];

        return view('customer.audit_show',$param);
    }

    /**
     * 客户审核提交地址
     */
    public function auditSave()
    {
        $htmlCommitData = Input::all();
        $sessionData = session('user');
        //数据提交验证
        if(!in_array($htmlCommitData['pass'],['0','1']) || !isset($htmlCommitData['pass']) || empty($htmlCommitData['select_list'])){
            return redirect('/client/customer/audit/list'.S::getUrlParam())-> with( promptMsg( '错误提交', 3 ));
        }else{
            $data = [
                    'approve_user_id'      => $sessionData['id'],
                    'approve_user_name'    => $sessionData['nickname'],
                    'approve_storage_name' => $sessionData['storage_name'],
                    'approve_storage_id'   => $sessionData['storage_id'],
                    'approve_time'         => date('Y-m-d H:i:s'),
            ];
            if($htmlCommitData['pass'] == '1') {     //通过
                $data['audit_status'] = Customer::AUDIT_STATUS_TRANSIT;
                $data['status'] = Customer::STATUS_ON;
                try{
                    Customer::where('node_id',$sessionData['node_id'])->whereIn('id',array_values($htmlCommitData['select_list']))->update($data);
                }catch(\Exception $e){
                    return redirect('/client/customer/audit/list'.S::getUrlParam())-> with( promptMsg( '操作失败', 3 ));
                }

            }else{
                $data['audit_status'] = Customer::AUDIT_STATUS_NO_THROUGH;
                $data['status'] = Customer::STATUS_OFF;

                //还原上一次的信息
                $oldeData = Traces::whereIn('id',function ($query) use($htmlCommitData,$sessionData){
                    $query->select(DB::raw("Max(id)"))
                            ->from('traces')
                            ->whereIn('source_id',array_values($htmlCommitData['select_list']))
                            ->where('node_id',$sessionData['node_id'])
                            ->where('action',3)
                            ->where('module',2)
                            ->where('run_flag',2)
                            ->groupBy('source_id');
                })->get();

                //需要还原的客户信息
                $restoreData = [];
                if(count($oldeData) > 0){
                    //需要直接修改的数据
                    $directData = [];
                    foreach($oldeData as $key => $value){
                        $approveJson = json_decode($value['approve_data'],1);
                        //如果上一次的审核是通过那么就还原，否则一律不变
                        if($approveJson['audit_status'] == '1'){
                            $requestData = json_decode($value->request_data,1);
                            $restoreData[$value->source_id] = array_merge($requestData,$approveJson);
                        }
                        $directData[] = $value->source_id;
                    }

                    //有被审核通过的数据的重复审核
                    if(!empty($restoreData)){
                        foreach ($restoreData as $key => $value){
                            try{
                                Customer::where('node_id',$sessionData['node_id'])->where('id',$key)->update($value);
                            }catch(\Exception $e){
                                return redirect('/client/customer/audit/list'.S::getUrlParam())-> with( promptMsg( '操作失败', 3 ));
                            }

                        }
                    }
                    $directData = array_diff(array_values($htmlCommitData['select_list']),$directData);

                    //首次申请的数据
                    if(!empty($directData)){
                        try{
                            Customer::where('node_id',$sessionData['node_id'])->where('id',array_values($directData))->update($data);
                        }catch(\Exception $e){
                            return redirect('/client/customer/audit/list'.S::getUrlParam())-> with( promptMsg( '操作失败', 3 ));
                        }
                    }
                    //自始至终都从未被审核通过的数据
                    if(empty($directData) && empty($restoreData)){
                        try{
                            Customer::where('node_id',$sessionData['node_id'])->where('id',array_values($htmlCommitData['select_list']))->update($data);
                        }catch(\Exception $e){
                            return redirect('/client/customer/audit/list'.S::getUrlParam())-> with( promptMsg( '操作失败', 3 ));
                        }
                    }
                }else{
                    //首次审核不存在历史记录
                    try{
                        Customer::where('node_id',$sessionData['node_id'])->where('id',array_values($htmlCommitData['select_list']))->update($data);
                    }catch(\Exception $e){
                        return redirect('/client/customer/audit/list'.S::getUrlParam())-> with( promptMsg( '操作失败', 3 ));
                    }
                }
            }
            //审核成功后记录到流水表里去
            TracesDao::index($htmlCommitData['select_list'],3,2,$data);
            return redirect('/client/customer/audit/list'.S::getUrlParam())-> with( promptMsg( '操作成功', 1 ));
        }

    }
    /**
     * 客户使用状态的修改
     */
    public function status()
    {
        $htmlCommitData = Input::all();
        $sessionData = session('user');

        if(empty($htmlCommitData['id'])) abort(403,'错误请求');

        $rowData = Customer::leftJoin('coupon_sale as b','customer.id','=','b.customer_id')
                ->join('storage as c','customer.request_storage_id','=','c.id')
                ->where('customer.node_id',$sessionData['node_id'])
                ->whereRaw( "FIND_IN_SET( {$sessionData['storage_id']}, lv_c.`full_id` )" )
                ->select('customer.*','b.status as sale_status')
                ->orderBy('customer.updated_at','desc')
                ->where('customer.id',$htmlCommitData['id'])
                ->first();

        //合理性检测
        if(count($rowData) < 0 || $rowData->sale_status == '1' || $rowData->audit_status != '1' || $rowData->status != $htmlCommitData['status']) abort(403,'错误请求');

        //处理停用
        if($rowData->status == '1'){
            $data = [
                    'status'    => '2'
            ];

        }else{      //处理启用
            $data = [
                    'status'       => '1',
                    'audit_status' => '2'
            ];
        }

        Customer::where('node_id',$sessionData['node_id'])->where('id',$htmlCommitData['id'])->update($data);
        try{
        }catch( \Exception $e){
            abort(403,'操作异常');
        }

        return redirect('/client/customer/list'.S::getUrlParam())-> with( promptMsg( '操作成功', 1 ));

    }




}