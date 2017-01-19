<?php

namespace App\Http\Controllers\Storage;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\{Input,DB,Validator};

use App\Model\{Storage,City};

use Lib\PublicClass\{S,Log};
use App\Dao\{StorageDao};

class StorageController extends Controller
{
    public function list()
    {
        $get = Input::all();
        S::setUrlParam();
        $userStorageId = session( 'user.storage_id' );
        $storages = Storage::where( 'node_id', session( 'user.node_id' ))
            -> whereRaw( "FIND_IN_SET( {$userStorageId}, `full_id` )" )
            -> orderBy( 'level', 'asc' )
            -> orderBy( 'id', 'asc' );

        empty( $get['id'] )    || $storages-> where( 'id', $get['id'] );
        empty( $get['level'] ) || $storages-> where( 'level', $get['level'] );
        if( !empty( $get['status'] ) && $get['status'] == 'off' )
        {
            $storages-> where( 'status', Storage::STATUS_OFF );
        }
        elseif ( empty( $get['status'] ) || $get['status'] == 'on' )
        {
            $storages-> where( 'status', Storage::STATUS_ON );
        }

        $storages = $storages-> paginate(15);
        $levels   = Storage::where( 'node_id', session( 'user.node_id' ))
            -> select( 'level' )
            -> whereRaw( "FIND_IN_SET( {$userStorageId}, `full_id` )" )
            -> where( 'status', Storage::STATUS_ON )
            -> groupBy( 'level' )
            -> orderBy( 'level' )
            -> get()
            -> toArray();
        return view( 'storage.storages', [
            'storages'=> $storages,
            'get'     => $get,
            'levels'  => $levels,
            'select'  => json_encode( StorageDao::getStorages(), JSON_UNESCAPED_UNICODE )
        ]);
    }

    public function edit()
    {
        $id            = Input::get('id');
        $userStorageId = session( 'user.storage_id' );

        $province = City::where('city_level',1)-> orderBy( 'province' )-> get()-> toArray();

        $param = [
            'storage' => [],
            'urlParam'=> S::getUrlParam(),
            'province'=> $province,
            'storages'=> json_encode( StorageDao::getStorages(), JSON_UNESCAPED_UNICODE )
        ];

        if( !empty( $id ))
        {
            $storage = Storage::leftJoin( 'storage as b', 'storage.parent_id', '=', 'b.id' )
                -> select( 'storage.*','b.name as parent_name' )
                -> where( 'storage.node_id', session( 'user.node_id' ))
                -> whereRaw( "FIND_IN_SET( {$userStorageId}, lv_storage.`full_id` )" )
                -> where( 'storage.id', $id )
                -> first();
            if( empty( $storage ) ) abort(403);
            $param['storage'] = $storage-> toArray();
            $param['city']    = City::where( 'city_level', 2 )
                -> where( 'province_code', $storage-> province_code )
                -> orderBy( 'city' )
                -> get()
                -> toArray();
            $param['town']    = City::where( 'city_level', 3 )
                -> where( 'city_code', $storage-> city_code )
                -> orderBy( 'town' )
                -> get()
                -> toArray();
        }

        return view( 'storage.storage_edit', $param );
    }

    public function show()
    {
        $id            = Input::get('id');
        $userStorageId = session( 'user.storage_id' );

        if( empty( $id )) abort( 403, '缺少ID' );

        $storage = Storage::leftJoin( 'storage as b', 'storage.parent_id', '=', 'b.id' )
            -> select( 'storage.*','b.name as parent_name' )
            -> where( 'storage.node_id', session( 'user.node_id' ))
            -> whereRaw( "FIND_IN_SET( {$userStorageId}, lv_storage.`full_id` )" )
            -> where( 'storage.id', $id )
            -> first();
        if( empty( $storage ) ) abort( 403, '仓库不存在' );

        return view( 'storage.storage_show', [ 'urlParam'=> S::getUrlParam(), 'storage'=> $storage-> toArray() ]);
    }

    public function save()
    {
        $storage   = Input::all();
        $validator = Validator::make( $storage, [
            'name'         => 'required',
            'acronym'      => 'required',
            'parent_id'    => 'required',
            'city_code'    => 'required',
            'town_code'    => 'required',
            'address'      => 'required',
        ], [
            'name.required'         => '仓库名必须写',
            'acronym.required'      => '仓库缩写必须写',
            'parent_id.required'    => '父级仓库必须选',
            'province_code.required'=> '省份必须选',
            'city_code.required'    => '城市必须选',
            'town_code.required'    => '区必须选',
            'address.required'      => '详细地址必须写',
            'acronym.alpha'         => '缩写必须是字母',
        ]);

        $province = City::where('city_level',1)-> orderBy( 'province' )-> get()-> toArray();
        $city     = City::where( 'city_level', 2 )
            -> where( 'province_code', $storage['province_code'] )
            -> orderBy( 'city' )
            -> get()
            -> toArray();
        $town     = City::where( 'city_level', 3 )
            -> where( 'city_code', $storage['city_code'] )
            -> orderBy( 'town' )
            -> get()
            -> toArray();
        $param = [
            'storage' => $storage,
            'urlParam'=> S::getUrlParam(),
            'province'=> $province,
            'city'    => $city,
            'town'    => $town,
            'storages'=> json_encode( StorageDao::getStorages(), JSON_UNESCAPED_UNICODE )
        ];

        if( $validator-> fails() )
        {
            return view( 'storage.storage_edit', $param )-> withErrors( $validator );
        }
/*        $rule  = "/^([a-z]|[A-Z]){2,}$/A";
        preg_match($rule,$storage['acronym'], $result);
        if( empty( $result ))
        {
            $validator-> errors()-> add('error', '仓库缩写必须是英文');
            return view( 'storage.storage_edit', $param )-> withErrors( $validator );
        }*/

        $storage['acronym'] = strtoupper( $storage['acronym'] );

        //拼接SQL——判断机构下的仓库缩写是否存在
        $countDb = Storage::where( 'acronym', $storage['acronym'] )
            -> where( 'node_id', session( 'user.node_id' ));
        $nameDb  = Storage::where( 'name', $storage['name'] )
            -> where( 'node_id', session( 'user.node_id' ));

        if( !empty( $storage['id'] ))//如果存在id就是更新，先判断ID是否存在
        {
            $userStorageId = session( 'user.storage_id' );
            $dbStorage = Storage::where( 'id', $storage['id'] )
                -> where( 'node_id', session( 'user.node_id' ))
                -> whereRaw( "FIND_IN_SET( {$userStorageId}, `full_id` )" )
                -> first();
            if( empty( $dbStorage ) ) abort(403);

            $count     = $countDb-> where( 'id', '<>', $storage['id'] )-> count();
            $nameCount = $nameDb-> where( 'id', '<>', $storage['id'] )-> count();
        }
        else
        {
            $dbStorage = new Storage();
            $count = $countDb-> count();
            $nameCount = $nameDb-> count();
        }

        if( $nameCount > 0 )
        {
            $validator-> errors()-> add('error', '仓库名称重复');
            return view( 'storage.storage_edit', $param )-> withErrors( $validator );
        }
        if( $count > 0 )
        {
            $validator-> errors()-> add('error', '仓库缩写重复');
            return view( 'storage.storage_edit', $param )-> withErrors( $validator );
        }
        //获取地区信息
        $city = City::where( 'province_code', $storage['province_code'] )
            -> where( 'city_code', $storage['city_code'] )
            -> where( 'town_code', $storage['town_code'] )
            -> first();
        if( empty( $city )) abort(403,'城市不存在');

        $dbStorage-> name          = $storage['name'];
        $dbStorage-> node_id       = session( 'user.node_id' );
        $dbStorage-> province_code = $city-> province_code;
        $dbStorage-> province      = $city-> province;
        $dbStorage-> city_code     = $city-> city_code;
        $dbStorage-> city          = $city-> city;
        $dbStorage-> town_code     = $city-> town_code;
        $dbStorage-> town          = $city-> town;
        $dbStorage-> address       = $storage['address'];
        $dbStorage-> acronym       = $storage['acronym'];
        $dbStorage-> custom_id     = $storage['custom_id'];

        //判断父级仓库是否存在，判断仓库层级是否超过最大值
        if( empty( $storage['id'] ))
        {
            $parent = Storage::where( 'id', $storage['parent_id'] )
                -> where( 'status', Storage::STATUS_ON )
                -> first();
            if( empty( $parent ))
            {
                $validator-> errors()-> add('error', '父级仓库不存在或者已经关闭');
                return view( 'storage.storage_edit', $param )-> withErrors( $validator );
            }

            $level = $parent-> level + 1;
            if( $level > 9 )
            {
                $validator-> errors()-> add('error', '仓库层级已经超过最大限制');
                return view( 'storage.storage_edit', $param )-> withErrors( $validator );
            }

            $dbStorage-> level     = $level;
            $dbStorage-> parent_id = $storage['parent_id'];
        }

        DB::beginTransaction();

        try
        {
            $dbStorage-> save();

            if( empty( $storage['id'] ))
            {
                $dbStorage-> full_id = $parent-> full_id . ','. $dbStorage-> id;
                $dbStorage-> save();
                StorageDao::setDefaultSeq( $dbStorage-> id );
            }
            StorageDao::delCache();
            DB::commit();
        }
        catch ( \Exception $e )
        {
            DB::rollBack();
            $validator-> errors()-> add('error', '数据库写入失败，请重新提交');
            return view( 'storage.storage_edit', $param )-> withErrors( $validator );
        }

        return redirect('/storage/list'.S::getUrlParam())-> with( promptMsg( '保存成功', 1 ));
    }
    public function state()
    {
        $id    = Input::get('id');
        $state = Input::get('state');

        $validator = Validator::make( Input::all(), [
            'id'   => 'required',
            'state'=> 'required',
        ], [
            'id.required'   => 'id必须写',
            'state.required'=> '变更状态必须写',
        ]);
        if( $validator-> fails() )
        {
            return redirect('/storage/list'.S::getUrlParam())-> withErrors( $validator );
        }

        $userStorageId = session( 'user.storage_id' );
        $storage = Storage::where( 'node_id', session( 'user.node_id' ))
            -> whereRaw( "FIND_IN_SET( {$userStorageId}, `full_id` )" )
            -> where( 'id', $id )
            -> first();
        if( empty( $storage )) abort(403);
        if( $storage-> level == 1 )
        {
            return redirect('/storage/list'.S::getUrlParam())-> with( promptMsg( '一级仓库不能停用', 3 ));
        }

        switch ( $state )
        {
            case 'off':
                $data = Storage::where( 'node_id', session( 'user.node_id' ))
                    -> whereRaw( "FIND_IN_SET( {$userStorageId}, `full_id` )" )
                    -> get()
                    -> toArray();

                $childId = getChildsId( $data, $id, 'parent_id' );
                sort( $childId );

                $idArr = array_merge( [$storage-> id], $childId );
                try
                {
                    Storage::whereIn( 'id', $idArr )
                        -> update([ 'status'=> Storage::STATUS_OFF, 'updated_at'=> date('Y-m-d H:i:s') ]);
                }
                catch ( \Exception $e )
                {
                    return redirect('/storage/list'.S::getUrlParam())-> with( promptMsg( '数据库存在失败-'.$e->getTraceAsString(), 4 ));
                }
                break;
            case 'on':
                $storage-> status = Storage::STATUS_ON;
                try
                {
                    $storage-> save();
                }
                catch ( \Exception $e )
                {
                    return redirect('/storage/list'.S::getUrlParam())-> with( promptMsg( '数据库存在失败-'.$e->getTraceAsString(), 4 ));
                }
                break;
        }
        StorageDao::delCache();
        return redirect('/storage/list'.S::getUrlParam())-> with( promptMsg( '修改成功', 1 ));
    }
}
