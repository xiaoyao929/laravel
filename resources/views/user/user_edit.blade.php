@extends('temp.index')

@section('style')
    <style>

    </style>
    <link href="{{ URL::asset('/css/bootstrap-treeview.css') }}" rel="stylesheet">
@stop

@section('scripts')
    <script>
        $(function () {
            $(".ui-select").chosen();
            function getTree() {
                // Some logic to retrieve, or generate tree structure
                var data = '{!! $storages !!}';
                return data;
            }

            $('#tree').treeview({
                data: getTree(),
                levels: 2,
                selectedIcon:"glyphicon glyphicon-ok",
                collapseIcon:"glyphicon glyphicon-minus-sign",
                expandIcon:"glyphicon glyphicon-plus-sign",
                showTags:true
            });
            $("#save").click(function () {
                var obj = $('#tree').treeview('getSelected');
                if( obj == null || obj.length == 0 )
                {
                    alert( '请选择仓库！' );
                    return false;
                }
                $("#storage_name").val(obj[0].text);
                $("#storage_id").val(obj[0].id);
                $('#myModal').modal('hide');
            })
        })
    </script>
    <script src="{{ URL::asset('/js/jquery.validate.min.js') }}"></script>
    <script src="{{ URL::asset('/js/bootstrap-treeview.js') }}"></script>
@stop

@section('body')
    <div class="row">
        <div class="col-sm-12">
            <section class="panel">
                <header class="panel-heading">
                    用户编辑
                </header>
                <div class="panel-body">
                    <div class="form">
                        <form class="cmxform form-horizontal adminex-form" method="post" action="/user/save" autocomplete="off">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="id" value="{{array_get($user,'id')}}">

                            @if( empty( $user['is_admin'] ) || $user['is_admin'] != 1  )
                                <div class="form-group ">
                                    <label for="role_id" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 所在分组</label>
                                    <div class="col-lg-4 col-xs-12">
                                        <select class="form-control ui-select" name="role_id" >
                                            @if(  count( $roles ) > 0 )
                                                @foreach ( $roles as $role )
                                                    @if( !empty( $user['role_id'] ) && $role['id'] == $user['role_id'] )
                                                        <option value="{{$role['id']}}" selected="selected">{{$role['name']}}</option>
                                                    @else
                                                        <option value="{{$role['id']}}">{{$role['name']}}</option>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label for="storage_id" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 所属仓库</label>
                                    <div class="col-lg-2 col-xs-8">
                                        <input type="hidden" id="storage_id" name="storage_id" value="{{array_get($user,'storage_id')}}">
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="storage_name" name="storage_name" value="{{array_get($user,'storage_name')}}" readonly="readonly">
                                            <span class="input-group-btn">
                                            <button class="btn btn-default" type="button" data-toggle="modal" data-target="#myModal">选择</button>
                                        </span>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="form-group ">
                                <label for="account" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 用户名</label>
                                <div class="col-lg-4 col-xs-12">

                                    @if(  empty( $user['id'] ) )
                                        <input class="form-control" id="account" name="account" type="email" value="{{array_get($user,'account')}}" placeholder="请使用邮箱" autocomplete="off" required minlength="2"/>
                                    @else
                                        <input class="form-control" id="account" name="account" type="email" readonly="readonly" value="{{array_get($user,'account')}}" autocomplete="off" required minlength="2"/>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="nickname" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 姓名</label>
                                <div class="col-lg-4 col-xs-12">
                                    <input class="form-control" id="nickname" name="nickname" type="text" value="{{array_get($user,'nickname')}}" autocomplete="off" required minlength="2"/>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="tel" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 电话</label>
                                <div class="col-lg-4 col-xs-12">
                                    <input class="form-control" id="tel" name="tel" type="number" value="{{array_get($user,'tel')}}" autocomplete="off" required minlength="2" maxlength="11"/>
                                </div>
                            </div>
                            <div class="form-group ">
                            @if(  empty( $user['id'] ) )
                                <label for="password" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 密码</label>
                            @else
                                <label for="password" class="control-label col-lg-2">密码</label>
                            @endif
                                <div class="col-lg-4 col-xs-12">
                                    @if(  empty( $user['id'] ) )
                                        <input class="form-control" id="password" name="password" type="text" value="{{array_get($user,'password')}}" autocomplete="off" required minlength="8"/>
                                    @else
                                        <input class="form-control" id="password" name="password" type="text" placeholder="为空不修改密码" autocomplete="off"  minlength="8"/>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-lg-offset-2 col-lg-10">
                                    <button class="btn btn-primary" type="submit">保存</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title" id="myModalLabel">仓库选择</h4>
                </div>
                <div class="modal-body">
                    <div id="tree"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="save">确认</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('footer')
    <footer>
        2014 &copy; AdminEx by ThemeBucket
    </footer>
@stop