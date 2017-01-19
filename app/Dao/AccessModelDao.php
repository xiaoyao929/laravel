<?php

namespace App\Dao;

use App\Model\{City};
use Illuminate\Support\Facades\Request;

class AccessModelDao
{
    private static $url = [];
    private static function getUrl()
    {
        if( empty( self::$url ))
        {
            $string     = Request::path();
            self::$url  = explode( '/', $string );
        }
    }

    public static function isApiModel():bool
    {
        $prefix = self::getPrefix();
        if( $prefix == 'api' ) return true;
        else return false;
    }
    public static function getPrefix()
    {
        self::getUrl();
        return self::$url[0];
    }
}