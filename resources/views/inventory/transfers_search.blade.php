@extends('temp.index')

@section('style')
    <style>

    </style>
    <link href="{{ URL::asset('/css/bootstrap-treeview.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('/js/bootstrap-datepicker/css/datepicker-custom.css') }}" rel="stylesheet">
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
            function getTransfers() {
                var data = '{!! $storagesTransfers !!}';
                return data;
            }

            $('#toTree').treeview({
                data: getTransfers(),
                levels: 2,
                selectedIcon:"glyphicon glyphicon-ok",
                collapseIcon:"glyphicon glyphicon-minus-sign",
                expandIcon:"glyphicon glyphicon-plus-sign",
                showTags:true
            });
            $('#fromTree').treeview({
                data: getTree(),
                levels: 2,
                selectedIcon:"glyphicon glyphicon-ok",
                collapseIcon:"glyphicon glyphicon-minus-sign",
                expandIcon:"glyphicon glyphicon-plus-sign",
                showTags:true
            });
        })
        $("#toSave").click(function () {
            var obj = $('#toTree').treeview('getSelected');
            if( obj == null || obj.length == 0 )
            {
                $("#to_name").val('');
                $("#to_id").val('');
            }
            else
            {
                $("#to_name").val(obj[0].text);
                $("#to_id").val(obj[0].id);
            }
            $('#toModal').modal('hide');
        })
        $("#fromSave").click(function () {
            var obj = $('#fromTree').treeview('getSelected');
            if( obj == null || obj.length == 0 )
            {
                $("#from_name").val('');
                $("#from_id").val('');
            }
            else
            {
                $("#from_name").val(obj[0].text);
                $("#from_id").val(obj[0].id);
            }
            $('#fromModal').modal('hide');
        })
    </script>
    <script>
        $(".show").click(function () {
            var id = $(this).data('id');
            location.href = '/inventory/transfers/search/show?id='+ id;
        })
    </script>
    <script src="{{ URL::asset('/js/bootstrap-treeview.js') }}"></script>
    <!--   时间控件 下面都是   -->
    <script src="{{ URL::asset('/js/bootstrap-datepicker/js/bootstrap-datepicker.js')}}"></script>
    <script src="{{ URL::asset('/js/bootstrap-datetimepicker/js/bootstrap-datetimepicker.js')}}"></script>
    <script src="{{ URL::asset('/js/bootstrap-timepicker/js/bootstrap-timepicker.js')}}"></script>
    <script src="{{ URL::asset('/js/bootstrap-colorpicker/js/bootstrap-colorpicker.js')}}"></script>
    <script src="{{ URL::asset('/js/pickers-init.js')}}"></script>
@stop

@section('body')
    <div class="row">
        <div class="col-md-12">
            <!--pagination start-->
            <section class="panel">
                <div class="panel-body">
                    <form class="cmxform form-horizontal adminex-form" method="get" action="" >

                        <div class="form-group">
                            <label for="from" class="control-label col-lg-1">申请日期</label>
                            <div class="col-md-3">
                                <div class="input-group input-large custom-date-range">
                                    <input type="text" class="form-control dpd1" name="from" value="{{array_get($search,'from')}}">
                                    <span class="input-group-addon">To</span>
                                    <input type="text" class="form-control dpd2" name="to" value="{{array_get($search,'to')}}">
                                </div>
                            </div>
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
                        </div>

                        <div class="form-group">
                            <label for="parent_id" class="control-label col-lg-1">调拨仓库</label>
                            <div class="col-lg-3">
                                <input type="hidden" id="from_id" name="from_id" value="{{array_get($search,'from_id')}}">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="from_name" name="from_name" value="{{array_get($search,'from_name')}}" readonly="readonly">
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="button" data-toggle="modal" data-target="#fromModal">选择</button>
                                    </span>
                                </div>
                            </div>
                            <label for="parent_id" class="control-label col-lg-1">接收仓库</label>
                            <div class="col-lg-3">
                                <input type="hidden" id="to_id" name="to_id" value="{{array_get($search,'to_id')}}">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="to_name" name="to_name" value="{{array_get($search,'to_name')}}" readonly="readonly">
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="button" data-toggle="modal" data-target="#toModal">选择</button>
                                    </span>
                                </div>
                            </div>
                            <label for="from" class="control-label col-lg-1">状态</label>
                            <div class="col-md-3">
                                <select class="form-control" name="status">
                                    <option value="0">全部</option>
                                    @foreach( $status as $k=> $v )
                                        @if( !empty($search['status']) && $search['status'] == $k )
                                            <option value="{{$k}}" selected="selected">{{$v}}</option>
                                        @else
                                            <option value="{{$k}}" >{{$v}}</option>
                                        @endif
                                    @endforeach
                                </select>
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
                        调拨记录查询
                    </header>
                    <div class="panel-body">
                        <section id="flip-scroll">
                            <table class="table table-bordered table-striped table-condensed cf table-hover">
                                <thead class="cf">
                                <tr>
                                    <th>调拨单号</th>
                                    <th>券类别</th>
                                    <th>券种简称</th>
                                    <th>调拨仓库</th>
                                    <th>起始券号</th>
                                    <th>结束券号</th>
                                    <th>入库数量</th>
                                    <th>接收仓库</th>
                                    <th>申请日期</th>
                                    <th>状态</th>
                                </tr>
                                </thead>
                                <tbody class="select_count">
                                @if( $list-> count() > 0 )
                                    @foreach( $list as $key => $value )
                                        <tr>
                                            <td><button class="btn btn-link data_link show" data-id="{{$value-> id}}" type="button">{{$value-> seq}}</button></td>
                                            <td>{{$value-> coupon_class_name}}</td>
                                            <td>{{$value-> coupon_type_name}}</td>
                                            <td>{{$value-> from_storage_name}}</td>
                                            <td>{{$value-> start_flow_no}}</td>
                                            <td>{{$value-> end_flow_no}}</td>
                                            <td>{{$value-> amount}}</td>
                                            <td>{{$value-> to_storage_name}}</td>
                                            <td>{!! date("Y-m-d",strtotime($value-> request_time)) !!}</td>
                                            <td>
                                                @if( $value-> status == 2 || $value-> status == 5 )
                                                    <span class="label label-warning" data-val="1">{{$status[$value->status]}}</span>
                                                @elseif( $value-> status == 3 || $value-> status == 4 )
                                                    <span class="label label-success" data-val="1">{{$status[$value->status]}}</span>
                                                @else
                                                    <span class="label label-info" data-val="1">{{$status[$value->status]}}</span>
                                                @endif
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
                                {{ $list-> appends( $search )-> links() }}
                            </div>
                        </section>
                    </div>
                </section>

            </div>
        </div>
    </form>
    <!-- Modal -->
    <div class="modal fade" id="fromModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title" id="myModalLabel">仓库选择</h4>
                </div>
                <div class="modal-body">
                    <div id="fromTree"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="fromSave">确认</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="toModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title" id="myModalLabel">仓库选择</h4>
                </div>
                <div class="modal-body">
                    <div id="toTree"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="toSave">确认</button>
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