<?php

namespace App\Http\Controllers\OpenApi;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\{Input};
use Lib\PublicClass\{S,Log,Xml};

use App\Model\{CouponMake,NodeInfo,CouponInfo,CouponSaleInfo,CouponUsedFlow};
use App\Dao\{UsedCouponFlowDao};

class SupportController extends Controller
{
    /**
     * 支撑制券完成后推送的文件包地址
     */
    public function finish()
    {
        $content= file_get_contents('php://input', 'r');
        if(!empty($content)){
            Log::log_write( '支撑发过来的数据:'.print_r($content,1), '', 'OpenApiSupport' );
        }else{            
            exit;   
        }

        Xml::fromXml($content);
        $content = Xml::getValue();
        Log::log_write( 'XML解析出来:'.print_r($content,1), '', 'OpenApiSupport' );

        if($content['Result']['ResultFlag'] != '0000'){
            exit;
        }
        $tran = explode('--',$content['TransactionID']);

        try{
            $rs = CouponMake::where('id',$tran[1])->update(['download_url'=>$content['FileUrl'],'status'=>'4']);
        }catch( \Exception $e ){
            $rs = false;
        }

        $rarr =  array(
                'GenerateOverRes' => array(
                        'TransactionID'	 => $content['TransactionID'],
                        'Status'		=> array(
                                'StatusCode' => $rs ? "0000":"9999",
                                'StatusText' => $rs?"成功":"失败",
                        ),
                ),
        );
        $xml = Xml::getXMLFromArray($rarr);
        exit($xml);
    }
    /**
     * 券码被核销后支撑通知过来的地址
     */
    public function usedCouponFlow()
    {
        $content= file_get_contents('php://input', 'r');
        if(!empty($content)){
            Log::log_write( '支撑发过来的数据:'.print_r($content,1), '', 'UsedCouponFlow' );
        }else{
            exit;
        }
        //默认返回失败状态
        $response = ['VerifySyncRes'=>['StatusCode'=>'9999']];

        Xml::fromXml($content);
        $content = Xml::getValue();
        Log::log_write( 'XML解析出来:'.print_r($content,1), '', 'UsedCouponFlow' );
        $node_id = $content['ISSPID'];      //商户号
        $spSeq   = $content['SpSeq'];       //当前券号
        if(empty($spSeq)){
            exit;
        }
        //请求类型
        if($content['TransType'] !='0001' && $content['TransType'] !='0002' && $content['TransType'] !='0003'  ){
            Log::log_write( '请求类型错误:'.$content['TransType'], '', 'UsedCouponFlow' );
            goto end;
        }
        //检测支撑是否重复发起请求
        $oldRequest = CouponUsedFlow::where('pos_seq',$content['TerminalSeq'])
                ->where('sp_seq',$content['ReqSeq'])
                ->first();
        if(count($oldRequest) > 0){
            $data = [
                'node_id'       => $content['ISSPID'],
                'pos_id'        => $content['TerminalId'],
                'pos_name'      => $content['TerminalName'],
                'tx_amt'        => $content['Amt'],
                'remain_amt'    => $content['ResiduaryAmt'],
                'trans_time'    => $content['TransTime'],
                'memo'          => $content['SpareField5'],
                'org_pos_seq'   => $content['SpareField2'],
                'trans_type'    => $content['TransType'],
            ];
            try{
                CouponUsedFlow::where('pos_seq',$content['TerminalSeq'])
                    ->where('sp_seq',$content['ReqSeq'])
                    ->update($data);
            }catch(\Exception $e){
                Log::log_write( '旺财更新旧流水记录异常,源数据:'.print_r($content,1), '', 'UsedCouponFlow' );
                goto end;
            }
            $response['VerifySyncRes']['StatusCode'] = '0000';
            goto end;
        }
        //商户检测
        $nodeInfo = NodeInfo::where('node_id',$node_id)->first();
        if(empty($nodeInfo)){
            Log::log_write( '商户不存在,node_id:['.$node_id.']', '', 'UsedCouponFlow' );
            goto end;
        }
        //券合理性检测
        $couponInfo = CouponInfo::join('coupon_class as b','coupon_info.coupon_class_id','=','b.id')
                ->where('coupon_info.coupon_flow_no',$spSeq)
                ->where('coupon_info.node_id',$node_id)
                ->select('coupon_info.*','b.verify_type')
                ->first();

        if(empty($couponInfo)){
            Log::log_write( '券不存在,coupon_flow_no:'.$spSeq, '', 'OpenApiUsedCouponFlow' );
            goto end;
        }

        if($couponInfo->status != '2' && $couponInfo->status != '5'){
            Log::log_write( '券状态不在核销范围内,coupon_flow_no:'.$spSeq, '', 'OpenApiUsedCouponFlow' );
            goto end;
        }

        $saleInfo = CouponSaleInfo::join('coupon_sale as b','coupon_sale_info.seq','=','b.seq')
                ->where('b.node_id',$content['ISSPID'])
                ->where('coupon_sale_info.start_flow_no','<=',$spSeq)
                ->where('coupon_sale_info.end_flow_no','>=',$spSeq)
                ->select('coupon_sale_info.*','b.storage_id','b.storage_name','b.customer_name')
                ->first();
        //记录到流水表里去
        $insertData = [
            'node_id'           => $content['ISSPID'],
            'coupon_class_id'   => $couponInfo->coupon_class_id,
            'coupon_class_name' => $couponInfo->coupon_class_name,
            'coupon_type_id'    => $couponInfo->coupon_type_id,
            'coupon_type_name'  => $couponInfo->coupon_type_name,
            'coupon_flow_no'    => $couponInfo->coupon_flow_no,
            'storage_id'        => $couponInfo->storage_id,
            'storage_name'      => $couponInfo->storage_name,
            //终端号
            'pos_id'            => $content['TerminalId'],
            //终端名称
            'pos_name'          => $content['TerminalName'],
            //终端流水号
            'pos_seq'           => $content['TerminalSeq'],
            //当次核销金额
            'tx_amt'            => $content['Amt']*100,
            //剩余金额
            'remain_amt'        => $content['ResiduaryAmt']*100,
            //核销时间
            'trans_time'        => $content['TransTime'],
            //买走这张券的客户名称
            'customer_name'     => $saleInfo->customer_name,
            //备注
            'memo'              => $content['SpareField5'],
            //原终端流水号
            'org_pos_seq'       => $content['SpareField2'],
            //交易类型
            'trans_type'        => $content['TransType'],
            'status'            => 1,
            //验证流水号
            'sp_seq'            => $content['ReqSeq'],
        ];
        $result = CouponUsedFlow::insert($insertData);
        //操作成功
        if($result){
            $content['couponInfo']       = $couponInfo;
            $content['saleInfo']         = $saleInfo;
            $content['node_id']          = $node_id;
            $content['coupon_flow_no']   = $couponInfo->coupon_flow_no;
            // 开始处理请求
            $result = UsedCouponFlowDao::index($content);
            if($result){
                $response['VerifySyncRes']['StatusCode'] = '0000';
            }
        }else{
            Log::log_write( '写入[lv_coupon_used_flow]库异常:'.print_r($insertData,1), '', 'OpenApiUsedCouponFlow' );
        }

end:
        $response = Xml::getXMLFromArray($response);
        exit($response);

    }



}