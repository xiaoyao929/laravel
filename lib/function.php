<?php

if (!function_exists('promptMsg'))
{
    /**
     * 生成提示信息
     * @param $msg
     * @param string $level 信息等级：1-success,2-info,3-warning,4-danger
     */
    function promptMsg( $msg, $level='2' )
    {
        switch ( $level )
        {
            case '1':
                $arr = [
                    'level'=> 'success',
                    'msg'  => $msg,
                ];
                break;
            case '2':
                $arr = [
                    'level'=> 'info',
                    'msg'  => $msg,
                ];
                break;
            case '3':
                $arr = [
                    'level'=> 'warning',
                    'msg'  => $msg,
                ];
                break;
            case '4':
                $arr = [
                    'level'=> 'danger',
                    'msg'  => $msg,
                ];
                break;
            default:
                abort('403');
        }
        return ['prompt_msg'=> $arr];
    }
}
if (!function_exists('unlimitedForLayer'))
{
    /**
     * 无限级分类
     * @param $cate
     * @param string $key
     * @param int $pid
     * @param string $name
     * @return array
     */
    function unlimitedForLayer( $cate, $key='pid', $pid = 0, $name = 'child' )
    {
        $arr = [];
        foreach ($cate as $k=> $v)
        {
            if ( $v[$key] == $pid )
            {
                $child    = unlimitedForLayer( $cate, $key, $v['id'], $name );
                if( !empty( $child ))
                {
                    $v[$name]  = $child;
                    $count     = count( getChildsId( $cate, $v['id'], $key ));
                    $v['tags'] = [$count];
                }
                $arr[]    = $v;
            }
        }
        return $arr;
    }
}
if (!function_exists('getChildsId'))
{
    /**
     * 传递一个父级分类ID返回所有子分类ID
     * @param $cate
     * @param $pid
     * @param string $key
     * @return array
     */
    function getChildsId ( $cate, $pid, $key='pid' )
    {
        $arr = [];
        foreach ($cate as $v)
        {
            if ($v[$key] == $pid)
            {
                $arr[] = $v['id'];
                $arr   = array_merge( $arr, getChildsId( $cate, $v['id'], $key ));
            }
        }
        return $arr;
    }
}
if (!function_exists('getChilds'))
{
    /**
     * 传递一个父级分类ID返回所有子分类
     * @param $cate
     * @param $pid
     * @param string $key
     * @return array
     */
    function getChilds ( $cate, $pid, $key='pid' )
    {
        $arr = [];
        foreach ($cate as $v)
        {
            if ( $v[$key] == $pid )
            {
                $arr[] = $v;
                $arr = array_merge( $arr, getChilds( $cate, $v['id'], $key ));
            }
        }
        return $arr;
    }
}
if (!function_exists('getSysSeq'))
{
    /**
     * 获取支撑流水号
     * @param bool $renewal
     * @return string
     */
    function getSysSeq ( $renewal = true )
    {
        return App\Dao\SeqDao::getSysSeq( $renewal );
    }
}
if (!function_exists('getOperateSeq'))
{
    function getOperateSeq ( $type )
    {
        return App\Dao\SeqDao::getOperateSeq( $type );
    }
}
if (!function_exists('getCardSeq'))
{
    function getCardSeq ( $count )
    {
        return App\Dao\SeqDao::getCardSeq( $count );
    }
}
if (!function_exists('getCustomerSeq'))
{
    function getCustomerSeq ()
    {
        return App\Dao\SeqDao::getCustomerSeq();
    }
}
if (!function_exists('priceShow'))
{
    function priceShow( $price )
    {
        if(empty( $price ))
            return 0;
        else
            return number_format( $price/100, 2 );
    }
}
if (!function_exists('getNonceStr'))
{
    /**
     *
     * 产生随机字符串，不长于32位
     * @param int $length
     * @return 产生的随机字符串
     */
    function getNonceStr($length=32)
    {
        $chars = "abcdefghjklmnpqrstuvwxyz0123456789";
        $str   = "";
        for ( $i = 0; $i < $length; $i++ )  {
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }
}
