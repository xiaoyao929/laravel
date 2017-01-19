<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\{Input,Validator};

use App\Model\{Permission,Menu};

use Lib\PublicClass\{S};
use App\Dao\{PermissionDao};

class PermissionController extends Controller
{
    public function permissions()
    {
        $permissions = Permission::leftJoin( 'menu as b', 'permissions.menu_parent', '=', 'b.id' )
            -> select( 'permissions.*', 'b.name as menu_name' )
            -> orderBy( 'b.sort' )
            -> orderBy( 'permissions.name' )
            -> paginate(15);

        return view(
            'manage.permissions', ['permissions'=> $permissions]
        );
    }
    public function edit()
    {
        $id         = Input::get('id');
        $permission = [];
        S::setUrlParam();

        $menus = Menu::where( 'parent_id', '0' )
            -> select( 'id', 'name' )
            -> orderBy( 'sort' )
            -> get()
            -> toArray();

        if( !empty( $id ))
        {
            $permission = Permission::where( 'id', $id )-> first();
            if( empty( $permission ) ) abort(403);
            $permission = $permission-> toArray();
        }

        return view( 'manage.permission_edit', [ 'permission'=> $permission, 'menus'=> $menus ]);
    }
    public function save()
    {
        $permission = Input::all();
        $validator  = Validator::make( $permission, [
            'name'        => 'required',
            'display_name'=> 'required',
            'menu_parent' => 'required',
        ], [
            'name.required'        => '地址必须写',
            'display_name.required'=> '说明必须写',
            'menu_parent.required' => '父级菜单必须写',
        ]);

        $menus = Menu::where( 'parent_id', '0' )
            -> select( 'id', 'name' )
            -> orderBy( 'sort' )
            -> get()
            -> toArray();

        if( $validator-> fails() )
        {
            return view( 'manage.permission_edit', [ 'permission'=> $permission, 'menus'=> $menus ] )-> withErrors( $validator );
        }
        if( !empty( $permission['id'] ))
        {
            $dbPermission = Permission::where( 'id', $permission['id'] )-> first();
            if( empty( $dbPermission ) ) abort(403);

        }
        else
        {
            $dbPermission = new Permission();
        }
        $dbPermission-> name         = $permission['name'];
        $dbPermission-> display_name = $permission['display_name'];
        $dbPermission-> description  = $permission['description'];
        $dbPermission-> menu_parent  = $permission['menu_parent'];
        try
        {
            $dbPermission-> save();
            PermissionDao::delCache();
        }
        catch ( \Exception $e )
        {
            $validator-> errors()-> add('error', $e-> getTraceAsString());
            return view( 'manage.permission_edit', [ 'permission'=> $permission, 'menus'=> $menus ] )-> withErrors( $validator );
        }
        return redirect('/manage/permissions'.S::getUrlParam())-> with( promptMsg( '保存成功', 1 ));
    }
}
