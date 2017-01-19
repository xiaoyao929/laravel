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
                var data = '{!! $select !!}';
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
                    $("#storage_name").val('');
                    $("#storage_id").val('');
                    $('#myModal').modal('hide');
                }
                else
                {
                    $("#storage_name").val(obj[0].text);
                    $("#storage_id").val(obj[0].id);
                    $('#myModal').modal('hide');
                }
            })
        })
    </script>
    <script>
        $(".state").click(function () {
            var id     = $(this).data('id');
            var status = $(this).data('status');
            location.href = '/user/status?id='+ id +'&status='+ status;
        })
        $(".del").click(function () {
            if( confirm("确认删除吗?"))
            {
                var id = $(this).data('id');
                location.href = '/user/del?id='+ id;
            }
            else
            {
                return false;
            }
        })
        $(".edit").click(function () {
            var id = $(this).data('id');
            location.href = '/user/edit?id='+ id;
        })
        $(".data_link").click(function () {
            var id = $(this).data('id');
            location.href = '/user/show?id='+ id;
        })
    </script>
    <script src="{{ URL::asset('/js/bootstrap-treeview.js') }}"></script>
@stop

@section('body')
    <div class="row">
        <div class="col-md-12">
            <!--pagination start-->
            <section class="panel">
                <div class="panel-body">
                    <form class="cmxform form-horizontal adminex-form" method="get" action="" >
                        <div class="form-group">
                            <label for="account" class="control-label col-lg-1">用户名</label>
                            <div class="col-lg-3">
                                <input type="text" class="form-control" id="account" name="account" value="{{array_get( $get, 'account' )}}">
                            </div>
                            <label for="nickname" class="control-label col-lg-1">姓名</label>
                            <div class="col-lg-3">
                                <input type="text" class="form-control" id="nickname" name="nickname" value="{{array_get( $get, 'nickname' )}}">
                            </div>
                            <label for="name" class="control-label col-lg-1">状态</label>
                            <div class="col-lg-3">
                                <select class="form-control ui-select" name="status">
                                    <option value="all" @if( !empty( $get['status'] ) && $get['status']== 'all' ) selected="selected" @endif >全部</option>
                                    <option value="on"  @if( empty( $get['status'] ) || $get['status']== 'on' ) selected="selected" @endif>正常</option>
                                    <option value="off" @if( !empty( $get['status'] ) && $get['status']== 'off' ) selected="selected" @endif>停用</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="storage_name" class="control-label col-lg-1">所属仓库</label>
                            <div class="col-lg-3">
                                <input type="hidden" id="storage_id" name="storage_id" value="{{array_get( $get, 'storage_id' )}}">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="storage_name" name="storage_name" value="{{array_get( $get, 'storage_name' )}}" readonly="readonly">
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="button" data-toggle="modal" data-target="#myModal">选择</button>
                                    </span>
                                </div>
                            </div>
                            <label for="role_id" class="control-label col-lg-1">角色</label>
                            <div class="col-lg-3">
                                <select class="form-control " name="role_id">
                                    <option value="">全部</option>
                                    @foreach( $roles as $role )
                                        @if( !empty( $get['role_id'] ) && $role['id'] == $get['role_id'] )
                                            <option value="{{$role['id']}}" selected="selected">{{$role['name']}}</option>
                                        @else
                                            <option value="{{$role['id']}}">{{$role['name']}}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-lg-offset-1 col-lg-10">
                                <button class="btn btn-primary" type="submit">搜索</button>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
            <!--pagination end-->
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <section class="panel">
                <header class="panel-heading">
                    用户列表
                </header>
                <div class="panel-body">
                    <section id="flip-scroll">
                        <table class="table table-bordered table-striped table-condensed cf">
                            <thead class="cf">
                            <tr>
                                <th>用户名</th>
                                <th>姓名</th>
                                <th>所属仓库</th>
                                <th>仓库等级</th>
                                <th>角色</th>
                                <th>状态</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(  $users-> count() > 0 )
                            @foreach ( $users as $user )
                            <tr>
                                <td><button class="btn btn-link data_link" data-id="{{$user-> id}}" type="button">{{$user-> account}}</button></td>
                                <td>{{$user-> nickname}}</td>
                                <td>{{$user-> storage_name}}</td>
                                <td>{{$user-> storage_level}}</td>
                                <td>{{$user-> role_name}}</td>
                                <td>
                                    @if(  $user-> status == 1 )
                                        <span class="label label-warning">停用</span>
                                    @else
                                        <span class="label label-success">正常</span>
                                    @endif
                                </td>
                                <td>
                                    @if( $user-> is_admin != 1 )
                                    @if( $user-> status == 1 )
                                        <button class="btn btn-success btn-xs state" data-id="{{$user-> id}}" data-status="on" type="button"><span class="fa fa-play"></span> 启用</button>
                                    @else
                                        <button class="btn btn-warning btn-xs state" data-id="{{$user-> id}}" data-status="off" type="button"><span class="fa fa-pause"></span> 停用</button>
                                    @endif
                                    @endif
                                        <button class="btn btn-default btn-xs edit" data-id="{{$user-> id}}" type="button"><span class="glyphicon glyphicon-pencil"></span> 编辑</button>
                                </td>
                            </tr>
                            @endforeach
                            @else
                                <tr>
                                    <td colspan="11" align="center" height="150">
                                        <h1>
                                            暂无数据
                                        </h1>
                                    </td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                        <div class="text-right">
                            {!! $users-> appends( $get )-> links() !!}
                        </div>
                    </section>
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