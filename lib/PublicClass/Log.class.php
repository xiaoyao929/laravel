<?php

namespace Lib\PublicClass;

use Illuminate\Support\Facades\Config;

/**
 * Log日志类
 * @author Jacky
 *
 */
class Log
{
    // 日志级别 从上到下，由低到高
    const EMERG     = 'EMERG';  // 严重错误: 导致系统崩溃无法使用
    const ALERT     = 'ALERT';  // 警戒性错误: 必须被立即修改的错误
    const CRIT      = 'CRIT';  // 临界值错误: 超过临界值的错误，例如一天24小时，而输入的是25小时这样
    const ERR       = 'ERR';  // 一般错误: 一般性错误
    const WARN      = 'WARN';  // 警告性错误: 需要发出警告的错误
    const NOTICE    = 'NOTIC';  // 通知: 程序可以运行但是还不够完美的错误
    const INFO      = 'INFO';  // 信息: 程序输出信息
    const DEBUG     = 'DEBUG';  // 调试: 调试信息
    const SQL       = 'SQL';  // SQL：SQL语句 注意只在调试模式开启时有效

    // 日志记录方式
    const SYSTEM    = 0;
    const MAIL      = 1;
    const FILE      = 3;
    const SAPI      = 4;

    private static $format   = '[ c ]';// 日期格式

    /**
     * 判断当前环境是否使用日志
     * @return bool
     */
    private static function verifyOpen() :bool
    {
        if( strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' )
        {
            return false;
        }
        else
        {
            $arr = Config::get('app.accredit_domain');
            $now = $_SERVER['HTTP_HOST'];
            if( in_array( $now, $arr ))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }
    public static function set( $key, $value )
    {
        try
        {
            self::$$key = $value;
        }
        catch ( \Exception $e )
        {
            S::error('10004');
        }
    }
    public static function log_write( $content, $level='', $log_name='' )
    {
        $hostName  = '[' . function_exists('gethostname') ? gethostname() : php_uname('n') . ']';
        $pid       = '[PID:' . getmypid() . ']';
        $separator = Config::get('app.directory_separator');
        $first     = '[PAGE:' . S::getCurrentGroupName() . $separator . S::getCurrentModuleName() . $separator . S::getCurrentControllerName() . ']';
        $first    .= '[IP:' . S::getIp() . '][GET:' . $_SERVER['REQUEST_URI'] . '][ACTION:' . S::getCurrentControllerName() . ']';
        $level     = $hostName . $pid . $first . $level;
        if ( empty( $log_name ))
        {
            $log_name = S::getCurrentModuleName();
        }

        $destination = 'WC_MC_' . S::getCurrentGroupName() . '_' . $log_name;
        if( self::verifyOpen() )
        {
            self::write( $content, $level, $destination );
        }
        else
        {
            $array = [
                'content'    => $content,
                'level'      => $level,
                'destination'=> $destination
            ];
            if( env( 'LOG_DEBUG' ) == 'on' )
            {
                echo json_encode( $array, JSON_UNESCAPED_UNICODE );
            }
        }
    }
    /**
     * 日志直接写入
     * @static
     * @access public
     * @param string $message 日志信息
     * @param string $level  日志级别
     * @param string $destination  写入目标
     */
    private static function write( $message, $level=self::ERR, $destination='' )
    {
        $now = date( self::$format );
        //启用syslog
        $logId     = defined('LOG_PID') ? LOG_PID : '';
        $logLocal5 = defined('LOG_LOCAL5') ? LOG_LOCAL5 : 0;
        $logDebug  = LOG_DEBUG;
        openlog( $destination, $logId, $logLocal5 );
        syslog( $logDebug, $now . $level . "Messagge: $message" );
        closelog();
    }
}
?>