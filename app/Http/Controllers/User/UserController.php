<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\{Input,Validator};

use App\Model\{Users,Role,Storage};

use Lib\PublicClass\{S};
use App\Dao\{StorageDao,RoleDao};

class UserController extends Controller
{
    /**
     * 用户列表
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function list()
    {
        S::setUrlParam();
        $get = Input::all();

        //显示所属机构下，所属仓库包含下级仓库的用户
        $userStorageId = session( 'user.storage_id' );
        $users = Users::leftJoin( 'role_user as b', 'users.id', '=', 'b.user_id' )
            -> leftJoin( 'roles as c', 'b.role_id', '=', 'c.id' )
            -> join( 'storage as d', 'users.storage_id', '=', 'd.id' )
            -> select( 'users.*', 'c.name as role_name', 'd.name as storage_name', 'd.level as storage_level' )
            -> where( 'users.node_id', session( 'user.node_id' ))
            -> whereRaw( "FIND_IN_SET( {$userStorageId}, lv_d.`full_id` )" )
            -> orderBy( 'users.is_admin', 'desc' )
            -> orderBy( 'd.level', 'asc' )
            -> orderBy( 'users.storage_id', 'asc' );

        if( !empty( $get['status'] ) && $get['status'] == 'off' )
        {
            $users-> where( 'users.status', Users::STATUS_OFF );
        }
        elseif ( empty( $get['status'] ) || $get['status'] == 'on' )
        {
            $users-> where( 'users.status', Users::STATUS_ON );
        }

        empty( $get['account'] )    || $users-> where( 'users.account', 'like', "%{$get['account']}%" );
        empty( $get['nickname'] )   || $users-> where( 'users.nickname', 'like', "%{$get['nickname']}%" );
        empty( $get['storage_id'] ) || $users-> where( 'd.id', $get['storage_id'] );
        empty( $get['role_id'] )    || $users-> where( 'c.id', $get['role_id'] );

        $users= $users-> paginate(15);

        return view('user.users', [
            'users' => $users,
            'get'   => $get,
            'select'=> json_encode( StorageDao::getStorages(), JSON_UNESCAPED_UNICODE ),
            'roles' => RoleDao::getRoles()
        ]);
    }

    /**
     * 用户编辑
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit()
    {
        $id    = Input::get('id');
        $user  = [];

        if( !empty( $id ))
        {
            $userStorageId = session( 'user.storage_id' );
            $user = Users::join( 'storage as b', 'users.storage_id', '=', 'b.id' )
                -> leftJoin( 'role_user as c', 'users.id', '=', 'c.user_id' )
                -> where( 'users.id', $id )
                -> where( 'users.node_id', session( 'user.node_id' ))
                -> whereRaw( "FIND_IN_SET( {$userStorageId}, lv_b.`full_id` )" )
                -> select( 'users.*', 'c.role_id', 'b.name as storage_name' )
                -> first();
            if( empty( $user ) ) abort(403);
            $user = $user-> toArray();
        }

        return view( 'user.user_edit', [
            'user'    => $user,
            'urlParam'=> S::getUrlParam(),
            'roles'   => RoleDao::getRoles(),
            'storages'=> json_encode( StorageDao::getStorages(), JSON_UNESCAPED_UNICODE )
        ]);
    }

    public function show()
    {
        $id = Input::get('id');

        if( empty( $id )) abort( 403, '缺少ID' );

        $userStorageId = session( 'user.storage_id' );
        $user = Users::join( 'storage as b', 'users.storage_id', '=', 'b.id' )
            -> leftJoin( 'role_user as c', 'users.id', '=', 'c.user_id' )
            -> leftJoin( 'roles as d', 'c.role_id', '=', 'd.id' )
            -> where( 'users.id', $id )
            -> where( 'users.node_id', session( 'user.node_id' ))
            -> whereRaw( "FIND_IN_SET( {$userStorageId}, lv_b.`full_id` )" )
            -> select( 'users.*', 'd.name as role_name', 'b.name as storage_name' )
            -> first();
        if( empty( $user )) abort( 403, '用户不存在' );

        return view( 'user.user_show', [
            'user'    =>  $user-> toArray(),
            'urlParam'=> S::getUrlParam()
        ]);
    }

    /**
     * 用户保存
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function save()
    {
        $user = Input::all();
        $userStorageId = session( 'user.storage_id' );

        $param = [
            'user'    => $user,
            'urlParam'=> S::getUrlParam(),
            'roles'   => RoleDao::getRoles(),
            'storages'=> json_encode( StorageDao::getStorages(), JSON_UNESCAPED_UNICODE )
        ];

        if( !empty( $user['id'] ))
        {
            $dbUser = Users::join( 'storage as b', 'users.storage_id', '=', 'b.id' )
                -> leftJoin( 'role_user as c', 'users.id', '=', 'c.user_id' )
                -> where( 'users.id', $user['id'] )
                -> where( 'users.node_id', session( 'user.node_id' ))
                -> whereRaw( "FIND_IN_SET( {$userStorageId}, lv_b.`full_id` )" )
                -> select( 'users.*', 'c.role_id' )
                -> first();

            if( empty( $dbUser ) ) abort(403);
            if( $dbUser-> is_admin == 1 )
            {
                $validator = Validator::make( $user, [
                    'id'        => 'required',
                    'nickname'  => 'required',
                ], [
                    'id.required'        => 'id必须写',
                    'nickname.required'  => '昵称必须写',
                ]);
            }
            else
            {
                $validator = Validator::make( $user, [
                    'id'        => 'required',
                    'nickname'  => 'required',
                    'role_id'   => 'required',
                    'storage_id'=> 'required',
                ], [
                    'id.required'        => 'id必须写',
                    'nickname.required'  => '昵称必须写',
                    'role_id.required'   => '分组必须填',
                    'storage_id.required'=> '所属仓库必须填',
                ]);
            }
            $user['is_admin'] = $dbUser-> is_admin;
        }
        else
        {
            $validator = Validator::make( $user, [
                'nickname'  => 'required',
                'account'   => 'required',
                'password'  => 'required',
                'role_id'   => 'required',
                'storage_id'=> 'required',
            ], [
                'nickname.required'  => '昵称必须写',
                'account.required'   => '账号必须写',
                'password.required'  => '密码必须写',
                'role_id.required'   => '分组必须填',
                'storage_id.required'=> '所属仓库必须填',
            ]);
            $dbUser = new Users();
        }

        if( $validator-> fails() )
        {
            return view( 'user.user_edit', $param )-> withErrors( $validator );
        }

        if( !empty( $user['password'] ))
        {
            //验证密码规则
            $passAction = true;
            $rules1 = '/[A-Z]+/';
            $rules2 = '/[a-z]+/';
            $rules3 = '/[0-9]+/';
            $rules4 = '/.{8,}/';
            if( !preg_match( $rules1, $user['password'] )) $passAction = false;
            if( !preg_match( $rules2, $user['password'] )) $passAction = false;
            if( !preg_match( $rules3, $user['password'] )) $passAction = false;
            if( !preg_match( $rules4, $user['password'] )) $passAction = false;

            if( !$passAction )
            {
                $validator-> errors()-> add( 'error', '密码不符合规范，必须包含大小写与数字！并且大于8位' );
                return view( 'user.user_edit', $param )-> withErrors( $validator );
            }
        }

        if( !empty( $user['is_admin'] ) && $user['is_admin'] == 1 ) //如果是超级管理员
        {
            $role = Role::where( 'id', 1 )-> first();
            $storage = Storage::where( 'node_id', session( 'user.node_id' ))
                -> where( 'level', 1 )
                -> where( 'status', Storage::STATUS_ON )
                -> first();
        }
        else//普通用户
        {
            $role = Role::where( 'id', $user['role_id'] )
                -> where( 'node_id', session( 'user.node_id' ))
                -> first();

            $storage = Storage::where( 'id', $user['storage_id'] )
                -> where( 'node_id', session( 'user.node_id' ))
                -> whereRaw( "FIND_IN_SET( {$userStorageId}, `full_id` )" )
                -> where( 'status', Storage::STATUS_ON )
                -> first();
        }
        //验证分组
        if( empty( $role ) )
        {
            $validator-> errors()-> add( 'error', '分组不存在' );
            return view( 'user.user_edit', $param )-> withErrors( $validator );
        }
        //验证仓库
        if( empty( $storage ) )
        {
            $validator-> errors()-> add( 'error', '仓库不存在' );
            return view( 'user.user_edit', $param )-> withErrors( $validator );
        }

        //验证用户是否存在
        $sqlObj = Users::where( 'account', $user['account'] );
        if( !empty( $user['id'] )) $sqlObj-> where( 'id', '<>', $user['id'] );
        $re = $sqlObj-> count();
        if( $re > 0 )
        {
            $validator-> errors()-> add('error', '用户名重复');
            return view( 'user.user_edit', $param )-> withErrors( $validator );
        }

        $dbUser-> node_id    = session('user.node_id');
        $dbUser-> nickname   = $user['nickname'];
        $dbUser-> tel        = $user['tel'];
        $dbUser-> storage_id = $storage-> id;
        !empty( $user['id'] )      || $dbUser-> account  = $user['account'];
        empty( $user['password'] ) || $dbUser-> password = md5( $user['password'] );

        try
        {
            $dbUser-> save();
            if( !empty( $user['id'] ) && !empty( $dbUser-> role_id ))
                $dbUser-> roles()-> updateExistingPivot( $dbUser-> role_id, ['role_id'=> $role-> id] );
            else
                $dbUser-> roles()-> attach( $role-> id );
        }
        catch ( \Exception $e )
        {
            $validator-> errors()-> add('error', $e-> getTraceAsString());
            return view( 'user.user_edit', $param )-> withErrors( $validator );
        }
        return redirect('/user/list'.S::getUrlParam())-> with( promptMsg( '保存成功', 1 ));
    }

    /**
     * 用户状态修改
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function status()
    {
        $id     = Input::get('id');
        $status = Input::get('status');

        $validator = Validator::make( Input::all(), [
            'id'    => 'required',
            'status'=> 'required',
        ], [
            'id.required'    => 'id必须写',
            'status.required'=> '变更状态必须写',
        ]);
        if( $validator-> fails() )
        {
            return redirect('/user/list'.S::getUrlParam())-> withErrors( $validator );
        }

        $userStorageId = session( 'user.storage_id' );
        $user = Users::join( 'storage as b', 'users.storage_id', '=', 'b.id' )
            -> where( 'users.id', $id )
            -> where( 'users.node_id', session( 'user.node_id' ))
            -> whereRaw( "FIND_IN_SET( {$userStorageId}, lv_b.`full_id` )" )
            -> select( 'users.*' )
            -> first();

        if( empty( $user )) return redirect('/user/list'.S::getUrlParam())-> with( promptMsg( '用户不存在', 3 ));
        if( $status == 'on' )
        {
            if( $user-> status == 1 )
            {
                $user-> status = 0;
                try
                {
                    $user-> save();
                }
                catch ( \Exception $e )
                {
                    return redirect('/user/list'.S::getUrlParam())-> with( promptMsg( '数据库存在失败-'.$e->getTraceAsString(), 4 ));
                }
            }
        }
        elseif ( $status == 'off' )
        {
            if( $user-> status == 0 )
            {
                $user-> status = 1;
                try
                {
                    $user-> save();
                }
                catch ( \Exception $e )
                {
                    return redirect('/user/list'.S::getUrlParam())-> with( promptMsg( '数据库存在失败-'.$e->getTraceAsString(), 4 ));
                }
            }
        }
        return redirect('/user/list'.S::getUrlParam())-> with( promptMsg( '修改成功', 1 ));
    }

    /**
     * 用户删除
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function del()
    {
        $id = Input::get('id');

        $validator = Validator::make( Input::all(), [
            'id'   => 'required',
        ], [
            'id.required'   => 'id必须写',
        ]);
        if( $validator-> fails() )
        {
            return redirect('/user/list'.S::getUrlParam())-> withErrors( $validator );
        }

        $userStorageId = session( 'user.storage_id' );
        $user = Users::join( 'storage as b', 'users.storage_id', '=', 'b.id' )
            -> where( 'users.id', $id )
            -> where( 'users.node_id', session( 'user.node_id' ))
            -> whereRaw( "FIND_IN_SET( {$userStorageId}, lv_b.`full_id` )" )
            -> select( 'users.*' )
            -> first();;

        if( empty( $user )) return redirect('/user/list'.S::getUrlParam())-> with( promptMsg( '用户不存在', 3 ));

        $user-> delete();
        return redirect('/user/list'.S::getUrlParam())-> with( promptMsg( '删除成功', 1 ));
    }
}
