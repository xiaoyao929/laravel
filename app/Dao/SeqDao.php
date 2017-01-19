<?php
/**
 * 支撑接口类
 * @author 严慧亮
 *
 */

namespace App\Dao;

use Illuminate\Support\Facades\{Config,DB};
use Lib\PublicClass\{S,Log};

use App\Model\{Sequence};

class SeqDao
{
    private static $sysSeq;

    /**
     * 获取与支撑交互的唯一编号
     * @param bool $renewal
     * @return string
     */
    public static function getSysSeq( $renewal = true )
    {
        if( !$renewal )
        {
            if( !empty( self::$sysSeq ))
            {
                return self::$sysSeq;
            }
        }
        $data = DB::select( "SELECT _nextval('sys_seq') AS reqid FROM DUAL" );
        if ( empty( $data ))
        {
            self::$sysSeq = str_pad( rand(1, 999999), 10, '0', STR_PAD_LEFT );
        }
        else
        {
            self::$sysSeq = str_pad( $data[0]-> reqid, 10, '0', STR_PAD_LEFT );
        }
        return self::$sysSeq;
    }

    /**
     * 获取操作流水号
     * @param  string   $type    生成单号的操作行为
     * @return string
     */
    public static function getOperateSeq ( $type )
    {
        switch ( $type )
        {
            case '制券':
                $string = 'zq_seq';
                $action = 'ZQ';
                break;
            case '入库':
                $string = 'rk_seq';
                $action = 'RK';
                break;
            case '调拨':
                $string = 'db_seq';
                $action = 'DB';
                break;
            case '作废':
                $string = 'zf_seq';
                $action = 'ZF';
                break;
            case '销售':
                $string = 'xs_seq';
                $action = 'XS';
                break;
            case '退券':
                $string = 'tq_seq';
                $action = 'TQ';
                break;
            case '换券':
                $string = 'hq_seq';
                $action = 'HQ';
                break;
            default:
                abort( 403, '类型不在范围内' );
        }
        $re  = DB::select( "SELECT _nextseq('{$string}',".session('user.storage_id').") AS reqid FROM DUAL" );
        $seq = str_pad( $re[0]-> reqid, 3, '0', STR_PAD_LEFT );
        return session('user.acronym').date('Ymd').$action.$seq;
    }

    /**
     * 获取客户流水号
     * @return string
     */
    public static function getCustomerSeq()
    {
        $re  = DB::select( "SELECT _nextseq('zzkh_seq',".session('user.storage_id').") AS reqid FROM DUAL" );
        return session('user.acronym').$re[0]-> reqid;
    }

    /**
     * 获取机构券号
     * @param  number $count  制券数量
     * @return array
     */
    public static function getCardSeq( $count )
    {
        if( $count < 1 ) abort(403,'参数错误');

        DB::beginTransaction();

        Sequence::where('name', Sequence::CARD_NAME )-> where( 'node_id', session( 'user.node_id' ))-> sharedLock()-> get();

        if( $count > 1 )//数量大于1
        {
            $re     = DB::select( "SELECT _nextval_by_increment('card_seq',1,'".session('user.node_id')."') AS reqid FROM DUAL" );
            $start  = str_pad( $re[0]-> reqid, 10, '0', STR_PAD_LEFT );
            $count  = $count - 1;
            $re     = DB::select( "SELECT _nextval_by_increment('card_seq',{$count},'".session('user.node_id')."') AS reqid FROM DUAL" );
            $end    = str_pad( $re[0]-> reqid, 10, '0', STR_PAD_LEFT );
            $return = [
                'start'=> $start,
                'end'  => $end,
            ];
        }
        else
        {
            $re     = DB::select( "SELECT _nextval_by_increment('card_seq',1,'".session('user.node_id')."') AS reqid FROM DUAL" );
            $start  = str_pad( $re[0]-> reqid, 10, '0', STR_PAD_LEFT );
            $return = [
                'start'=> $start,
                'end'  => $start,
            ];
        }
        DB::commit();
        return $return;
    }
}