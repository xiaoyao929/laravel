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
        })
    </script>
    <script>
        $(function () {
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
        })
        $("#save").click(function () {
            var obj = $('#tree').treeview('getSelected');
            if( obj == null || obj.length == 0 )
            {
                $("#storage_name").val('');
                $("#storage_id").val('');
            }
            else
            {
                $("#storage_name").val(obj[0].text);
                $("#storage_id").val(obj[0].id);
            }
            $('#modal').modal('hide');
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
                            <label for="class" class="control-label col-lg-1">券类别</label>
                            <div class="col-lg-3">
                                <select class="form-control ui-select" name="class">
                                    <option value="">全部</option>
                                    @foreach( $class as $v )
                                        @if( !empty($search['class']) && $search['class'] == $v['id'] )
                                            <option value="{{$v['id']}}" selected="selected">{{$v['name']}}</option>
                                        @else
                                            <option value="{{$v['id']}}" >{{$v['name']}}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <label for="name" class="control-label col-lg-1">券简称</label>
                            <div class="col-lg-3">
                                <select class="form-control ui-select" name="name">
                                    <option value="">全部</option>
                                    @foreach( $type as $v )
                                        @if( !empty($search['name']) && $search['name'] == $v['id'] )
                                            <option value="{{$v['id']}}" selected="selected">{{$v['name']}}</option>
                                        @else
                                            <option value="{{$v['id']}}" >{{$v['name']}}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <label for="storage_id" class="control-label col-lg-1">所属仓库</label>
                            <div class="col-lg-3">
                                <input type="hidden" id="storage_id" name="storage_id" value="{{array_get($search,'storage_id')}}">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="storage_name" name="storage_name" value="{{array_get($search,'storage_name')}}" readonly="readonly">
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="button" data-toggle="modal" data-target="#modal">选择</button>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-lg-offset-1 col-lg-10">
                                <button class="btn btn-primary" type="submit">开始搜索</button>
                            </div>
                        </div>

                    </form>
                </div>
            </section>
            <!--pagination end-->
        </div>
    </div>
    <form class="cmxform form-horizontal adminex-form" >
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="row">
            <div class="col-sm-12">
                <section class="panel">
                    <header class="panel-heading">
                        库存查询
                    </header>
                    <div class="panel-body">
                        <section id="flip-scroll">
                            <table class="table table-bordered table-striped table-condensed cf table-hover">
                                <thead class="cf">
                                <tr>
                                    <th>网点</th>
                                    <th>券类别</th>
                                    <th>券种简称</th>
                                    <th>可用库存</th>
                                    <th>待审核</th>
                                    <th>调拨在途</th>
                                    <th>已作废</th>
                                    <th>已销售</th>
                                    <th>已使用</th>
                                </tr>
                                </thead>
                                <tbody class="select_count">
                                @if( $list-> count() > 0 )
                                    @foreach( $list as $key => $value )
                                        <tr>
                                            <td>{{$value-> storage_name}}</td>
                                            <td>{{$value-> coupon_class_name}}</td>
                                            <td>{{$value-> coupon_type_name}}</td>
                                            <td><span class="badge badge-primary">{{$value-> amount_no_sale}}</span></td>
                                            <td><span class="badge badge-inverse">{{$value-> amount_audit}}</span></td>
                                            <td><span class="badge">{{$value-> amount_transfers}}</span></td>
                                            <td><span class="badge badge-important">{{$value-> amount_destroyed}}</span></td>
                                            <td><span class="badge">{{$value-> amount_saled}}</span></td>
                                            <td><span class="badge">{{$value-> amount_used}}</span></td>
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
                                {{ $list-> appends( $search )-> links() }}
                            </div>
                        </section>
                    </div>
                </section>

            </div>
        </div>
    </form>
    <!-- Modal -->
    <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
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