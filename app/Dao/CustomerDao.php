<?php

namespace App\Dao;

use App\Model\{InsideSector,Customer};
use Request;

class CustomerDao
{
    public static function getInternalByCode( $code )
    {
        $user = session('user');
        return InsideSector::where( 'node_id', $user['node_id'] )
            -> where( 'sector_id', $code )
            -> where( 'status', InsideSector::STATUS_TRANSIT )
            -> where( 'request_storage_id', $user['storage_id'] )
            -> select( 'id', 'seq', 'company_name', 'company_id', 'sector_name', 'sector_id' )
            -> get()
            -> toArray();
    }
    public static function getClientByNameAndMobile( $name, $mobile )
    {
        $user = session('user');
        return Customer::where( 'node_id', $user['node_id'] )
            -> where( 'customer_type', Customer::TYPE_CLIENT )
            -> where( 'name', $name )
            -> where( 'contact_mobile', $mobile )
            -> where( 'status', Customer::STATUS_ON )
            -> where( 'request_storage_id', $user['storage_id'] )
            -> select( 'id', 'seq', 'name', 'contact_mobile', 'contact_addr', 'contact_email', 'certificate_type', 'certificate_other_type', 'certificate_code' )
            -> get()
            -> toArray();
    }
    public static function getCompanyByNameAndTel( $name, $tel )
    {
        $user = session('user');
        return Customer::where( 'node_id', $user['node_id'] )
            -> where( 'customer_type', Customer::TYPE_COMPANY )
            -> where( 'contact_name', $name )
            -> where( 'contact_tel', $tel )
            -> where( 'status', Customer::STATUS_ON )
            -> where( 'request_storage_id', $user['storage_id'] )
            -> select( 'id', 'seq', 'name', 'contact_name', 'contact_tel', 'contact_mobile', 'contact_addr', 'contact_email', 'certificate_type', 'certificate_other_type', 'certificate_code' )
            -> get()
            -> toArray();
    }
}