<?php

namespace App\Dao;

use App\Model\{PosGroup};
use Illuminate\Support\Facades\{Cache};

//券种类别
class PosGroupDao
{
    /**
     * 获取终端组
     */
    public static function getPosGroup()
    {
        $data = Cache::rememberForever('cache:pos_group:'.session( 'user.node_id'),function()
        {
            return PosGroup::where('node_id',session( 'user.node_id'))
                    ->where('status',PosGroup::STATUS_ON)
                    ->get()
                    ->toArray();

        });

        return $data;

    }




}