@extends('temp.index')

@section('style')
    <style>

    </style>
    <link href="{{ URL::asset('/css/bootstrap-treeview.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('/js/bootstrap-datepicker/css/datepicker-custom.css') }}" rel="stylesheet">
@stop

@section('scripts')
    <script>
        //全选
        $('#select_all').click(function (d) {
            var checked_all = $(this).prop('checked');
            if(checked_all){
                $("input[type='checkbox']").attr('checked',true);
            }else{
                $("input[type='checkbox']").attr('checked',false);
            }
        })
        $(function () {
            //选择仓库
            function getTree() {
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
                    <form class="cmxform form-horizontal adminex-form" method="get" action="/make/list" >
                        <div class="form-group">
                            <label for="name" class="control-label col-lg-1">券类别</label>
                            <div class="col-lg-3">
                                <select class="form-control" name="class_id">
                                    <option value="">全部</option>
                                    @foreach($class as $key => $value)
                                        @if(isset($search['class_id']) && $search['class_id'] == $value['id'])
                                            <option value="{{$value['id']}}" selected >{{$value['name']}}</option>
                                        @else
                                            <option value="{{$value['id']}}" >{{$value['name']}}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <label for="name" class="control-label col-lg-1">券简称</label>
                            <div class="col-lg-3">
                                <select class="form-control" name="type_id">
                                    <option value="">全部</option>
                                    @foreach($couponType as $key => $value)
                                        @if(isset($search['type_id']) && $search['type_id'] == $value['id'])
                                            <option value="{{$value['id']}}" selected >{{$value['name']}}</option>
                                        @else
                                            <option value="{{$value['id']}}" >{{$value['name']}}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <label for="name" class="control-label col-lg-1">制券单号</label>
                            <div class="col-lg-3">
                                <input class="form-control" type="text" value="{{$search['seq']}}" name="seq" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="name" class="control-label col-lg-1">申请日期</label>
                            <div class="col-md-3">
                                <div class="input-group input-large custom-date-range">
                                    <input type="text" class="form-control dpd1" name="from" value="{{$search['from']}}">
                                    <span class="input-group-addon">To</span>
                                    <input type="text" class="form-control dpd2" name="to" value="{{$search['to']}}">
                                </div>
                            </div>
                            <label for="name" class="control-label col-lg-1">申请仓库</label>
                            <div class="col-lg-3">
                                <input type="hidden" id="storage_id" name="request_storage_id" value="{{$search['request_storage_id']}}">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="storage_name" name="storage_name" value="{{$search['storage_name']}}" readonly="readonly">
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="button" data-toggle="modal" data-target="#myModal">选择</button>
                                    </span>
                                </div>
                            </div>
                            <label for="name" class="control-label col-lg-1">状态</label>
                            <div class="col-lg-3">
                                <select class="form-control" name="status">
                                    <option value="">全部</option>
                                    @foreach($status as $key => $value)
                                        @if($key == $search['status'])
                                            <option value="{{$key}}" selected >{{$value}}</option>
                                        @else
                                            <option value="{{$key}}">{{$value}}</option>
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
        <div class="row">
            <div class="col-sm-12">
                <section class="panel">
                    <header class="panel-heading">
                        制券记录
                    </header>
                    <div class="panel-body">
                        <section id="flip-scroll">
                            <table class="table table-bordered table-striped table-condensed cf table-hover">
                                <thead class="cf">
                                <tr>
                                    <th>制券单号</th>
                                    <th>券类别</th>
                                    <th>券简称</th>
                                    <th>券开始编号</th>
                                    <th>券截止编号</th>
                                    <th>数量</th>
                                    <th>申请时间</th>
                                    <th>申请仓库</th>
                                    <th>票面开始日期</th>
                                    <th>票面截止日期</th>
                                    <th>状态</th>
                                </tr>
                                </thead>
                                <tbody class="select_count">
                                @if(count($list) > 0)
                                    @foreach($list as $key => $value)
                                        <tr>
                                            <td><a class="btn-link data_link" href="/make/audit/show?from_list=1&id={{$value->id}}">{{$value->seq}}</a></td>
                                            <td>{{$value->coupon_class_name}}</td>
                                            <td>{{$value->coupon_type_name}}</td>
                                            <td>{{$value->start_flow_no}}</td>
                                            <td>{{$value->end_flow_no}}</td>
                                            <td>{{$value->amount}}</td>
                                            <td>{!! date('Y年m月d日',strtotime($value->request_time)) !!}</td>
                                            <td>{{$value['request_storage_name']}}</td>
                                            <td>{!! date('Y年m月d日',strtotime($value->begin_time)) !!}</td>
                                            <td>{!! date('Y年m月d日',strtotime($value->end_time)) !!}</td>
                                            <td>{{$status[$value->status]}}</td>
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
                            @if(count($list) > 0 && method_exists($list,'render'))
                                <div class="text-right">
                                    {{ $list-> appends( $search )-> links() }}
                                </div>
                            @endif
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