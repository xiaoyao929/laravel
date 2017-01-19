@extends('temp.index')

@section('style')
    <style>

    </style>
@stop

@section('scripts')
    <script>
        $(function () {
            $(".ui-select").chosen();
        })
    </script>
    <script src="{{ URL::asset('/js/jquery.validate.min.js') }}"></script>
@stop

@section('body')
    <div class="row">
        <div class="col-sm-12">
            <section class="panel">
                <header class="panel-heading">
                    菜单编辑
                </header>
                <div class="panel-body">
                    <div class="form">
                        <form class="cmxform form-horizontal adminex-form" method="post" action="/manage/menu/save" autocomplete="off">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            @if(  !empty( $menu['id'] ) )
                                <input type="hidden" name="id" value="{{$menu['id']}}">
                            @endif

                            <div class="form-group ">
                                <label for="name" class="control-label col-lg-2">名称</label>
                                <div class="col-lg-4 col-xs-12">
                                    @if(  empty( $menu['id'] ) )
                                        <input class="form-control" id="name" name="name" type="text" value="" autocomplete="off" required minlength="2"/>
                                    @else
                                        <input class="form-control" id="name" name="name" type="text" value="{{$menu['name']}}" autocomplete="off" required minlength="2"/>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="description" class="control-label col-lg-2">上级菜单</label>
                                <div class="col-lg-4 col-xs-12">
                                    <select class="form-control ui-select" name="parent_id">
                                        <option value="0" >没有上级菜单</option>
                                        @if(  $parent-> count() > 0 )
                                            @foreach ( $parent as $value )
                                                @if( !empty( $menu['parent_id'] ) && $value-> id == $menu['parent_id'] )
                                                    <option value="{{$value-> id}}" selected="selected">{{$value-> name}}</option>
                                                @else
                                                    <option value="{{$value-> id}}" >{{$value-> name}}</option>
                                                @endif
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label for="url" class="control-label col-lg-2">地址</label>
                                <div class="col-lg-4 col-xs-12">
                                    <select class="form-control ui-select" name="permission_id">
                                        <option value="0" >不选择地址</option>
                                        @foreach ( $permission as $value )
                                            @if( !empty( $menu['permission_id'] ) && $value['id'] == $menu['permission_id'] )
                                                <option value="{{$value['id']}}" selected="selected">{{$value['display_name']}}[{{$value['name']}}]</option>
                                            @else
                                                <option value="{{$value['id']}}" >{{$value['display_name']}}[{{$value['name']}}]</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label for="sort" class="control-label col-lg-2">排序</label>
                                <div class="col-lg-4 col-xs-12">
                                    @if(  empty( $menu['id'] ) )
                                        <input class="form-control" id="sort" name="sort" type="number" value="0" autocomplete="off" required/>
                                    @else
                                        <input class="form-control" id="sort" name="sort" type="number" value="{{$menu['sort']}}" autocomplete="off" required/>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group ">
                                <label for="icon" class="control-label col-lg-2">图标</label>
                                <div class="col-lg-4 col-xs-12">
                                    @if(  empty( $menu['id'] ) )
                                        <input class="form-control" id="icon" name="icon" type="text" value="" autocomplete="off" />
                                    @else
                                        <input class="form-control" id="icon" name="icon" type="text" value="{{$menu['icon']}}" autocomplete="off" />
                                    @endif
                                </div>
                            </div>

                            <div class="form-group ">
                                <label for="prefix" class="control-label col-lg-2">前缀</label>
                                <div class="col-lg-4 col-xs-12">
                                    @if(  empty( $menu['id'] ) )
                                        <input class="form-control" id="prefix" name="prefix" type="text" value="" autocomplete="off" />
                                    @else
                                        <input class="form-control" id="prefix" name="prefix" type="text" value="{{$menu['prefix']}}" autocomplete="off" />
                                    @endif
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-lg-offset-2 col-lg-10">
                                    <button class="btn btn-primary" type="submit">保存</button>
                                    <button class="btn btn-default" onclick="location.href = '/manage/menus'" type="button">返回</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

        </div>
    </div>
@stop

@section('footer')
    <footer>
        2014 &copy; AdminEx by ThemeBucket
    </footer>
@stop