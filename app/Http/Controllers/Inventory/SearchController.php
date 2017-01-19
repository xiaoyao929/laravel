<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\{Input,Validator,Config};
use Lib\PublicClass\{S};

use App\Dao\{StorageDao,CouponClassDao,CouponTypeDao,SystemStatusDao};
use App\Model\{CouponStorageStock,CouponInfo,CouponMake,CouponPutIn,CouponTransfers,
    CouponInvalid,CouponSale,CouponSaleInfo,CouponReplace,CouponFallbackSale};

class SearchController extends Controller
{
    public function stock()
    {
        $search        = Input::all();
        $userStorageId = session( 'user.storage_id' );
        $couponStorageStock = CouponStorageStock::join( 'storage as b', 'coupon_storage_stock.storage_id', '=', 'b.id' )
            -> whereRaw( "FIND_IN_SET( {$userStorageId}, lv_b.`full_id` )" )
            -> where( 'coupon_storage_stock.node_id', session( 'user.node_id' ))
            -> select( 'coupon_storage_stock.*' )
            -> orderBy( 'coupon_storage_stock.storage_id', 'asc' );

        empty( $search['class'] )     || $couponStorageStock-> where('coupon_storage_stock.coupon_class_id', $search['class']);
        empty( $search['name'] )      || $couponStorageStock-> where('coupon_storage_stock.coupon_type_id', $search['name']);
        empty( $search['storage_id'] )|| $couponStorageStock-> where('coupon_storage_stock.storage_id', $search['storage_id']);
        $list = $couponStorageStock-> paginate(15);
        return view('inventory.search_stock', [
            'list'    => $list,
            'class'   => CouponClassDao::getCouponClass(),
            'type'    => CouponTypeDao::getCouponType(),
            'storages'=> json_encode( StorageDao::getStorages(), JSON_UNESCAPED_UNICODE ),
            'search'  => $search
        ]);
    }
    public function couponInfo()
    {
        $search    = Input::all();
        $identity  = 'orther'; //默认为其他仓库
        $validator = Validator::make( $search, [], []);
        $flowNo    = trim( array_get( $search, 'flow_no' ));
        $user      = session('user');
        $info      = [];

        $param    = [
            'search'    => $search,
            'info'      => $info
        ];

        if( !empty( $flowNo ))
        {
            //券详情和类型信息
            $coupon = CouponInfo::join( 'coupon_type as b', 'coupon_info.coupon_type_id', '=', 'b.id' )
                -> join( 'pos_group as c', 'b.pos_group', '=', 'c.group_id' )
                -> where( 'coupon_info.node_id', $user['node_id'] )
                -> where( 'coupon_info.coupon_flow_no', $flowNo )
                -> select( 'coupon_info.*', 'b.detail_name', 'b.custom_no', 'b.memo as type_memo', 'c.name as group_name', 'c.group_id' )
                -> first();
            if( empty( $coupon ))
            {
                $validator-> errors()-> add('error', '输入的券号有错误，请检查！');
                return view( 'inventory.search_coupon_info', $param )-> withErrors( $validator );
            }
            $info['info']        = $coupon-> toArray();
            $info['info_status'] = SystemStatusDao::getCouponInfo();

            //制券信息
            $make = CouponMake::where( 'node_id', $user['node_id'] )
                -> where( 'start_flow_no', '<=', $flowNo )
                -> where( 'end_flow_no', '>=', $flowNo )
                -> where( function ($query){
                    $query-> orWhere( 'status', CouponMake::STATUS_MAKING )
                        -> orWhere( 'status', CouponMake::STATUS_COMPLETE )
                        -> orWhere( 'status', CouponMake::STATUS_PUT_IN_PENDING )
                        -> orWhere( 'status', CouponMake::STATUS_PUT_IN );
                })
                -> first();
            if( !empty( $make ))
            {
                $info['make']        = $make-> toArray();
                $info['make_status'] = SystemStatusDao::getCouponMake();
            }
            //入库信息
            $putIn = CouponPutIn::where( 'node_id', $user['node_id'] )
                -> where( 'start_flow_no', '<=', $flowNo )
                -> where( 'end_flow_no', '>=', $flowNo )
                -> where( 'status', CouponPutIn::STATUS_PUT_IN )
                -> first();
            if( !empty( $putIn ))
            {
                $info['putin'] = $putIn-> toArray();
            }
            //调拨信息
            $transfers = CouponTransfers::where( 'node_id', $user['node_id'] )
                -> where( 'start_flow_no', '<=', $flowNo )
                -> where( 'end_flow_no', '>=', $flowNo )
                -> where( 'status', CouponTransfers::STATUS_AFFIRM_THROUGH )
                -> get()
                -> toArray();

            if( !empty( $transfers ))
            {
                $info['transfers']        = $transfers;
            }
            //作废信息
            $invalid = CouponInvalid::where( 'node_id', $user['node_id'] )
                -> where( 'start_flow_no', '<=', $flowNo )
                -> where( 'end_flow_no', '>=', $flowNo )
                -> where( 'status', CouponInvalid::STATUS_TRANSIT )
                -> first();

            if( !empty( $invalid ))
            {
                $info['invalid'] = $invalid-> toArray();
            }
            //销售信息
            $sale = CouponSale::join( 'coupon_sale_info as b', 'coupon_sale.id', '=', 'b.id' )
                -> where( 'coupon_sale.node_id', $user['node_id'] )
                -> where( 'b.start_flow_no', '<=', $flowNo )
                -> where( 'b.end_flow_no', '>=', $flowNo )
                -> where( 'status', CouponSale::STATUS_TRANSIT )
                -> select( 'coupon_sale.*' )
                -> first();

            if( !empty( $sale ))
            {
                $re = CouponSale::join( 'storage as b', 'coupon_sale.storage_id', '=', 'b.id' )
                    -> whereRaw( "FIND_IN_SET( {$user['storage_id']}, lv_b.`full_id` )" )
                    -> where( 'coupon_sale.id', $sale['id'] )
                    -> where( 'coupon_sale.status', CouponSale::STATUS_TRANSIT )
                    -> first();
                if( !empty( $re )) $identity = 'available';

                $info['identity']              = $identity;
                $info['sale']                  = $sale-> toArray();
                $info['sale']['customer_info'] = json_decode( $info['sale']['customer_info'], true );
            }

            $fallback = CouponFallbackSale::join( 'coupon_fallback as b', 'coupon_fallback_sale.fallback_seq', '=', 'b.seq' )
                -> where( 'coupon_fallback_sale.node_id', $user['node_id'] )
                -> where( 'coupon_fallback_sale.start_flow_no', '<=', $flowNo )
                -> where( 'coupon_fallback_sale.end_flow_no', '>=', $flowNo )
                -> where( 'coupon_fallback_sale.status', CouponFallbackSale::STATUS_TRANSIT )
                -> select( 'b.seq', 'b.coupon_type_name', 'coupon_fallback_sale.fallback_amount', 'coupon_fallback_sale.start_flow_no',
                    'coupon_fallback_sale.end_flow_no', 'b.reason_type', 'b.reason_content', 'b.memo', 'b.request_storage_name', 'b.request_user_name',
                    'b.request_time', 'coupon_fallback_sale.approve_storage_name', 'coupon_fallback_sale.approve_user_name', 'coupon_fallback_sale.approve_time' )
                -> first();
            if( !empty( $fallback ))
            {
                $info['fallback'] = $fallback-> toArray();
                $info['fallback_status'] = SystemStatusDao::getcouponFallback();
            }

            //换券信息
            $replace = CouponReplace::where( 'node_id', $user['node_id'] )
                -> where( function ( $query ) use( $flowNo ){
                    $query-> orWhere( 'from_flow_no', $flowNo )
                        -> orWhere( 'to_flow_no', $flowNo );
                })
                -> where( 'status', CouponReplace::STATUS_TRANSIT )
                -> first();

            if( !empty( $replace ))
            {
                $info['replace'] = $replace-> toArray();
            }

            $param['info'] = $info;
        }

        return view('inventory.search_coupon_info', $param );
    }
    /**
     * 未激活券查询
     */
    public function noActivation()
    {
        $select = [
                'coupon_type_id'  => '',
                'coupon_class_id' => '',
                'storage_id'      => '',
                'storage_name'    => '',
                'start_flow_no'   => '',
                'end_flow_no'     => '',
                'status'          => '',
        ];
        $sessionData = session('user');
        $search = array_merge($select,Input::all());

        $filter = array_filter($search);
        if(empty($filter)){
            $param = [
                'list'        => (object)null,
                'search'      => $search,
                'status'        => SystemStatusDao::getCouponInfo(),
                'couponType'  => CouponTypeDao::getCouponType(),
                'couponClass' => CouponClassDao::getCouponClass(),
                'storageList' => json_encode(StorageDao::getStorages(), JSON_UNESCAPED_UNICODE)
            ];
            return view('inventory.search_noactivated',$param);

        }

        $list = CouponInfo::join('storage as b','coupon_info.storage_id','=','b.id')
//                ->join('storage as c','coupon_info.storage_id','=','c.id')
                ->where('coupon_info.node_id',$sessionData['node_id'])
                ->where('coupon_info.activate',0)
                ->whereRaw("FIND_IN_SET( {$sessionData['storage_id']}, lv_b.`full_id` )")
                ->select('coupon_info.*');

        //开始号段
        if(!empty($search['start_flow_no'])){
            $list->where('coupon_info.coupon_flow_no','>=',$search['start_flow_no']);
        }
        //结束号段
        if(!empty($search['end_flow_no'])){
            $list->where('coupon_info.coupon_flow_no','<=',$search['end_flow_no']);
        }
        //券类别
        if(!empty($search['coupon_class_id'])){
            $list->where('coupon_info.coupon_class_id',$search['coupon_class_id']);
        }
        //券简称
        if(!empty($search['coupon_type_id'])){
            $list->where('coupon_info.coupon_type_id',$search['coupon_type_id']);
        }
        //所在仓库
        if(!empty($search['storage_id'])){
            $list->where('coupon_info.storage_id',$search['storage_id']);
        }
        //状态
        if(!empty($search['status'])){
            $list->where('coupon_info.status',$search['status']);
        }

        $list = $list->paginate(15);
        $param = [
            'list'          => $list,
            'search'        => $search,
            'status'        => SystemStatusDao::getCouponInfo(),
            'couponType'    => CouponTypeDao::getCouponType(),
            'couponClass'   => CouponClassDao::getCouponClass(),
            'storageList'   => json_encode(StorageDao::getStorages(), JSON_UNESCAPED_UNICODE)
        ];
        return view('inventory.search_noactivated',$param);
        
    }
    /**
     * 文件上传式的库存检查
     */
    public function fileInspectStorage()
    {
        S::setUrlParam();
        $sessionData = session('user');
        $file = $_FILES['csvFile'];

        if($file['error'] != 0){
            $errStr = '文件错误';
            goto end;
        }
        if(stristr($file['name'], 'csv') != 'csv'){
            $errStr = '文件格式错误';
            goto end;
        }
        $resource = fopen($file['tmp_name'],'rw');
        if($resource !== false){
            //错误提示信息
            $errStr = '';
            //行数
            $nu = 0;
            //提交过来的表格数据
            $tabelData = [];
            while(($data = fgetcsv($resource, 1000, ',')) !== false){
                if($nu != 0){
                    if(empty($data[0]) || empty($data[1]) || empty($data[2])){
                        $errStr = "第{$nu}行缺少必填项";
                        goto end;
                    }else{
                        $coupon_type_name = iconv('GBK', 'UTF-8', $data[0]);
                        //同一种券中间有断号的时候会出现这种现象
                        if(isset($tabelData[$coupon_type_name])){
                            $tabelData[$coupon_type_name][] = [
                                    'coupon_type_name' => $coupon_type_name,
                                    'start_flow_no'    => iconv('GBK', 'UTF-8', $data[1]),
                                    'end_flow_no'      => iconv('GBK', 'UTF-8', $data[2]),
                            ];
                        }else{
                            $tabelData[$coupon_type_name][] = [
                                    'coupon_type_name' => $coupon_type_name,
                                    'start_flow_no'    => iconv('GBK', 'UTF-8', $data[1]),
                                    'end_flow_no'      => iconv('GBK', 'UTF-8', $data[2]),
                            ];
                        }


                    }
                }
                $nu++;
            }
            unset($nu);
            @fclose($resource);

            $list = CouponInfo::join('storage as b','coupon_info.storage_id','=','b.id')
                    ->where('coupon_info.node_id',$sessionData['node_id'])
                    ->whereRaw("FIND_IN_SET( {$sessionData['storage_id']}, lv_b.`full_id` )")
                    ->where(function ($query) use ($tabelData){
                        foreach ($tabelData as $key => $value){
                            foreach($value as $key2 => $value2){
                                $query->orWhereBetween('coupon_info.coupon_flow_no',[$value2['start_flow_no'],$value2['end_flow_no']]);
                            }
                        }
                    })
                    ->select('coupon_info.*')
                    ->get()
                    ->toArray();
            if(count($list) > 0){
                $listAsId = array_column($list,'coupon_flow_no','id');
                $listToStatus = array_column($list,'status','id');
                //缺失
                $deficiency = [];
                //已销售
                $sale = [];
                //已作废
                $invalid = [];
                //已核销
                $verification = [];
                foreach ($tabelData as $key => $value){
                    foreach ($value as $key2 => $value2){
                        //缺失的券的开始号段
                        $deficiencyStr = '';
                        //缺失的券的结束号段
                        $deficiencyEnd = '';
                        //已销售的券的开始号段
                        $saleStr = '';
                        //已销售的券的结束号段
                        $saleEnd = '';
                        //已作废的券的开始号段
                        $invalidStr = '';
                        //已作废的券的结束号段
                        $invalidEnd = '';
                        //已核销的券的开始号段
                        $verificationStr = '';
                        //已核销的券的结束号段
                        $verificationEnd = '';
                        for($i = $value2['start_flow_no']; $i <= $value2['end_flow_no']; $i++){
                            $has = array_search($i,$listAsId);
                            if($has){           //存在的
                                $status = $listToStatus[$has];
                                //已销售的券
                                if($status == '2'){
                                    if(empty($saleStr)){
                                        $saleStr = $i;
                                        $saleEnd = $i;
                                    }else{
                                        $saleEnd = $i;
                                    }
                                }
                                //已作废的券
                                if($status == '3'){
                                    if(empty($invalidStr)){
                                        $invalidStr = $i;
                                        $invalidEnd = $i;
                                    }else{
                                        $invalidEnd = $i;
                                    }
                                }
                                //已核销的券
                                if($status == '5'){
                                    if(empty($verificationStr)){
                                        $verificationStr = $i;
                                        $verificationEnd = $i;
                                    }else{
                                        $verificationEnd = $i;
                                    }
                                }
                            }else{              //缺失的
                                if(empty($deficiencyStr)){
                                    $deficiencyStr = $i;
                                    $deficiencyEnd = $i;
                                }else{
                                    $deficiencyEnd = $i;
                                }
                            }

                        }
                        //缺失的券
                        if(!empty($deficiencyStr)){
                            $deficiency[] = [
                                    'coupon_type_name'      => $value2['coupon_type_name'],
                                    'start_flow_no'         => $deficiencyStr,
                                    'end_flow_no'           => $deficiencyEnd,
                                    'status'                => '缺失'
                            ];
                        }
                        //已销售的券
                        if(!empty($saleStr)){
                            $sale[] = [
                                    'coupon_type_name'      => $value2['coupon_type_name'],
                                    'start_flow_no'         => $saleStr,
                                    'end_flow_no'           => $saleEnd,
                                    'status'                => '已销售'
                            ];
                        }
                        //已作废的券
                        if(!empty($invalidStr)){
                            $invalid[] = [
                                    'coupon_type_name'      => $value2['coupon_type_name'],
                                    'start_flow_no'         => $invalidStr,
                                    'end_flow_no'           => $invalidEnd,
                                    'status'                => '缺失'
                            ];
                        }
                        //已核销的券
                        if(!empty($verificationStr)){
                            $verification[] = [
                                    'coupon_type_name'      => $value2['coupon_type_name'],
                                    'start_flow_no'         => $verificationStr,
                                    'end_flow_no'           => $verificationEnd,
                                    'status'                => '缺失'
                            ];
                        }

                    }

                }
            }else{
                $errStr = '未找到指定的券';
            }
        }else{
            return redirect('/inventory/search/coupon/noactivated'.S::getUrlParam())->with(promptMsg('文件打开错误', 3));
        }
end:
        //错误终止
        if(!empty($errStr)){
            return redirect('/inventory/search/coupon/noactivated'.S::getUrlParam())->with(promptMsg($errStr, 3));
        }
        //合并结果
        $overData = array_merge($deficiency,$sale,$invalid,$verification);
        //文件下载地址
        $csvUrl = '';
        if(!empty($overData)) {
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

            //缺失
            $deficiency = [];
            //已销售
            $sale = [];
            //已作废
            $invalid = [];
            //已核销
            $verification = [];

            //标题行
            $str = "券名称,开始券号,结束券号,状态\r\n";
            foreach ($overData as $key => $value) {
                $str .= "{$value['coupon_type_name']},{$value['start_flow_no']},{$value['end_flow_no']},{$value['status']}\r\n";
            }
            $fp = fopen($root . $csvUrl, 'a');
            fwrite($fp, iconv('UTF-8', 'GBK', $str));
            @fclose($fp);
        }
        $param = [
            'csvUrl'    => $csvUrl,
        ];
        return view('inventory.search_noactivated_file_result',$param);

    }

}