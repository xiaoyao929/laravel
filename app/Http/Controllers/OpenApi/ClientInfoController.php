<?php

namespace App\Http\Controllers\OpenApi;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\{Input,Redis,Config};

use Lib\PublicClass\{S};
use App\Model\{InsideSector,Customer};


class ClientInfoController extends Controller
{
    public function info()
    {
        $model   = Input::get('model');
        $keyWord = Input::get('key_word');
        if( empty( $model ) || empty( $keyWord )) S::error('40001');

        switch ( $model )
        {
            case 'internal':
                $data = InsideSector::where( 'status', InsideSector::STATUS_TRANSIT )
                    -> where( 'request_storage_id', session( 'user.storage_id' ))
                    -> where( 'node_id', session( 'user.node_id' ))
                    -> where(function ( $query ) use( $keyWord ){
                        $query-> orWhere( 'sector_name', 'like', "%$keyWord%" )
                            -> orWhere( 'sector_id', 'like', "%$keyWord%" );
                    })
                    -> select( 'id', 'company_name', 'company_id', 'sector_name', 'sector_id' )
                    -> orderBy( 'updated_at', 'desc' )
                    -> orderBy( 'sort', 'asc' )
                    -> get()
                    -> toArray();
                break;
            case 'client':
                $re = Customer::where( 'status', Customer::STATUS_ON )
                    -> where( 'request_storage_id', session( 'user.storage_id' ))
                    -> where( 'node_id', session( 'user.node_id' ))
                    -> where( 'customer_type', Customer::TYPE_CLIENT )
                    -> where(function ( $query ) use( $keyWord ){
                        $query-> orWhere( 'name', 'like', "%$keyWord%" )
                            -> orWhere( 'contact_mobile', 'like', "%$keyWord%" );
                    })
                    -> select( 'id', 'name', 'contact_mobile', 'contact_addr', 'contact_email', 'certificate_type', 'certificate_other_type', 'certificate_code' )
                    -> orderBy( 'updated_at', 'desc' )
                    -> get()
                    -> toArray();
                $data = [];
                foreach ( $re as $k=> $v )
                {
                    $data[$k] = [
                        'id'              => $v['id'],
                        'name'            => $v['name'],
                        'contact_mobile'  => $v['contact_mobile'],
                        'contact_addr'    => $v['contact_addr'],
                        'contact_email'   => $v['contact_email'],
                        'certificate_type'=> $v['certificate_type'] == '其他' ? $v['certificate_other_type'] : $v['certificate_type'],
                        'certificate_code'=> $v['certificate_code']
                    ];
                }
                break;
            case 'company':
                $re = Customer::where( 'status', Customer::STATUS_ON )
                    -> where( 'node_id', session( 'user.node_id' ))
                    -> where( 'request_storage_id', session( 'user.storage_id' ))
                    -> where( 'customer_type', Customer::TYPE_COMPANY )
                    -> where(function ( $query ) use( $keyWord ){
                        $query-> orWhere( 'name', 'like', "%$keyWord%" )
                            -> orWhere( 'contact_name', 'like', "%$keyWord%" )
                            -> orWhere( 'contact_mobile', 'like', "%$keyWord%" );
                    })
                    -> select( 'id', 'name', 'contact_name', 'contact_tel', 'contact_mobile', 'contact_addr', 'contact_email',
                        'certificate_type', 'certificate_other_type', 'certificate_code' )
                    -> orderBy( 'updated_at', 'desc' )
                    -> get()
                    -> toArray();
                $data = [];
                foreach ( $re as $k=> $v )
                {
                    $data[$k] = [
                        'id'              => $v['id'],
                        'name'            => $v['name'],
                        'contact_name'    => $v['contact_name'],
                        'contact_tel'     => $v['contact_tel'],
                        'contact_mobile'  => $v['contact_mobile'],
                        'contact_addr'    => $v['contact_addr'],
                        'contact_email'   => $v['contact_email'],
                        'certificate_type'=> $v['certificate_type'] == '其他' ? $v['certificate_other_type'] : $v['certificate_type'],
                        'certificate_code'=> $v['certificate_code']
                    ];
                }
                break;
        }
        return S::jsonReturn($data);
    }
}