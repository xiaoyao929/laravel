<?php

namespace App\Dao;

use App\Model\{City};
use Illuminate\Support\Facades\{Input};


class AjaxDao
{
    public static function getCity()
    {
        $code = Input::get('code');
        $city = City::where('city_level',2)
            -> where( 'province_code', $code )
            -> select( 'city', 'city_code' )
            -> orderBy( 'city' )
            -> get()
            -> toArray();
        return $city;
    }
    public static function getTown()
    {
        $code = Input::get('code');
        $town = City::where('city_level',3)
            -> where( 'city_code', $code )
            -> select( 'town', 'town_code' )
            -> orderBy( 'town' )
            -> get()
            -> toArray();
        return $town;
    }
}