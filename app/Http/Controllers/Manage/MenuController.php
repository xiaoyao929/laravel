<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\{Input,DB};
use Validator;

use App\Model\{Menu,Permission};

class MenuController extends Controller
{
    public function menus()
    {
        $data = Menu::leftJoin( 'permissions as b', 'menu.permission_id', '=', 'b.id' )
            -> select( 'menu.*', 'b.name as url' )
            -> orderBy( 'parent_id', 'asc' )
            -> orderBy( 'sort', 'asc' )
            -> get()
            -> toArray();

        $menus = [];

        foreach ( $data as $menu )
        {
            if( $menu['parent_id'] == 0 )
            {
                $menus[$menu['id']] = $menu;
            }
            else
            {
                $menus[$menu['parent_id']]['menus'][] = $menu;
            }
        }

        return view( 'manage.menus', ['menus'=> $menus]);
    }
    public function edit()
    {
        $id     = Input::get('id');
        $menu   = [];
        $parent = Menu::where( 'parent_id', 0 )
            -> orderBy( 'sort', 'asc' )
            -> get();

        $permission = Permission::leftJoin( 'menu as b', 'permissions.menu_parent', '=', 'b.id' )
            -> select( 'permissions.id', 'permissions.name', 'permissions.display_name' )
            -> orderBy( 'b.sort' )
            -> orderBy( 'permissions.name' )
            -> get()
            -> toArray();

        if( !empty( $id ))
        {
            $menu = Menu::where( 'id', $id )-> first();
            if( empty( $menu ) ) abort(403);
            $menu = $menu-> toArray();
        }

        return view( 'manage.menu_edit', [ 'menu'=> $menu, 'parent'=> $parent, 'permission'=> $permission ]);
    }
    public function save()
    {
        $menu      = Input::all();
        $parent    = Menu::where( 'parent_id', 0 )-> get();
        $validator = Validator::make( $menu, [
            'name'     => 'required',
            'parent_id'=> 'required',
            'sort'     => 'required',
        ], [
            'name.required'     => '名称必须写',
            'parent_id.required'=> '父ID必须写',
            'sort.required'     => '排序必须写',
        ]);

        $permission = Permission::select( 'id', 'name', 'display_name' )
            -> get()
            -> toArray();

        if( $validator-> fails() )
        {
            return view( 'manage.menu_edit', [ 'menu'=> $menu, 'parent'=> $parent, 'permission'=> $permission ] )-> withErrors( $validator );
        }

        if( !empty( $menu['id'] ))
        {
            $dbMenu = Menu::where( 'id', $menu['id'] )-> first();
            if( empty( $dbMenu ) ) abort(403);

        }
        else
        {
            $dbMenu = new Menu();
        }
        $dbMenu-> name          = $menu['name'];
        $dbMenu-> parent_id     = $menu['parent_id'];
        $dbMenu-> permission_id = $menu['permission_id'];
        $dbMenu-> sort          = $menu['sort'];
        $dbMenu-> icon          = $menu['icon'];
        $dbMenu-> prefix        = empty( $menu['prefix'] ) ? null : $menu['prefix'];
        try
        {
            $dbMenu-> save();
        }
        catch ( \Exception $e )
        {
            $validator-> errors()-> add('error', $e-> getTraceAsString());
            return view( 'manage.menu_edit', [ 'menu'=> $menu, 'parent'=> $parent, 'permission'=> $permission ] )-> withErrors( $validator );
        }
        return redirect('/manage/menus')-> with( promptMsg( '保存成功', 1 ));
    }
    public function visiable()
    {
        $id       = Input::get('id');
        $visiable = Input::get('visiable');

        $validator = Validator::make( Input::all(), [
            'id'      => 'required',
            'visiable'=> 'required',
        ], [
            'id.required'      => 'id必须写',
            'visiable.required'=> '变更状态必须写',
        ]);
        if( $validator-> fails() )
        {
            return redirect('/manage/menus')-> withErrors( $validator );
        }

        $menu = Menu::where( 'id', $id )-> first();

        if( empty( $menu )) return redirect('/manage/menus')-> with( promptMsg( '用户不存在', 3 ));
        if( $visiable == 'on' )
        {
            if( $menu-> visiable == 0 )
            {
                $menu-> visiable = 1;
                try
                {
                    $menu-> save();
                }
                catch ( \Exception $e )
                {
                    return redirect('/manage/menus')-> with( promptMsg( '数据库存在失败-'.$e->getTraceAsString(), 4 ));
                }
            }
        }
        elseif ( $visiable == 'off' )
        {
            if( $menu-> visiable == 1 )
            {
                $menu-> visiable = 0;
                try
                {
                    $menu-> save();
                }
                catch ( \Exception $e )
                {
                    return redirect('/manage/menus')-> with( promptMsg( '数据库存在失败-'.$e->getTraceAsString(), 4 ));
                }
            }
        }
        return redirect('/manage/menus')-> with( promptMsg( '修改成功', 1 ));
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
            return redirect('/manage/menus')-> withErrors( $validator );
        }
        $menu = Menu::where( 'id', $id )-> first();

        if( empty( $menu )) return redirect('/manage/menus')-> with( promptMsg( '菜单不存在', 3 ));

        DB::beginTransaction();

        try
        {
            Menu::where( 'id', $id )-> delete();
            if( $menu-> parent_id == 0 )
            {
                Menu::where( 'parent_id', $id )-> delete();
            }
            DB::commit();
        }
        catch ( \Exception $e )
        {
            DB::rollback();
            return redirect('/manage/menus')-> with( promptMsg( '数据库存在失败-'.$e->getTraceAsString(), 4 ));
        }
        return redirect('/manage/menus')-> with( promptMsg( '删除成功', 1 ));
    }
}
