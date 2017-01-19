<?php

namespace App\Http\Controllers\Sale;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\{Input,Validator,Config,DB};
use Lib\PublicClass\{S};

use App\Dao\{StorageDao,TracesDao,SystemStatusDao};
use App\Model\{InsideSector,Traces};

/**
 * 内部部门
 */
class InsideSectorController extends Controller
{
    /*
     * 新增公司列表
     */
    public function list()
    {
        S::setUrlParam();
        $sessionData = session('user');
        $select = [
                'company_sector_id'   => '',
                'company_sector_name' => '',
                'request_storage_id'  => '',
                'storage_name'        => '',
                'status'              => ''
        ];
        $search = array_merge($select,Input::all());

        $list = InsideSector::join('storage as b','inside_sector.request_storage_id','=','b.id')
                ->where('inside_sector.node_id',$sessionData['node_id'])
                ->whereRaw( "FIND_IN_SET( {$sessionData['storage_id']}, lv_b.`full_id` )" )
                ->select('inside_sector.*')
                ->orderBy('inside_sector.status','asc')
                ->orderBy('inside_sector.updated_at','desc')
                ->orderBy('inside_sector.sort','asc');
        // 公司/部门编号
        if(!empty($search['company_sector_id'])){
            $list->where(function ($query)use ($search){
                $query->orWhere('inside_sector.company_id',$search['company_sector_id']);
                $query->orWhere('inside_sector.sector_id',$search['company_sector_id']);
            });
        }
        // 公司/部门名称
        if(!empty($search['company_sector_name'])){
            $list->where(function ($query)use ($search) {
                $query->orWhere('inside_sector.company_name', '=', $search['company_sector_name']);
                $query->orWhere('inside_sector.sector_name', '=', $search['company_sector_name']);
            });
        }
        //申请仓库
        if(!empty($search['request_storage_id'])){
            $list->where('inside_sector.request_storage_id','=',$search['request_storage_id']);
        }
        //状态
        if(!empty($search['status'])){
            $list->where('inside_sector.status','=',$search['status']);
        }

        $list = $list->paginate(15);

        $param = [
                'search' => $search,
                'status' => SystemStatusDao::getInsideSector(),
                'list'   => $list,
                'select' => json_encode(StorageDao::getStorages(), JSON_UNESCAPED_UNICODE)
        ];

        return view('insideSector.inside_sector_list',$param);
    }

    /**
     * 新增公司
     */
    public function edit()
    {
        return view('insideSector.inside_sector_edit',['rowData'=>[]]);
    }
    /*
     * 新增公司提交地址
     */
    public function save()
    {
        $htmlCommitData = Input::all();
        $sessionData = session('user');

        if(empty($htmlCommitData['company_name']) || empty($htmlCommitData['company_id']) || empty($htmlCommitData['sector_name']) || empty($htmlCommitData['sector_id'])){
            return view('insideSector.inside_sector_edit',['rowData'=>$htmlCommitData])-> withErrors('缺少必填项', 3 );
        }
        //数据合理性检测
        if(!is_numeric($htmlCommitData['company_id'])){
            return view('insideSector.inside_sector_edit',['rowData'=>$htmlCommitData])-> withErrors('公司编号错误', 3 );
        }
        //检测是否有重复的
        $row = InsideSector::where('node_id',$sessionData['node_id'])
                ->where('company_id',$htmlCommitData['company_id'])
                ->where('sector_id',$htmlCommitData['sector_id'])
                ->where('request_storage_id',$sessionData['storage_id'])
                ->first();
        if(count($row) > 0){
            return view('insideSector.inside_sector_edit',['rowData'=>$htmlCommitData])-> withErrors('公司或部门已存在', 3 );
        }
        $insideSector = new InsideSector();
        //页面提交信息
        $insideSector->company_name = $htmlCommitData['company_name'];
        $insideSector->company_id   = $htmlCommitData['company_id'];
        $insideSector->sector_name  = $htmlCommitData['sector_name'];
        $insideSector->sector_id    = $htmlCommitData['sector_id'];
        //基本信息
        $insideSector->seq                     = getCustomerSeq();
        $insideSector->request_storage_name    = $sessionData['storage_name'];
        $insideSector->request_storage_id      = $sessionData['storage_id'];
        $insideSector->request_user_id         = $sessionData['id'];
        $insideSector->request_user_name       = $sessionData['nickname'];
        $insideSector->request_time            = date('Y-m-d H:i:s');
        $insideSector->node_id                 = $sessionData['node_id'];
        $insideSector->sort                    = 1;
        //入库
        $result = $insideSector->save();
        if($result){
            $dat = [
                    'company_name'         => $insideSector->company_name,
                    'company_id'           => $insideSector->company_id,
                    'sector_name'          => $insideSector->sector_name,
                    'sector_id'            => $insideSector->sector_id,
                    'seq'                  => $insideSector->seq,
                    'request_storage_name' => $insideSector->request_storage_name,
                    'request_storage_id'   => $insideSector->request_storage_id,
                    'request_user_id'      => $insideSector->request_user_id,
                    'request_user_name'    => $insideSector->request_user_name,
                    'request_time'         => $insideSector->request_time,
                    'node_id'              => $insideSector->node_id,
                    'sort'                 => $insideSector->sort,
            ];
            TracesDao::index($insideSector->id,1,1,$dat);
            return redirect('/client/inside/sector/edit')->with(promptMsg('申请成功', 1));
        }else{
            return view('insideSector.inside_sector_edit',['rowData'=>$htmlCommitData])-> withErrors('申请失败', 3 );
        }

    }
    /**
     * 上传文件导入数据，含有添加和修改操作
     */
    public function fileSave()
    {
        $sessionData = session('user');
        $file = $_FILES['csvFile'];

        if($file['error'] != 0){
            return view('insideSector.inside_sector_edit',['rowData'=>[]])->withErrors('文件错误',3);
        }
        if(stristr($file['name'], 'csv') != 'csv'){
            return view('insideSector.inside_sector_edit',['rowData'=>[]])->withErrors('文件格式错误',3);
        }

        $resource = fopen($file['tmp_name'],'rw');
        if($resource !== false){
            //修改的数据
            $updata = [];
            //添加的数据
            $addData = [];
            //错误的数据
            $errData = [];
            //行数
            $nu = 0;
            //时间
            $time = date('Y-m-d H:i:s');
            while(($data = fgetcsv($resource, 1000, ',')) !== false){
                if($nu != 0){
                    //原部门编号和现部门编号都存在视为修改
                    if(!empty($data[2]) && !empty($data[3]) && !empty($data[4])){
                        $updata[$nu] = [
                                'company_id'        => $data[0],
                                'company_name'      => iconv('GBK','UTF-8', $data[1]),
                                'old_sector_id'     => iconv('GBK', 'UTF-8', $data[2]),
                                'new_sector_id'     => iconv('GBK', 'UTF-8', $data[3]),
                                'new_sector_name'   => iconv('GBK','UTF-8', $data[4]),
                                'sort'              => $nu,
                                'action'            => '1'
                        ];
                    }else{
                        //新增时候的4个必填参数
                        if(!empty($data[0]) && !empty($data[1]) && !empty($data[2]) && !empty($data[4])){
                            $addData[$nu] = [
                                    'seq'                => getCustomerSeq(),
                                    'company_name'         => iconv('GBK', 'UTF-8', $data[1]),
                                    'company_id'           => $data[0],
                                    'sector_name'          => iconv('GBK', 'UTF-8', $data[4]),
                                    'sector_id'            => iconv('GBK', 'UTF-8', $data[2]),
                                    'request_storage_name' => $sessionData['storage_name'],
                                    'request_storage_id'   => $sessionData['storage_id'],
                                    'status'               => InsideSector::STATUS_PENDING,
                                    'sort'                 => $nu,
                                    'request_user_id'      => $sessionData['id'],
                                    'request_user_name'    => $sessionData['nickname'],
                                    'request_time'         => $time,
                                    'node_id'              => $sessionData['node_id'],
                                    'created_at'           => $time,
                                    'updated_at'           => $time,
                            ];
                        }else{
                            $errData[$nu] = [
                                    'company_id'        => $data[0],
                                    'company_name'      => iconv('GBK','UTF-8', $data[1]),
                                    'old_sector_id'     => iconv('GBK', 'UTF-8', $data[2]),
                                    'new_sector_id'     => iconv('GBK', 'UTF-8', $data[3]),
                                    'new_sector_name'   => iconv('GBK','UTF-8', $data[4]),
                                    'sort'                => $nu,
                                    'action'            => '2'
                            ];
                        }
                    }

                }
                $nu++;
            }
            @fclose($resource);
            //数量限制
            $count = count($updata) + count($addData) + count($errData);
            if($count > 1000){
                return view('insideSector.inside_sector_edit',['rowData'=>[]])->withErrors('请输入1000条以内的数据',3);
            }
            //修改时候的错误数据
            $updataErr = [];
            //检测后正常可修的数据
            $succUpdata = [];
            if(!empty($updata)){
                $old_sector_id = array_column($updata,'old_sector_id','sort');
                $new_sector_id = array_column($updata,'new_sector_id','sort');
                //公司编号
                $cm_company_id = array_column($updata,'company_id','sort');
                $listUpdate = InsideSector::join('storage as b','inside_sector.request_storage_id','=','b.id')
                        ->where('inside_sector.node_id',$sessionData['node_id'])
                        ->whereRaw( "FIND_IN_SET( {$sessionData['storage_id']}, lv_b.`full_id` )" )
                        ->whereIn('inside_sector.sector_id',array_values($old_sector_id))
                        ->whereIn('inside_sector.company_id',array_values($cm_company_id))
                        ->where('inside_sector.status','!=','2')
                        ->select('inside_sector.*')
                        ->get()
                        ->toArray();
                if(!empty($listUpdate)){
                    //提交过来不存在的部门编号
                    $rs_sector_id = array_column($listUpdate,'sector_id');
                    $fail_sector_id = array_diff($old_sector_id,$rs_sector_id);
                    //检测提交过来重复的部门编号
                    $repetitiveErr = [];
                    foreach ($updata as $key => $value){
                        if(!isset($repetitiveErr[$key])){
                            //原部门编号重复的
                            foreach ($old_sector_id as $key2 => $value2){
                                if($value['old_sector_id'] == $value2 && $key != $key2){
                                    $repetitiveErr[$key] = $value;
                                    $repetitiveErr[$key2] = $value2;
                                }
                            }
                            //新部门编号重复的
                            foreach ($new_sector_id as $key3 => $value3){
                                if($value['new_sector_id'] == $value3 && $key != $key3){
                                    $repetitiveErr[$key] = $value;
                                    $repetitiveErr[$key3] = $value3;
                                }
                            }

                        }
                    }
                    if(!empty($repetitiveErr)){
                        foreach ($repetitiveErr as $key => $value){
                            $repetitiveErr[$key]['action'] = '3';
                        }

                    }
                    $pureData = array_diff_key($updata,$repetitiveErr);
                    //已经被循环过的key
                    $keys = [];
                    //错误的key
                    $errKey = [];
                    foreach($pureData as $key => $value){
                        $last_arr = [];
                        $is_in = [];
                        if(!in_array($key,$keys)){
                            $this->selTO($pureData,$value['old_sector_id'],$is_in,$last_arr);
                            $keys = $keys + $is_in;

                            if(empty($last_arr) && empty($is_in)){   //没找到有相互间替换的
                                $errKey[$key][] = $key;
                            }else {
                                $last_key = array_keys($last_arr)[0];
                                if ($last_key != $key) {       //非闭环的时候
                                    if (isset($errKey[$last_key])) {
                                        $errKey[$last_key][] = $key;
                                    } else {
                                        $errKey[$last_key][] = $last_key;
                                        $errKey[$last_key][] = $key;
                                    }
                                }

                            }
                        }
                    }
                    //已存在库中的
                    $exist = [];
                    //查找最终闭环结束的部门id是否已在库里存在
                    if(!empty($errKey)){
                        $serachArr = [];
                        foreach ($errKey as $kye => $value){
                            $serachArr[$kye] = $pureData[$kye]['new_sector_id'];
                        }
                        $serchList = InsideSector::join('storage as b','inside_sector.request_storage_id','=','b.id')
                                ->where('inside_sector.node_id',$sessionData['node_id'])
                                ->whereRaw( "FIND_IN_SET( {$sessionData['storage_id']}, lv_b.`full_id` )" )
                                ->whereIn('inside_sector.sector_id',array_values($serachArr))
//                                ->where('inside_sector.status','!=','2')
                                ->select('inside_sector.*')
                                ->get();
                        if(count($serchList) > 0){
                            foreach ($serchList as $key =>$value){
                                $srachKey = array_search($value->sector_id,$serachArr);
                                $filep = array_flip($errKey[$srachKey]);
                                if(empty($exist)){
                                    $exist = $filep;
                                }else{
                                    $exist += $filep;
                                }
                            }
                        }
                    }
                    if(!empty($exist)){
                        foreach ($exist as $key => $value){
                            $exist[$key]['action'] = '4';
                        }
                    }
                    //        不存在的      +  重复的        +  库里已存在的 (非闭环情况下的)
                    $allErr = $fail_sector_id + $repetitiveErr + $exist;
                    //最终错误的数据
                    $updataErr = array_intersect_key($updata,$allErr);
                    //正常的部门编号
                    $succUpdata = array_diff_key($updata,$allErr);

                }else{
                    $updataErr = array_intersect_key($updata,$old_sector_id);
                }
            }

            //开始数据入库
            DB::beginTransaction();
            //修改
            if(!empty($succUpdata) && !empty($listUpdate)){
                foreach($succUpdata as $key => $value){
                    foreach ($listUpdate as $key3 => $value3){
                        if($value['old_sector_id'] == $value3['sector_id'] && $value3['company_id'] == $value['company_id']){
                            $d = [
                                    'request_storage_name'  => $sessionData['storage_name'],
                                    'request_storage_id'    => $sessionData['storage_id'],
                                    'request_user_id'       => $sessionData['id'],
                                    'request_user_name'     => $sessionData['nickname'],
                                    'request_time'          => $time,
                                    'approve_user_id'       => null,
                                    'approve_storage_id'    => null,
                                    'sector_id'             => $value['new_sector_id'],
                                    'sector_name'           => $value['new_sector_name'],
                                    'sort'                  => $value['sort'],
                                    'status'                => InsideSector::STATUS_PENDING,
                            ];
                            try{
                                InsideSector::where('id',$value3['id'])->where('company_id',$value3['company_id'])->update($d);
                            }catch(\Exception $e){
                                DB::rollBack();
                                abort(403,'保存异常');
                            }
                            //修改的时候记录到流水表里
//                            if(!empty($listUpdate)){
//                                foreach ($listUpdate as $key2 => $value2){
//                                    if($value3['sector_id'] == $value['old_sector_id']){
                                        TracesDao::index($value3['id'],2,1,$d);
//                                        break;
//                                    }
//                                }
//                            }

                        }
                    }
                }
            }
            DB::commit();

            //添加时的异常数据
            $addErr = [];
            //添加时的正常数据
            $succAdd = [];
            if(!empty($addData)){
                $cm_company_id = array_column($addData,'company_id','sort');
                $cm_sector_id = array_column($addData,'sector_id','sort');
                $list = InsideSector::join('storage as b','inside_sector.request_storage_id','=','b.id')
                        ->whereRaw( "FIND_IN_SET( {$sessionData['storage_id']}, lv_b.`full_id` )" )
                        ->where('inside_sector.node_id', $sessionData['node_id'])
                        ->whereIn('inside_sector.company_id',$cm_company_id)
                        ->whereIn('inside_sector.sector_id', $cm_sector_id)
                        ->select('inside_sector.*')
                        ->get()
                        ->toArray();
                //提交过来重复的数据
                $faliComm = array_diff_key($addData,array_unique($cm_sector_id));

                if(!empty($list)){
                    $rs_company_id = array_column($list,'company_id','sector_id');
                    $rs_sector_id = array_column($list,'sector_id');
                    $fail_sector_id = array_intersect($cm_sector_id,$rs_sector_id);

                    //同一个公司下的相同部门
                    $fail_company_id = [];
                    if(!empty($fail_sector_id)){
                        foreach ($fail_sector_id as $key => $value){
                            if(isset($rs_company_id[$value]) && $rs_company_id[$value] == $addData[$key]['company_id']){
                                $fail_company_id[$key] = $value;
                            }
                        }
                    }
                    //已存在的数据
                    $faliAdd = $fail_company_id + $fail_sector_id + $faliComm;
                    //最终错误的数据
                    $addErr = array_intersect_key($addData,$faliAdd);
                    //提取出正常的数据
                    $succAdd = array_diff_key($addData,$faliAdd);
                }else{
                    $succAdd = $addData;
                }
            }

            DB::beginTransaction();
            //添加
            $rs = false;
            if(!empty($succAdd)){
                try{
                    $rs = InsideSector::insert($succAdd);
                }catch(\Exception $e){
                    DB::rollBack();
                    abort(403,'保存异常');
                }
            }
            //添加完记录到流水表里
            if(!empty($succAdd) && $rs){
                $sort = array_column($succAdd,'sort');
                $list = InsideSector::where('node_id',$sessionData['node_id'])->where('request_time',$time)->whereIn('sort',$sort)->get();
                TracesDao::index($list,1,1,$succAdd,true);
            }
            DB::commit();

        }else{
            return view('insideSector.inside_sector_edit',['rowData'=>[]])->withErrors('文件打开错误',3);
        }

        //错误输出数据
        $returnErr = [];
        //错误数据的下载地址
        $csvUrl = '';
        if(!empty($updataErr) || !empty($addErr) || !empty($errData)){
            $root = getcwd();
            $csvUrl = '/upload/'.$sessionData['node_id'].'/csv/'.time().'.csv';
            //文件处理
            if(is_dir($root.'/upload/'.$sessionData['node_id'])){
                if(!is_dir($root.'/upload/'.$sessionData['node_id'].'/csv')){
                    mkdir('/upload/'.$sessionData['node_id'].'/csv',0777);
                }
            }else{
                mkdir($root.'/upload/'.$sessionData['node_id'],0777);
                mkdir($root.'/upload/'.$sessionData['node_id'].'/csv',0777);
            }

            $failData = $updataErr + $addErr + $errData;
            ksort($failData);
            //生成错误文件提供下载
            $str = "公司编号,公司名称,原部门编号,现部门编号,部门名称,错误原因\r\n";
            foreach($failData as $key => $value){
                if(isset($value['action']) && $value['action'] == '1'){
                    $str .= "{$value['company_id']},{$value['company_name']},{$value['old_sector_id']},{$value['new_sector_id']},{$value['new_sector_name']},公司或部门不存在,或状态不在可编辑范围内\r\n";
                    $returnErr[$key] = ['msg'=>'公司或部门不存在,或状态不在可编辑范围内'];

                }elseif(isset($value['action']) && $value['action'] == '2'){
                    $str .= "{$value['company_id']},{$value['company_name']},{$value['old_sector_id']},{$value['new_sector_id']},{$value['new_sector_name']},缺少必填项\r\n";
                    $returnErr[$key] = ['msg'=>'缺少必填项'];

                }elseif(isset($value['action']) && $value['action'] == '3'){
                    $str .= "{$value['company_id']},{$value['company_name']},{$value['old_sector_id']},{$value['new_sector_id']},{$value['new_sector_name']},文件内重复数据\r\n";
                    $returnErr[$key] = ['msg'=>'文件内重复数据'];

                }elseif(isset($value['action']) && $value['action'] == '4'){
                    $str .= "{$value['company_id']},{$value['company_name']},{$value['old_sector_id']},{$value['new_sector_id']},{$value['new_sector_name']},部门已存在\r\n";
                    $returnErr[$key] = ['msg'=>'部门已存在'];

                }else{
                    $str .= "{$value['company_id']},{$value['company_name']},{$value['sector_id']},,{$value['sector_name']},公司或部门已存在\r\n";
                    $returnErr[$key] = ['msg'=>'公司或部门已存在,请勿重复提交'];

                }
            }
            $fp = fopen($root.$csvUrl, 'a');
            fwrite($fp, iconv('UTF-8','GBK', $str));
            @fclose($fp);
        }
        return view('insideSector.inside_sector_file_save_result',['csvUrl'=>$csvUrl,'returnErr'=>$returnErr,'count'=>count($updata) + count($addData) + count($errData),'fileCount'=>count($updataErr) + count($addErr) + count($errData)]);
    }
    /**
     * 抵御接龙式的修改
     */
    public function selTO($arr,$old_id,&$ttf = [],&$last_arr){
        foreach($arr as $key => $value){
            if($value['new_sector_id'] == $old_id && !in_array($key,$ttf)){
                $ttf[$key] = $key;
                $last_arr = [$key=>$value];
                $this->selTO($arr,$value['old_sector_id'],$ttf,$last_arr);
            }

        }
        return false;
    }

    /*
     * 新增公司审核列表
     */
    public function auditList()
    {
        S::setUrlParam();
        $select = [
                'company_sector_id'   => '',
                'company_sector_name' => '',
                'request_storage_id'  => '',
                'storage_name'        => ''
        ];
        $sessionData = session('user');
        $search = array_merge($select,Input::all());

        $list = InsideSector::join('storage as b','inside_sector.request_storage_id','=','b.id')
                ->whereRaw( "FIND_IN_SET( {$sessionData['storage_id']}, lv_b.`full_id` )" )
                ->where('inside_sector.node_id',$sessionData['node_id'])
                ->where('inside_sector.status','2')
                ->select('inside_sector.*')
                ->orderBy('inside_sector.updated_at','desc')
                ->orderBy('inside_sector.sort','asc');

        // 公司/部门编号
        if(!empty($search['company_sector_id'])){
            $list->where(function ($query)use ($search){
                $query->orWhere('inside_sector.company_id',$search['company_sector_id']);
                $query->orWhere('inside_sector.sector_id',$search['company_sector_id']);
            });
        }
        // 公司/部门名称
        if(!empty($search['company_sector_name'])){
            $list->where(function ($query)use ($search) {
                $query->orWhere('inside_sector.company_name', '=', $search['company_sector_name']);
                $query->orWhere('inside_sector.sector_name', '=', $search['company_sector_name']);
            });
        }
        //申请仓库
        if(!empty($search['request_storage_id'])){
            $list->where('inside_sector.request_storage_id','=',$search['request_storage_id']);
        }

        $list = $list->paginate(15);
        return view('insideSector.inside_sector_audit_list',['list'=>$list,'search'=>$search,'select'=>json_encode( StorageDao::getStorages(), JSON_UNESCAPED_UNICODE )]);
    }
    /*
     * 新增公司审核提交地址
     */
    public function auditSave()
    {
        $htmlCommitData = Input::all();
        $sessionData = session('user');
        //数据提交验证
        if(!in_array($htmlCommitData['pass'],['0','1']) || !isset($htmlCommitData['pass']) || empty($htmlCommitData['select_list'])){
            return redirect('/client/inside/sector/audit/list'.S::getUrlParam())-> with( promptMsg( '错误提交', 3 ));
        }else{
            if($htmlCommitData['pass'] == '1') {     //通过
                $status = InsideSector::STATUS_TRANSIT;
            }else{
                $status = InsideSector::STATUS_NO_THROUGH;
            }
            $data = [
                    'approve_user_id'      => $sessionData['id'],
                    'approve_user_name'    => $sessionData['nickname'],
                    'approve_storage_id'   => $sessionData['storage_id'],
                    'approve_storage_name' => $sessionData['storage_name'],
                    'approve_time'         => date('Y-m-d H:i:s'),
                    'status'               => $status
            ];

            try{
                InsideSector::whereIn('id',array_values($htmlCommitData['select_list']))->update($data);
            }catch(\Exception $e){
                return redirect('/client/inside/sector/audit/list'.S::getUrlParam())-> with( promptMsg( '操作失败', 3 ));
            }
            //审核完记录到流水表里
            TracesDao::index($htmlCommitData['select_list'],3,1,$data);
            return redirect('/client/inside/sector/audit/list'.S::getUrlParam())-> with( promptMsg( '操作成功', 1 ));
        }
    }




}