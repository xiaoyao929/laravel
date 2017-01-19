<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/', function () {
    return redirect('/home');
    //return view('welcome');
});

//无需登录
Route::group([],function(){
    Route::get('/login', 'Index\IndexController@login');
    Route::get('/login/verify/code', 'Index\IndexController@verifyCode');
    Route::post('/login/verify', 'Index\IndexController@loginVerify');
});

Route::group([ 'middleware'=> [ 'login', 'permissions' ]], function(){
    Route::get('/logout', 'Index\IndexController@logout');
    Route::get('/home', 'Index\IndexController@home');
    Route::get('/password', 'Index\IndexController@changePass');
    Route::post('/password/save', 'Index\IndexController@changePassSave');
    Route::get('/ajax', 'Index\IndexController@ajax');
    Route::post('/ajax', 'Index\IndexController@ajax');


    //退换券管理
    Route::group([ 'prefix' => 'exchange' ], function(){
        Route::group([ 'prefix' => 'replace' ], function(){
            //换券记录
            Route::get('/list', 'Replace\ReplaceController@searchList');
            Route::get('/list/show', 'Replace\ReplaceController@searchShow');

            //换券申请
            Route::get('/apply', 'Replace\ReplaceController@apply');
            Route::post('/apply/save', 'Replace\ReplaceController@applySave');

            //换券审核列表
            Route::get('/audit', 'Replace\ReplaceController@auditList');
            Route::post('/audit/save', 'Replace\ReplaceController@auditSave');
            Route::get('/audit/show', 'Replace\ReplaceController@auditShow');
        });
        Route::group([ 'prefix' => 'fallback' ], function(){
            //退券记录
            Route::get('/list', 'Fallback\FallbackController@list');

            //退券申请
            Route::get('/edit', 'Fallback\FallbackController@edit');
            Route::post('/save', 'Fallback\FallbackController@save');

            //退券审核
            Route::get('/audit/list', 'Fallback\FallbackController@auditList');
            Route::get('/audit/show', 'Fallback\FallbackController@auditShow');
            Route::post('/audit/save', 'Fallback\FallbackController@auditSave');
        });
    });

    //销售
    Route::group([ 'prefix' => 'sale' ], function(){
        //销售登记记录
        Route::get('/list', 'Sale\SaleController@searchList');
        Route::get('/list/show', 'Sale\SaleController@searchShow');

        //销售登记申请
        Route::get('/apply', 'Sale\SaleController@apply');
        Route::get('/apply/batch/temp', 'Sale\SaleController@applyBatchTemp');
        Route::post('/apply/save', 'Sale\SaleController@applySave');
        Route::post('/apply/batch/save', 'Sale\SaleController@applyBatchSave');

        //销售登记审核
        Route::get('/audit/list', 'Sale\SaleController@auditList');
        Route::get('/audit/show', 'Sale\SaleController@auditShow');
        Route::post('/audit/save', 'Sale\SaleController@auditSave');

    });

    //客户管理
    Route::group([ 'prefix' => 'client' ], function(){
        //客户
        Route::group([ 'prefix' => 'customer' ], function(){
            //客户查询
            Route::get('/list', 'Sale\CustomerController@list');

            //客户新增
            Route::get('/edit', 'Sale\CustomerController@edit');
            Route::post('/save', 'Sale\CustomerController@save');
            Route::post('/file/save', 'Sale\CustomerController@fileSave');

            //客户审核
            Route::get('/audit/list', 'Sale\CustomerController@auditList');
            Route::get('/audit/show', 'Sale\CustomerController@auditShow');
            Route::post('/audit/save', 'Sale\CustomerController@auditSave');

            //客户状态修改
            Route::get('/audit/status', 'Sale\CustomerController@status');
        });

        //内部部门
        Route::group([ 'prefix' => 'inside' ], function(){
            //内部部门查询
            Route::get('/sector/list', 'Sale\InsideSectorController@list');

            //内部部门申请
            Route::get('/sector/edit', 'Sale\InsideSectorController@edit');
            Route::post('/sector/save', 'Sale\InsideSectorController@save');
            Route::post('/sector/file/save', 'Sale\InsideSectorController@fileSave');

            //内部部门审核
            Route::get('/sector/audit/list', 'Sale\InsideSectorController@auditList');
            Route::post('/sector/audit/save', 'Sale\InsideSectorController@auditSave');
        });
    });

    //券作废
    Route::group([ 'prefix' => 'invalid' ], function(){
        //券作废记录
        Route::get('/search', 'Invalid\InvalidController@searchList');
        Route::get('/search/show', 'Invalid\InvalidController@searchShow');
        //券作废申请
        Route::get('/apply', 'Invalid\InvalidController@apply');
        Route::post('/apply/save', 'Invalid\InvalidController@applySave');

        //券作废审核
        Route::get('/audit', 'Invalid\InvalidController@auditList');
        Route::get('/audit/show', 'Invalid\InvalidController@auditShow');
        Route::post('/audit/save', 'Invalid\InvalidController@auditSave');
    });

    //系统
    Route::group([ 'prefix' => 'manage' ], function(){
        Route::get('/permissions', 'Manage\PermissionController@permissions');
        Route::get('/permission/edit', 'Manage\PermissionController@edit');
        Route::post('/permission/save', 'Manage\PermissionController@save');
        Route::get('/permission/del', 'Manage\PermissionController@del');

        Route::get('/menus', 'Manage\MenuController@menus');
        Route::get('/menu/edit', 'Manage\MenuController@edit');
        Route::post('/menu/save', 'Manage\MenuController@save');
        Route::get('/menu/del', 'Manage\MenuController@del');
        Route::get('/menu/visiable', 'Manage\MenuController@visiable');
    });
    //仓库
    Route::group([ 'prefix' => 'storage' ], function(){
        Route::get('/list', 'Storage\StorageController@list');
        Route::get('/edit', 'Storage\StorageController@edit');
        Route::post('/save', 'Storage\StorageController@save');
        Route::get('/state', 'Storage\StorageController@state');
        Route::get('/show', 'Storage\StorageController@show');
    });
    //角色
    Route::group([ 'prefix' => 'role' ], function(){
        Route::get('/list', 'Role\RoleController@list');
        Route::get('/edit', 'Role\RoleController@edit');
        Route::post('/save', 'Role\RoleController@save');
        Route::get('/del', 'Role\RoleController@del');
        Route::get('/accredit/edit', 'Role\RoleController@accredit');
        Route::post('/accredit/save', 'Role\RoleController@accreditSave');
    });
    //用户
    Route::group([ 'prefix' => 'user' ], function(){
        Route::get('/list', 'User\UserController@list');
        Route::get('/edit', 'User\UserController@edit');
        Route::post('/save', 'User\UserController@save');
        Route::get('/status', 'User\UserController@status');
        Route::get('/del', 'User\UserController@del');
        Route::get('/show', 'User\UserController@show');
    });
    //券种
    Route::group([ 'prefix' => 'type' ], function(){
        //券种列表
        Route::get('/list', 'Type\TypeController@list');
        //制作卡券
        Route::get('/edit', 'Type\TypeController@edit');
        Route::post('/save', 'Type\TypeController@save');
        //卡券状态
        Route::get('/state', 'Type\TypeController@state');
        //券种详情
        Route::get('/show', 'Type\TypeController@show');

    });
    //制券
    Route::group([ 'prefix' => 'make' ], function(){
        //制券记录
        Route::get('/list', 'Make\MakeController@list');
        //制券申请
        Route::get('/edit', 'Make\MakeController@edit');
        Route::post('/save', 'Make\MakeController@save');

        //制券审核列表
        Route::get('/audit/list', 'Make\MakeController@auditList');
        //审核列表查看单个
        Route::get('/audit/show', 'Make\MakeController@auditShow');
        //审核提交地址
        Route::post('/audit/save', 'Make\MakeController@auditSave');

    });
    //报表
    Route::group([ 'prefix' => 'stat' ], function(){
        Route::get('/no_sales', 'Stat\SaleController@noSale');
        Route::post('/no_sales/down', 'Stat\SaleController@noSaleDown');
    });
    //库存管理
    Route::group([ 'prefix' => 'inventory' ], function(){
        Route::group([ 'prefix' => 'search' ], function(){
            //未激活券查询
            Route::get('/coupon/noactivated', 'Inventory\SearchController@noActivation');
            Route::post('/coupon/file/inspect/storage', 'Inventory\SearchController@fileInspectStorage');
            //券详情查询
            Route::get('/coupon/info', 'Inventory\SearchController@couponInfo');
            //库存查询
            Route::get('/stock', 'Inventory\SearchController@stock');
        });

        Route::group([ 'prefix' => 'transfers' ], function(){
            //调拨记录查询
            Route::get('/search', 'Inventory\TransfersController@searchList');
            Route::get('/search/show', 'Inventory\TransfersController@searchShow');

            //调拨申请
            Route::get('/apply', 'Inventory\TransfersController@apply');
            Route::get('/apply/edit', 'Inventory\TransfersController@applyEdit');
            Route::post('/apply/save', 'Inventory\TransfersController@applySave');

            //调拨审核
            Route::get('/audit', 'Inventory\TransfersController@auditList');
            Route::get('/audit/show', 'Inventory\TransfersController@auditShow');
            Route::post('/audit/save', 'Inventory\TransfersController@auditSave');

            //调拨确认
            Route::get('/confirm', 'Inventory\TransfersController@confirm');
            Route::get('/confirm/show', 'Inventory\TransfersController@confirmShow');
            Route::post('/confirm/save', 'Inventory\TransfersController@confirmSave');
        });
        Route::group([ 'prefix' => 'make' ], function(){
            //新券入库记录
            Route::get('/search', 'Inventory\PutInController@searchList');
            Route::get('/search/show', 'Inventory\PutInController@searchShow');

            //新券入库审核
            Route::get('/audits', 'Inventory\PutInController@auditList');
            Route::post('/audit/save', 'Inventory\PutInController@auditSave');
            Route::get('/audit/show', 'Inventory\PutInController@auditShow');

            //新券制券完成
            Route::get('/list', 'Inventory\PutInController@makeOver');
            Route::get('/show', 'Inventory\PutInController@makeShow');
            Route::post('/save', 'Inventory\PutInController@makeSave');
        });
    });
});