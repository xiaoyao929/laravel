<?php
namespace Lib\PublicClass;

/**
 * Xml解析类 v1.13 @edit date 2016-06-27 @auth by 严慧亮
 */
class Xml {
    private static $errorMsg; //错误信息
    private static $value;    //解析后的集合
    private static $special = ['CreateTime'];

    /**
     * 返回错误
     * @return mixed
     */
    public static function error()
    {
        return self::$errorMsg;
    }

    /**
     * 数组转xml
     * @param $data
     * @param string $encoding
     * @param null $root
     * @param string $mode
     * @return mixed|string
     */
    public static function getXMLFromArray( $data, $encoding='utf-8', $root = null, $mode='' )
    {
        switch ( $mode )
        {
            case 'wechat':
                $xml = self::_getXmlFromArrayToWechat($data);
                break;
            default:
                $xml = self::_getXMLFromArray($data);
        }

        if ($root != null) {
            $xml = sprintf($root, $xml);
        }
        // 转换编码
        if (strtolower($encoding) != 'utf-8') {
            $xml = mb_convert_encoding($xml, $encoding, 'utf-8,gbk');
        }
        $xml = "<?xml version='1.0' encoding='" . $encoding . "'?>\n\n" . $xml;
        return $xml;
    }

    /**
     * 数组转xml无头部
     * @param $data
     * @param string $encoding
     * @param null $root
     * @param string $mode
     * @return mixed|string
     */
    public static function getXMLFromArrayNoHeader($data, $encoding='utf-8', $root = null, $mode='' )
    {
        switch ( $mode )
        {
            case 'wechat':
                $xml = self::_getXmlFromArrayToWechat($data);
                break;
            default:
                $xml = self::_getXMLFromArray($data);
        }
        if ($root != null) {
            $xml = sprintf($root, $xml);
        }
        // 转换编码
        if (strtolower($encoding) != 'utf-8') {
            $xml = mb_convert_encoding($xml, $encoding, 'utf-8,gbk');
        }
        return $xml;
    }

    /**
     * 微信用数组转xml
     * @param $data
     * @param string $prefix
     * @param string $lineFeed
     * @return string
     */
    private static function _getXmlFromArrayToWechat( $data, $prefix='', $lineFeed='' )
    {
        if( empty( $prefix )) $prefix = 'CDATA';
        $str = "";
        foreach ( $data as $key=> $value )
        {
            if (! is_array($value)) {
                if( in_array( $key, self::$special ) )
                {
                    $str .= "<{$key}>$value</{$key}>{$lineFeed}";
                }
                else
                {
                    $str .= "<{$key}><![{$prefix}[$value]]></{$key}>{$lineFeed}";
                }
            } else {
                if (isset($value[0])) {
                    foreach ($value as $v) {
                        $str .= "<{$key}>" . self::_getXMLFromArray($v) .
                            "</{$key}>{$lineFeed}";
                    }
                } else {
                    $str .= "<{$key}>{$lineFeed}". self::_getXMLFromArray($value) ."</{$key}>{$lineFeed}";
                }
            }
        }
        return $str;
    }

    /**
     * 数组转xml
     * @param $data
     * @param string $lineFeed
     * @return string
     */
    private static function _getXMLFromArray( $data, $lineFeed='' )
    {
        $str = "";
        foreach ($data as $key => $value) {
            if (! is_array($value)) {
                $str .= "<{$key}>$value</{$key}>{$lineFeed}";
            } else {
                if (isset($value[0])) {
                    foreach ($value as $v) {
                        $str .= "<{$key}>" . self::_getXMLFromArray($v) .
                            "</{$key}>{$lineFeed}";
                    }
                } else {
                    $str .= "<{$key}>{$lineFeed}". self::_getXMLFromArray($value) ."</{$key}>{$lineFeed}";
                }
            }
        }
        return $str;
    }

    /**
     * 解析xml
     * @param $xml
     * @return bool
     */
    public static function fromXml( $xml )
    {
        if( empty( $xml ))
        {
            self::$errorMsg = 'XML内容为空';
            return false;
        }
        if (preg_match('/<?xml.*encoding=[\'"](.*?)[\'"].*?>/m', $xml, $m))
        {
            $encoding        = strtoupper($m[1]);
            if( $encoding == 'GBK' )
            {
                $xml = iconv("GBK", "utf-8//IGNORE", $xml) ;
                $xml = str_replace( 'GBK', 'utf-8', $xml );
            }

        }

        $xml = get_magic_quotes_gpc() ? stripslashes($xml) : $xml;
        try{
            $xml = simplexml_load_string( $xml, 'SimpleXMLElement', LIBXML_NOCDATA );
        }
        catch ( \Exception $e )
        {
            self::$errorMsg = 'XML解析失败';
            return false;
        }

        if( empty( $xml ))
        {
            self::$errorMsg = 'XML解析失败';
            return false;
        }
        $data = json_decode( json_encode( $xml ), true);
        //避开空节点自动转换成数组类型
        foreach ($data as $key => $value) {
            if(is_array($value) && empty($value) ){
                $data[$key] = '';
            }
        }
        self::$value = $data;
        return true;
    }

    /**
     * 返回解析结果
     * @return mixed
     */
    public static function getValue()
    {
        return self::$value;
    }
}

?>
