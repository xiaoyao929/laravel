<?php

namespace App\Http\Controllers\Stat;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\{Input,Validator,DB};
use Lib\PublicClass\{S,Log};

use Excel;

use App\Model\{StatNoSaleTime,StatNoSale};
use App\Dao\{StorageDao,CouponTypeDao};

/**
 * 销售
 */
class SaleController extends Controller
{
    public function noSale()
    {
        $search = Input::all();
        $cycle  = StatNoSaleTime::where( 'time', '>', date( 'Y-m-d', strtotime('-6 month')))
            -> orderBy( 'time', 'desc' )
            -> get()
            -> toArray();
        return view('stat.stat_no_sale', [
            'type'    => CouponTypeDao::getCouponType(),
            'cycle'   => $cycle,
            'storages'=> json_encode( StorageDao::getStorages(), JSON_UNESCAPED_UNICODE ),
            'search'  => $search
        ]);
    }
    public function noSaleDown()
    {
        $post = Input::all();
        if( empty( $post['cycle'] )) abort( 403, '错误输入！' );
        if( empty( $post['type_id'] )) return redirect('/stat/no_sales')-> with( promptMsg( '至少选择一种券', 3 ));

        $user = session('user');
        $statNoSaleTime = StatNoSaleTime::where( 'id', $post['cycle'] )-> first();
        if( empty( $statNoSaleTime )) return redirect('/stat/no_sales')-> with( promptMsg( '选择的时间不存在', 3 ));

        $time = $statNoSaleTime-> toArray();

        $statNoSale = StatNoSale::where( 'node_id', $user['node_id'] )
            -> where( 'time_id', $post['cycle'] )
            -> whereIn( 'coupon_type_id', $post['type_id'] )
            -> select( 'storage_name', 'coupon_class_name', 'coupon_type_name', 'start_flow_no', 'end_flow_no', 'amount_no_sale',
                'amount_audit', 'amount_transfers', 'amount_destroyed' )
            -> orderBy( 'storage_id', 'asc' )
            -> orderBy( 'coupon_type_id', 'asc' )
            -> orderBy( 'start_flow_no', 'asc' );

        empty( $post['parent_id'] ) || $statNoSale-> where( 'storage_id', $post['parent_id'] );

        $data = $statNoSale-> get()-> toArray();

        $filename = '未销售报表'.date( 'Ymd', strtotime( $time['time'] ));
        $title = [['仓库名称', '类别', '券简称', '开始券号', '结束券号', '可用库存', '待审核', '调拨在途', '已作废']];

        Excel::create( $filename, function( $excel ) use ( $title, $data )
        {
            $excel-> sheet('score', function( $sheet ) use ( $title, $data )
            {
                $sheet-> freezeFirstRow()-> rows($title)-> rows($data);
            });
        })-> export('xls');
    }
}