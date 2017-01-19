<?php

namespace Lib\PublicClass;

use Illuminate\Support\Facades\{
    Config, DB, Input, Redis
};
use Illuminate\Http\{Request};
use Session;

/**
 * 静态类
 * @author Jacky
 *
 */
class S
{
    /**
     * jsonp输出
     * @param array $data
     * @param number $errcode
     * @param string $err
     */
    public static function jsonReturn( $data, $errcode=0, $err='', $action=true )
    {
        $return = [ 'code'=>(int)$errcode, 'data'=>$data, 'msg'=>$err ];
        if( $action )
        {
            return response()-> json( $return )-> header('charset', 'utf-8');
        }
        else
        {
            header('Content-Type: application/json; charset=utf-8');
            exit( json_encode( $return, JSON_UNESCAPED_UNICODE ));
        }
    }
    /**
     * 报错
     * @param string $code
     */
    public static function error( $code, $data=null, $stop=true )
    {
        $code = (int)$code;
        $arr  = [];
        if( preg_match( "/^1\d{2,4}$/",$code ))
        {
            $arr = Config::get('error.error100');
        }
        elseif( preg_match( "/^2\d{2,4}$/",$code ))
        {
            $arr = Config::get('error.error200');
        }
        elseif( preg_match( "/^3\d{2,4}$/",$code ))
        {
            $arr = Config::get('error.error300');
        }
        elseif( preg_match( "/^4\d{2,4}$/",$code ))
        {
            $arr = Config::get('error.error400');
        }
        elseif( preg_match( "/^5\d{2,4}$/",$code ))
        {
            $arr = Config::get('error.error500');
        }
        elseif( preg_match( "/^6\d{2,4}$/",$code ))
        {
            $arr = Config::get('error.error600');
        }
        elseif( preg_match( "/^7\d{2,4}$/",$code ))
        {
            $arr = Config::get('error.error700');
        }
        elseif( preg_match( "/^8\d{2,4}$/",$code ))
        {
            $arr = Config::get('error.error800');
        }
        elseif( preg_match( "/^9\d{2,4}$/",$code ))
        {
            $arr = Config::get('error.error900');
        }
        $msg = $arr[$code];
        if(!empty( $data )) $msg = $msg . '-' . $data;

        if( $stop )
        {
            Log::log_write( 'code:'. $code . 'msg:' . $arr[$code], '', 'errorLog' );
            S::jsonReturn( null, $code, $msg, false );
        }
        else
        {
            Log::log_write( 'code:'. $code . 'msg:' . $arr[$code], '', 'errorLog' );
        }
    }

    /**
     * goods_id生成
     *
     * @return string
     */
    public static function get_goods_id():string
    {
        $data = DB::select("SELECT _nextval('goods_id') as goods_id FROM DUAL");
        if (! $data) {
            S::error('40019');
        }
        $goodsId = $data[0]->goods_id;
        return 'gw' . str_pad($goodsId, 10, '0', STR_PAD_LEFT);
    }

    /**
     * 获取IP
     * @return string
     */
    public static function getIp():string
    {
        if( !empty( $_SERVER["HTTP_CLIENT_IP"] ))
        {
            $cip = $_SERVER["HTTP_CLIENT_IP"];
        }
        elseif( !empty($_SERVER["HTTP_X_FORWARDED_FOR"] ))
        {
            $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        }
        elseif( !empty($_SERVER["REMOTE_ADDR"] ))
        {
            $cip = $_SERVER["REMOTE_ADDR"];
        }
        else{
            $cip = "无法获取！";
        }
        return $cip;
    }

    /**
     * curl获取
     * @param $url
     * @param int $second
     * @param string $mode
     * @param null $postData
     * @return mixed
     */
    public static function curl( $url, $second = 30, $mode='get', $postData=null, $useCert=false, $sslcertPath=null, $sslkeyPath=null, $ssl=false  )
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_URL, $url);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

/*       //如果有配置代理这里就设置代理
        if( $wxPayConfig-> curl_proxy_host != "0.0.0.0" && $wxPayConfig-> curl_proxy_port != 0 ){
            curl_setopt( $ch,CURLOPT_PROXY, $wxPayConfig-> curl_proxy_host );
            curl_setopt( $ch,CURLOPT_PROXYPORT, $wxPayConfig-> curl_proxy_port );
        }*/
        if ( $ssl == true )
        {
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, true );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2 );//严格校验
        }
        else
        {
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
        }
        if( $useCert == true )
        {
            //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
            curl_setopt( $ch, CURLOPT_SSLCERTTYPE,'PEM' );
            curl_setopt( $ch, CURLOPT_SSLCERT, $sslcertPath );
            curl_setopt( $ch, CURLOPT_SSLKEYTYPE,'PEM' );
            curl_setopt( $ch, CURLOPT_SSLKEY, $sslkeyPath  );
        }

        if( strtolower($mode) == 'post' )
        {
            //post提交方式
            curl_setopt( $ch, CURLOPT_POST, TRUE );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $postData );
        }

        //运行curl
        $data = curl_exec( $ch );
        //返回结果

        //$error = curl_errno( $ch );
        curl_close( $ch );
        return $data;
    }


    /**
     * curl获取
     *
     * @param      $url
     * @param null $postData
     * @param int  $second
     * @param bool $useCert
     * @param null $sslcertPath
     * @param null $sslkeyPath
     * @param bool $ssl
     *
     * @return mixed
     */
    public static function doPost($url, $postData=null, $second = 30, $useCert=false, $sslcertPath=null, $sslkeyPath=null, $ssl=false  )
    {
        return S::curl($url, $second, 'post', $postData, $useCert, $sslcertPath, $sslkeyPath, $ssl);
    }

    /**
     * curl获取
     *
     * @param      $url
     * @param null $postData
     * @param int  $second
     * @param bool $useCert
     * @param null $sslcertPath
     * @param null $sslkeyPath
     * @param bool $ssl
     *
     * @return mixed
     */
    public static function doGet($url, $second = 30, $useCert=false, $sslcertPath=null, $sslkeyPath=null, $ssl=false  )
    {
        return S::curl($url, $second, 'GET', null, $useCert, $sslcertPath, $sslkeyPath, $ssl);
    }



    /** aes 256 加密与解密
     * @param String $ostr
     * @param String $securekey
     * @param String $type encrypt, decrypt
     */
    public static function aes( $ostr, $securekey, $type = 'encrypt' )
    {
        if( $ostr == '' )
        {
            return '';
        }
        $original = ['+','/','='];
        $target   = ['-','_','*'];

        $key = $securekey;
        $iv  = strrev($securekey);
        $td  = mcrypt_module_open( 'rijndael-256', '', 'ofb', '' );
        mcrypt_generic_init( $td, $key, $iv );

        $str = '';

        switch($type)
        {
            case 'encrypt':
                $str = str_replace( $original, $target, base64_encode( mcrypt_generic( $td, $ostr )));
                break;

            case 'decrypt':
                $str = mdecrypt_generic( $td, base64_decode( str_replace( $target, $original, $ostr )));
                break;
        }

        mcrypt_generic_deinit( $td );

        return $str;
    }

    public static function unicode_encode($name)
    {
        $name = iconv('UTF-8', 'UCS-2', $name);
        $len = strlen($name);
        $str = '';
        for ($i = 0; $i < $len - 1; $i = $i + 2)
        {
            $c  = $name[$i];
            $c2 = $name[$i + 1];
            if (ord($c) > 0)
            {    // 两个字节的文字
                $str .= '\u'.base_convert(ord($c), 10, 16).base_convert(ord($c2), 10, 16);
            }
            else
            {
                $str .= $c2;
            }
        }
        return $str;
    }
    public static function unicode_decode($name)
    {
        // 转换编码，将Unicode编码转换成可以浏览的utf-8编码
        $pattern = '/([\w]+)|(\\\u([\w]{4}))/i';
        preg_match_all($pattern, $name, $matches);
        if (!empty($matches))
        {
            $name = '';
            for ($j = 0; $j < count($matches[0]); $j++)
            {
                $str = $matches[0][$j];
                if (strpos($str, '\\u') === 0)
                {
                    $code  = base_convert(substr($str, 2, 2), 16, 10);
                    $code2 = base_convert(substr($str, 4), 16, 10);
                    $c     = chr($code).chr($code2);
                    $c     = iconv('UCS-2', 'UTF-8', $c);
                    $name .= $c;
                }
                else
                {
                    $name .= $str;
                }
            }
        }
        return $name;
    }
    /**
     * 获取当前控制器名
     * @return string
     */
    public static function getCurrentControllerName() :string
    {
        $arr = explode( '/', self::getCurrentAction()['controller'] );
        return last($arr);
    }
    /**
     * 获取当前方法名
     * @return string
     */
    public static function getCurrentMethodName() :string
    {
        return self::getCurrentAction()['method'];
    }
    /**
     * 获取当前模块名
     * @return string
     */
    public static function getCurrentModuleName() :string
    {
        $arr = explode( '/', dirname( self::getCurrentAction()['controller'] ));
        return last($arr);
    }
    /**
     * 获取当前模块名
     * @return string
     */
    public static function getCurrentGroupName() :string
    {
        $arr = explode( '/', dirname( dirname( self::getCurrentAction()['controller'] )));
        return last($arr);
    }
    /**
     * 获取当前控制器与方法
     * @return array
     */
    public static function getCurrentAction() :array
    {
        $action = \Route::current()-> getActionName();
        list($class, $method) = explode('@', $action);
        return ['controller' => str_replace(  '\\', '/', $class ), 'method' => $method];
    }

    public static function sortArray( $arr )
    {
        asort( $arr );
        return array_merge( $arr );
    }
    public static function arsortArray( $arr )
    {
        arsort( $arr );
        return array_merge( $arr );
    }

    /**
     * 字符串编码转为utf-8
     * @param $str
     *
     * @return mixed|string
     */
    public static function utf8Str(&$str)
    {
        $str = function_exists('mb_convert_encoding') ? mb_convert_encoding($str,
                'utf-8', 'utf-8,gbk') : $str;
        return $str;
    }

    /**
     * 时间格式化处理
     * @author John Zeng<zengc@imageco.com.cn>
     *
     * @param $time    时间
     * @param $format  格式
     * @param $other   时分秒格式
     *
     * @return $date
     */
    public static function dateFormat($str, $format = 'Y-m-d H:i:s', $other = '') {
        $date = trim($str);
        if (!$date) {
            return false;
        }
        $date = strtotime($date);
        if (!$date) {
            return $str;
        }
        if ('' != $other) {
            $date = strtotime(date('Y-m-d', $date) . $other);
        }
        if ($format == 'defined1') {
            // 今天
            $day  = date('Ymd', $date);
            $time = date('H:i', $date);
            if ($day == date('Ymd', strtotime("-1 day"))) {
                $time = '昨天&nbsp;&nbsp;&nbsp;' . $time;
            } else if ($day != date('Ymd')) {
                $week = array(
                    '星期日',
                    '星期一',
                    '星期二',
                    '星期三',
                    '星期四',
                    '星期五',
                    '星期六',
                );
                $time = $week[date('w', $date)] . $time;
            }
            $date = $time;
        } else {
            if ('' == $format) {
                $format = 'YmdHis';
            }
            $date = date($format, $date);
            if ($other != '') {
                $date .= $other;
            }
            if (strpos($date, '1970') === 1) {
                return $str;
            }
        }

        return $date;
    }
    /**
     * @param        $params
     * @param string $label
     * @param string $fileName
     * @param string $filePath
     */
    public static function file_debug($params, $label = '', $fileName = '', $filePath = '')
    {
        if (empty($filePath)) {
            if (defined('PHP_OS') && strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
                $filePath = '/tmp/';
            } else {
                $filePath = 'd:/';
            }
        }

        if (empty($fileName)) {
            if (empty($label)) {
                $fileName = 'file_debug.log';
            } else {
                $fileName = $label;
            }
        }

        $file = $filePath . $fileName;
        if (!is_scalar($params)) {
            $params = var_export($params,1);
        }
        error_log('[date:]'.date('Y-m-d H:i:s').'|'.$label . ':' . $params . PHP_EOL, 3, $file);
    }
    /**
     * 获取根域名
     * @param $domain
     * @return string
     */
    public static function getUrlToDomain( $domain )
    {
        if( empty( $domain )) return null;
        $domain_postfix_cn_array = ["com", "net", "org", "gov", "edu", "com.cn", "cn"];
        $array_domain = explode(".", $domain);
        $array_num    = count( $array_domain ) - 1;

        if( strtolower( $domain ) == 'localhost' )
        {
            $re_domain = '';
        }
        elseif ( preg_match( '/^((25[0-5]|2[0-4]\d|[01]?\d\d?)($|(?!\.$)\.)){4}$/', $domain ) )
        {
            $re_domain = '';
        }
        elseif ( $array_domain[$array_num] == 'cn')
        {
            if ( in_array( $array_domain[$array_num - 1], $domain_postfix_cn_array ))
            {
                $re_domain = $array_domain[$array_num - 2] . "." . $array_domain[$array_num - 1] . "." . $array_domain[$array_num];
            }
            else
            {
                $re_domain = $array_domain[$array_num - 1] . "." . $array_domain[$array_num];
            }
        }
        else
        {
            $re_domain = $array_domain[$array_num - 1] . "." . $array_domain[$array_num];
        }
        return $re_domain;
    }

    /**
     * httpget参数缓存
     */
    public static function setUrlParam()
    {
        $get = Input::all();

        $user   = session('user');
        $prefix = Config::get('cache.prefix');

        if( !empty( $get ))
        {
            $urlParam = '?'. http_build_query( $get );
            //Log::log_write( 'url参数：'. $urlParam, '', 'httpUrl' );
            $param    = [ 'url_param'=> $urlParam ];
            return Redis::hSet( $prefix.':cache:url_param:' . $user['node_id'], $user['id'], json_encode( $param, JSON_UNESCAPED_UNICODE ));
        }
        else
        {
            return Redis::hDel(  $prefix.':cache:url_param:' . $user['node_id'], $user['id'] );
        }
    }

    /**
     * 获取httpget参数
     * @return Session|string
     */
    public static function getUrlParam()
    {
        $user     = session('user');
        $prefix   = Config::get('cache.prefix');
        $data     = json_decode( Redis::hGet( $prefix.':cache:url_param:'.$user['node_id'], $user['id'] ), true );
        if( !empty( $data['url_param'] ))
        {
            return $data['url_param'];
        }
        else
        {
            return '';
        }
    }
    /**
     * 生成单号
     * @param   $action         string  操作行为
     * @param   $number         string  需要多少个单号
     * @param   $field_name     string  字段名
     * @return  array
     */
    public static function serialNumber($action, $field_name = 'serial_id', $number = '1'){
        $result = [];
        $tableName = Config::get('serial.action_table')[$action];
        $lastId = DB::select("SELECT _Max(`{$field_name}`) FROM {$tableName}");
        $number = explode($action,$lastId)['1'];




        return $result;
    }




}