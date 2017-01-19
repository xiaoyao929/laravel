<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * 内部部门
 */
class InsideSector extends Model
{
    protected $table = 'inside_sector';
    //审核状态
    const STATUS_TRANSIT    = 1; // 审核通过
    const STATUS_PENDING    = 2; // 待审核
    const STATUS_NO_THROUGH = 3; // 审核不通过
}