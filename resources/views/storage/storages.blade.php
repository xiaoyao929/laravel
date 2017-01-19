@extends('temp.index')

@section('style')
    <style>

    </style>
    <link href="{{ URL::asset('/css/bootstrap-treeview.css') }}" rel="stylesheet">
@stop

@section('scripts')
    <script>
        $(function () {
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
                    $("#name").val('');
                    $("#id").val('');
                    $('#myModal').modal('hide');
                }
                else
                {
                    $("#name").val(obj[0].text);
                    $("#id").val(obj[0].id);
                    $('#myModal').modal('hide');
                }
            })
        })
    </script>
    <script>
        $(".state").click(function () {
            var id    = $(this).data('id');
            var state = $(this).data('state');
            if( state == 'off' )
            {
                if( !confirm("确定停用此仓库吗？\n若选择停用，所属子仓库将一并停用"))
                {
                    return false;
                }
            }
            location.href = '/storage/state?id='+ id +'&state='+ state;
        })
        $(".edit").click(function () {
            var id = $(this).data('id');
            location.href = '/storage/edit?id='+ id;
        })
        $(".data_link").click(function () {
            var id = $(this).data('id');
            location.href = '/storage/show?id='+ id;
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
                            <label for="name" class="control-label col-lg-1">仓库名称</label>
                            <div class="col-lg-3">
                                <input type="hidden" id="id" name="id" value="{{array_get( $get, 'id' )}}">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="name" name="name" value="{{array_get( $get, 'name' )}}" readonly="readonly">
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="button" data-toggle="modal" data-target="#myModal">选择</button>
                                    </span>
                                </div>
                            </div>
                            <label for="name" class="control-label col-lg-1">仓库级别</label>
                            <div class="col-lg-3">
                                <select class="form-control" name="level">
                                    <option value="">全部</option>
                                    @foreach( $levels as $level )
                                        @if( !empty( $get['level'] ) && $level['level'] == $get['level'] )
                                            <option value="{{$level['level']}}" selected="selected">{{$level['level']}}</option>
                                        @else
                                            <option value="{{$level['level']}}">{{$level['level']}}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <label for="name" class="control-label col-lg-1">仓库状态</label>
                            <div class="col-lg-3">
                                <select class="form-control" name="status">
                                    <option value="all" @if( !empty( $get['status'] ) && $get['status']== 'all' ) selected="selected" @endif >全部</option>
                                    <option value="on"  @if( empty( $get['status'] ) || $get['status']== 'on' ) selected="selected" @endif>正常</option>
                                    <option value="off" @if( !empty( $get['status'] ) && $get['status']== 'off' ) selected="selected" @endif>停用</option>
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
                    仓库查询
                </header>
                <div class="panel-body">
                    <section id="flip-scroll">
                        <table class="table table-bordered table-striped table-condensed cf">
                            <thead class="cf">
                            <tr>
                                <th>序号</th>
                                <th>仓库名</th>
                                <th>仓库缩写</th>
                                <th>仓库级别</th>
                                <th>省市区</th>
                                <th>自定义编号</th>
                                <th>状态</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(  $storages-> count() > 0 )
                            @foreach ( $storages as $key=> $storage )
                            <tr>
                                <td>{{$key+1}}</td>
                                <td><button class="btn btn-link data_link" data-id="{{$storage-> id}}" type="button">{{$storage-> name}}</button></td>
                                <td>{{$storage-> acronym}}</td>
                                <td>{{$storage-> level}}</td>
                                <td>{{$storage-> province}}-{{$storage-> city}}-{{$storage-> town}}</td>
                                <td>{{$storage-> custom_id}}</td>
                                <td>
                                    @if(  $storage-> status == 1 )
                                        <span class="label label-success">正常</span>
                                    @else
                                        <span class="label label-warning">停用</span>
                                    @endif
                                </td>
                                <td>
                                    @if( $storage-> level != 1 )
                                    @if(  $storage-> status == 1 )
                                        <button class="btn btn-warning btn-xs state" data-id="{{$storage-> id}}" data-state="off" type="button"><span class="fa fa-pause"></span> 停用</button>
                                    @else
                                        <button class="btn btn-success btn-xs state" data-id="{{$storage-> id}}" data-state="on" type="button"><span class="fa fa-play"></span> 启用</button>
                                    @endif
                                    @endif
                                        <button class="btn btn-default btn-xs edit" data-id="{{$storage-> id}}" type="button"><span class="glyphicon glyphicon-pencil"></span> 编辑</button>
                                </td>
                            </tr>
                            @endforeach
                            @endif
                            </tbody>
                        </table>
                        <div class="text-right">
                            {{ $storages-> appends( $get )-> links() }}
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