<?php

namespace App\Dao;

use Illuminate\Support\Facades\{DB};
use Lib\PublicClass\{Log};
use App\Model\{CouponUsedFlow,CouponInfo,CouponStorageStock};

class UsedCouponFlowDao
{
    //券信息
    public static $couponInfo;
    //销售信息
    public static $saleInfo;

    /**
     * 中转处理
     * @param $data
     */
    public static function index($data)
    {
        self::$couponInfo = $data['couponInfo'];
        self::$saleInfo   = $data['saleInfo'];
        //冲正撤销
        if($data['TransType'] =='0003' || $data['TransType'] =='0002'  ){
            $result = self::correctUndo($data);
        }else{
            $result = self::verified($data);
        }

        return $result;
    }
    /**
     * 正常核销
     */
    private static function verified($data)
    {
        $couponInfo = self::$couponInfo;
        
        //按次数核验
        if($couponInfo->verify_type == '2' && $couponInfo->status != '2'){
            Log::log_write( '券状态不在可核销状态内,coupon_flow_no:'.$couponInfo->coupon_flow_no, '', 'OpenApiUsedCouponFlow' );
            return false;
        }
        //按金额核验的
        if($couponInfo->verify_type == '1' && $couponInfo->residuary_price/100 < $data['ResiduaryAmt'] && ($couponInfo->status != '2' || $couponInfo->status != '5')){
            Log::log_write( '超过最大金额验证,coupon_flow_no:'.$couponInfo->coupon_flow_no, '', 'OpenApiUsedCouponFlow' );
                return false;
        }
        //修改券信息
        DB::beginTransaction();
        try{
            $result = CouponInfo::where('node_id',$data['node_id'])
            ->where('coupon_flow_no',$couponInfo->coupon_flow_no)
            ->update(['sp_seq'=>$data['ReqSeq'],'residuary_price'=>$data['ResiduaryAmt']*100,'status'=>5,'va_status'=>$data['Status']]);
        }catch(\Exception $e){
            DB::rollBack();
            Log::log_write('修改表[coupon_info]失败', '', 'OpenApiUsedCouponFlow' );
            return false;
        }
        //被核验的数量+1 
        if($data['Status'] == '3'){         //金额被核销完了
            $result = CouponStorageStock::where('node_id',$data['node_id'])
                ->where('storage_id',$couponInfo->storage_id)
                ->where('coupon_class_id',$couponInfo->coupon_class_id)
                ->where('coupon_type_id',$couponInfo->coupon_type_id)
                ->increment('amount_used');
            if(!$result){
                DB::rollBack();
                Log::log_write('修改表[coupon_storage_stock]失败', '', 'OpenApiUsedCouponFlow' );
                return false;
            }
        }
        DB::commit();

        return true;

    }

    /**
     * 冲正撤销
     */
    private static function correctUndo($data)
    {
        $couponInfo = self::$couponInfo;
        if($couponInfo->sp_seq != $data['SpareField2']){
            //未找到原核验流水号
            return false;
        }
        DB::beginTransaction();
        //更新原流水记录状态为撤销
        try{
            CouponUsedFlow::where('sp_seq',$data['SpareField2'])
                ->update(['status'=>2]);
        }catch(\Exception $e){
            DB::rollBack();
            Log::log_write('修改表[coupon_used_flow]失败', '', 'OpenApiUsedCouponFlow' );
            return false;
        }
        //更新券信息
        try{
            //状态为未使用就全额回退
            if($data['Status'] == '0'){
                $data['ResiduaryAmt'] = $couponInfo->coupon_price;
            }else{
                $data['ResiduaryAmt'] = $data['ResiduaryAmt']*100;
            }
            $result = CouponInfo::where('node_id',$data['node_id'])
                    ->where('coupon_flow_no',$couponInfo->coupon_flow_no)
                    ->update(['sp_seq'=>$data['ReqSeq'],'residuary_price'=>$data['ResiduaryAmt'],'status'=>2,'va_status'=>$data['Status']]);
        }catch(\Exception $e){
            DB::rollBack();
            Log::log_write('修改表[coupon_info]失败', '', 'OpenApiUsedCouponFlow' );
            return false;
        }
        //库存数量-1    （上一次的更新结果是把库存扣减了，这次撤销就换回去）
        if($couponInfo->va_status == '3'){
            $result = CouponStorageStock::where('node_id',$data['node_id'])
                    ->where('storage_id',$couponInfo->storage_id)
                    ->where('coupon_class_id',$couponInfo->coupon_class_id)
                    ->where('coupon_type_id',$couponInfo->coupon_type_id)
                    ->decrement('amount_used');
        }
        if(!$result){
            DB::rollBack();
            Log::log_write('修改表[coupon_storage_stock]失败', '', 'OpenApiUsedCouponFlow' );
            return false;
        }
        DB::commit();

        return true;
    }


}