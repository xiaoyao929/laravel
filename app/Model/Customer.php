<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * 客户
 */
class Customer extends Model
{
    protected $table = 'customer';
    //客户使用状态
    const STATUS_ON    = 1; // 正常
    const STATUS_OFF   = 2; // 停用
    //客户类型
    const TYPE_CLIENT  = 1; //个人
    const TYPE_COMPANY = 2;  //单位
    //审核状态
    const AUDIT_STATUS_TRANSIT      = 1; // 审核通过
    const AUDIT_STATUS_PENDING      = 2; // 待审核
    const AUDIT_STATUS_EDIT_PENDING = 3; // 修改后待审核
    const AUDIT_STATUS_NO_THROUGH   = 4; // 审核不通过
    //1-身份证 2-护照 3-营业执照 4-机构代码证 5-其他
    const CERTIFICATE_TYPE_ID_CARD            = 1; //身份证
    const CERTIFICATE_TYPE_PASSPORT           = 2; //护照
    const CERTIFICATE_TYPE_BUSINESS_LICENSE   = 3; //营业执照
    const CERTIFICATE_TYPE_ORGANIZATION_CODE  = 4; //机构代码证
    const CERTIFICATE_TYPE_OTHER              = 5; //其他

}