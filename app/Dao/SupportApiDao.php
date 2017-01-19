<?php
/**
 * 支撑接口类
 * @author 严慧亮
 *
 */

namespace App\Dao;

use Illuminate\Support\Facades\{Config};
use Lib\PublicClass\{S,Log, Xml};

class SupportApiDao
{
    public static function index( $param, $time=30 )
    {
        $config     = Config::get('support.apiConfig');
        $xmlParam   = Xml::getXMLFromArray( $param, 'gbk' );
        Log::log_write( '发往支撑的数据:'.$xmlParam, '', 'supportApi' );
        $result_xml = S::curl( $config['url'], $time, 'post', $xmlParam );
        Log::log_write( '支撑返回的数据:'.$result_xml, '', 'supportApi' );
        if( !Xml::fromXml( $result_xml ))
        {
            Log::log_write( Xml::error());
            return false;
        }
        return Xml::getValue();
    }
    public static function index2( $param, $time=30 )
    {
        $config     = Config::get('support.apiConfig');
        $xmlParam   = Xml::getXMLFromArray( $param, 'gbk' );
        Log::log_write( '发往支撑的数据:'.$xmlParam, '', 'supportApi' );
        $result_xml = S::curl( $config['url2'], $time, 'post', $xmlParam );
        Log::log_write( '支撑返回的数据:'.$result_xml, '', 'supportApi' );
        if( !Xml::fromXml( $result_xml ))
        {
            Log::log_write( Xml::error());
            return false;
        }
        return Xml::getValue();
    }

    /**
     * 制券请求(不加密)
     * @return bool|mixed
     */
    public static function makeCoupon( $param, $url, $time=30 )
    {
        $config     = Config::get('support.apiConfig');
        $xmlParam   = Xml::getXMLFromArray( $param, 'gbk' );
        Log::log_write( '发往支撑的数据:'.$xmlParam, '', 'supportApi' );
        $result_xml = S::curl( $config[$url], $time, 'post', $xmlParam );
        Log::log_write( '支撑返回的数据:'.$result_xml, '', 'supportApi' );

        if( !Xml::fromXml( $result_xml ))
        {
            Log::log_write( Xml::error());
            return false;
        }
        return Xml::getValue();
    }
    /**
     * 新增券种请求(要加密)
     * @return bool|mixed
     */
    public static function couponType( $param, $time=30 )
    {
        $config     = Config::get('support.apiConfig');
        $xmlParam   = Xml::getXMLFromArray( $param, 'gbk' );
        //加密处理
        $text = "xml=".urlencode($xmlParam)."&mac=".md5($config['macKey'] . $xmlParam . $config['macKey']);
        Log::log_write( '发往支撑的数据:'.$text, '', 'supportApi' );
        $result_xml = S::curl( $config['type_url'], $time, 'post', $text );
        Log::log_write( '支撑返回的数据:'.$result_xml, '', 'supportApi' );

        $msg = '';
        parse_str($result_xml,$resp);
        if(!isset($resp['xml'])){
            $msg = '没有xml数据返回';
        }
        if(!Xml::fromXml( $resp['xml'] )){
            Log::log_write( Xml::error());
            $msg = 'xml解析异常';
        }

        if(empty($msg)){        //正常
            $return = Xml::getValue();
        }else{                  //异常
            $return = [
                    'Status'    => [
                            'StatusCode'    => '-1',
                            'StatusText'    => $msg
                    ]
            ];
        }

        return $return;
    }

}