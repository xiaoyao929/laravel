<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/***************支撑请求地址*****************/
//地址前要加api
//支撑制券完成后文件提交地址
Route::get('/make/finish', 'OpenApi\SupportController@finish');
Route::post('/make/finish', 'OpenApi\SupportController@finish');
//支撑验证券同步数据提交地址
Route::get('/used/coupon/flow', 'OpenApi\SupportController@usedCouponFlow');
Route::post('/used/coupon/flow', 'OpenApi\SupportController@usedCouponFlow');

Route::get('/client/info', [
    'middleware'=> 'login',
    'uses'=> 'OpenApi\ClientInfoController@info',
]);

Route::get('/day/no_sale', 'OpenApi\DayController@noSale');


