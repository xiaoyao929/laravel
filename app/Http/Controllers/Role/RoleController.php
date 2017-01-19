<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\{Input,DB,Validator,Cache};

use App\Model\{Role,RoleUser,Permission,PermissionRole};
use Lib\PublicClass\{S};
use App\Dao\{RoleDao};

class RoleController extends Controller
{
    public function list()
    {
        S::setUrlParam();
        $get = Input::all();

        $roles = Role::where( 'node_id', session( 'user.node_id' ));

        empty( $get['name'] ) || $roles-> where( 'name', 'like', "%{$get['name']}%" );

        $roles = $roles-> paginate(15);

        return view( 'role.roles', [ 'roles'=> $roles, 'get'=> $get ]);
    }
    public function edit()
    {
        $id   = Input::get('id');
        $role = [];
        if( !empty( $id ))
        {
            if( $id == 1 ) abort(403);
            $role = Role::where( 'id', $id )
                -> where( 'node_id', session( 'user.node_id' ))
                -> first();
            if( empty( $role ) ) abort(403);
            $role = $role-> toArray();
        }
        return view( 'role.role_edit', [ 'role'=> $role ] );
    }
    public function save()
    {
        $role = Input::all();
        $validator = Validator::make( $role, [
            'name'=> 'required',
        ], [
            'name.required'=> '组名必须写',
        ]);
        if( $validator-> fails() )
        {
            return view( 'role.role_edit', [ 'role'=> $role, 'urlParam'=> S::getUrlParam() ])-> withErrors( $validator );
        }
        if( !empty( $role['id'] ))
        {
            $dbRole = Role::where( 'id', $role['id'] )
                -> where( 'node_id', session( 'user.node_id' ))
                -> where( 'is_admin', Role::SUPER_ADMIN_OFF )
                -> first();
            if( empty( $dbRole ) ) abort(403);
        }
        else
        {
            $dbRole = new Role();
        }
        $dbRole-> name         = $role['name'];
        $dbRole-> node_id      = session( 'user.node_id' );
        $dbRole-> description  = $role['description'];
        try
        {
            $dbRole-> save();
            RoleDao::delCache();
        }
        catch ( \Exception $e )
        {
            $validator-> errors()-> add('error', $e-> getTraceAsString());
            return view( 'role.role_edit', [ 'role'=> $role, 'urlParam'=> S::getUrlParam() ])-> withErrors( $validator );
        }
        return redirect('/role/list'.S::getUrlParam())-> with( promptMsg( '保存成功', 1 ));
    }
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
            return redirect('/role/list'.S::getUrlParam())-> withErrors( $validator );
        }

        $role = Role::where( 'id', $id )
            -> where( 'node_id', session( 'user.node_id' ))
            -> where( 'is_admin', Role::SUPER_ADMIN_OFF )
            -> first();

        if( empty( $role )) return redirect('/role/list'.S::getUrlParam())-> with( promptMsg( '角色不存在', 3 ));
        DB::beginTransaction();

        try
        {
            Role::where( 'id', $id )-> delete();
            RoleUser::where( 'role_id', $id )-> delete();
            RoleDao::delCache();
            DB::commit();
        }
        catch ( \Exception $e )
        {
            DB::rollback();
            return redirect('/role/list'.S::getUrlParam())-> with( promptMsg( $e-> getTraceAsString(), 3 ));
        }

        return redirect('/role/list'.S::getUrlParam())-> with( promptMsg( '删除成功', 1 ));
    }
    public function accredit()
    {
        $id = Input::get('id');

        $role = Role::where( 'id', $id )
            -> where( 'node_id', session( 'user.node_id' ))
            -> where( 'is_admin', Role::SUPER_ADMIN_OFF )
            -> first();

        if( empty( $role )) abort( 403, '角色不存在' );

        $re = Cache::remember('permission:list', Permission::CACHE_TIME, function()
        {
            return $re = Permission::leftJoin( 'menu as b', 'permissions.menu_parent', '=', 'b.id' )
                -> select('permissions.id','permissions.display_name','permissions.menu_parent','b.name')
                -> orderBy( 'b.sort' )
                -> orderBy( 'permissions.name' )
                -> get()
                -> toArray();
        });
        $permissionRole = PermissionRole::where('role_id', $id )
            -> get()
            -> toArray();

        $arr = [];
        if( !empty( $permissionRole ))
        {
            $arr = array_pluck( $permissionRole, 'permission_id' );
        }

        $permission = [];

        foreach ( $re as $v )
        {
            if( !isset( $permission[$v['menu_parent']] ))
            {
                $permission[$v['menu_parent']] = [
                    'name'=> $v['name'],
                    'id'  => $v['menu_parent'],
                ];
                $action = true;
            }
            if( in_array( $v['id'], $arr ))
            {
                $v['checked'] = 'on';
            }
            else
            {
                $v['checked'] = 'off';
                $action = false;
            }
            if( $action )
                $permission[$v['menu_parent']]['checked'] = 'on';
            else
                $permission[$v['menu_parent']]['checked'] = 'off';

            $permission[$v['menu_parent']]['child'][] = $v;
        }
        $permission = array_merge( $permission, [] );

        return view( 'role.role_accredit', [
            'id'        => $id,
            'role'      => $role,
            'permission'=> $permission,
            'urlParam'  => S::getUrlParam()
        ]);
    }
    public function accreditSave()
    {
        $input = Input::all();

        $role = Role::where( 'id', $input['role_id'] )
            -> where( 'node_id', session( 'user.node_id' ))
            -> where( 'is_admin', Role::SUPER_ADMIN_OFF )
            -> first();
        if( empty( $role )) abort( 403, '角色不存在' );

        $sqlArr = Permission::orderBy('id')
            -> pluck('id')
            -> toArray();

        $inputArr = $input['permission'];
        $re = array_diff( $inputArr, $sqlArr );

        if( !empty( $re ))
        {
            abort( 403, '输入的权限ID不存在,ID:'.implode( ',',$re ));
        }

        $role-> perms() -> sync( $inputArr );
        try
        {
            $role-> perms() -> sync( $inputArr );
        }
        catch ( \Exception $e )
        {
            abort( 403, '数据库输入出错' );
        }
        return redirect('/role/list'.S::getUrlParam())-> with( promptMsg( '权限保存成功', 1 ));
    }
}
