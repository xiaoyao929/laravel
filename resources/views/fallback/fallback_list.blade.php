@extends('temp.index')

@section('style')
    <style>
        .level2-box{ background: #424F63;}
        .details .table{ background: #424F63; border: none;}
    </style>
    <link href="{{ URL::asset('/css/bootstrap-treeview.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('/js/bootstrap-datepicker/css/datepicker-custom.css') }}" rel="stylesheet">
@stop

@section('scripts')
    <script>
        $(function () {
            $(document).ready(function() {
                $(document).on('click','#hidden-table-info tbody td img',function () {
                    var nTr = $(this).parents('tr')[0];
                    if ($(nTr).hasClass("closed"))
                    {
                        this.src = "{{ URL::asset('/images/details_close.png') }}";
                        $(nTr).removeClass("closed").addClass("open");
                        $(nTr).next("tr").removeClass("hidden");
                    }
                    else
                    {
                        this.src = "{{ URL::asset('/images/details_open.png') }}";
                        $(nTr).removeClass("open").addClass("closed");
                        $(nTr).next("tr").addClass("hidden");
                    }
                });
            });
            //选择仓库
            function getTree() {
                var data = '{!! $storageList !!}';
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
    <script src="{{ URL::asset('/js/bootstrap-treeview.js')}}"></script>
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
                    <form class="cmxform form-horizontal adminex-form" method="get" action="/exchange/fallback/list" >
                        <div class="form-group">
                            <label for="name" class="control-label col-lg-1">券类别</label>
                            <div class="col-lg-3">
                                <select class="form-control" name="coupon_class">
                                    <option value="">全部</option>
                                    @foreach($couponClass as $key => $value)
                                        @if(!empty($search['coupon_class']) && $value['id'] == $search['coupon_class'])
                                            <option value="{{$value['id']}}" selected>{{$value['name']}}</option>
                                        @else
                                            <option value="{{$value['id']}}">{{$value['name']}}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <label for="name" class="control-label col-lg-1">券简称</label>
                            <div class="col-lg-3">
                                <select class="form-control" name="coupon_type">
                                    <option value="">全部</option>
                                    @foreach($couponType as $key => $value)
                                        @if(!empty($search['coupon_type']) && $value['id'] == $search['coupon_type'])
                                            <option value="{{$value['id']}}" selected>{{$value['name']}}</option>
                                        @else
                                            <option value="{{$value['id']}}">{{$value['name']}}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <label for="name" class="control-label col-lg-1">申请仓库</label>
                            <div class="col-lg-3">
                                <input type="hidden" id="storage_id" name="request_storage_id" value="{{$search['request_storage_id']}}">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="storage_name" name="request_storage_name" value="{{$search['request_storage_name']}}" readonly="readonly">
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="button" data-toggle="modal" data-target="#myModal">选择</button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="name" class="control-label col-lg-1">客户类型</label>
                            <div class="col-lg-3">
                                <select class="form-control" name="customer_type">
                                    <option value="" selected>全部</option>
                                    @foreach($customerType as $key => $value)
                                        @if(!empty($search['customer_type']) && $key == $search['customer_type'])
                                            <option value="{{$key}}" selected>{{$value['name']}}</option>
                                        @else
                                            <option value="{{$key}}">{{$value['name']}}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <label for="name" class="control-label col-lg-1">部门/客户名称</label>
                            <div class="col-lg-3">
                                <input class="form-control" type="text" value="{{$search['customer_name']}}" name="customer_name" />
                            </div>
                            <label for="name" class="control-label col-lg-1">退券单号</label>
                            <div class="col-lg-3">
                                <input class="form-control" type="text" value="{{$search['fallback_seq']}}" name="fallback_seq" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="name" class="control-label col-lg-1">销售单号</label>
                            <div class="col-lg-3">
                                <input class="form-control" type="text" value="{{$search['sale_seq']}}" name="sale_seq" />
                            </div>
                            <label for="name" class="control-label col-lg-1">申请日期</label>
                            <div class="col-md-3">
                                <div class="input-group input-large custom-date-range">
                                    <input type="text" class="form-control dpd1" name="from" value="{{$search['from']}}">
                                    <span class="input-group-addon">To</span>
                                    <input type="text" class="form-control dpd2" name="to" value="{{$search['to']}}">
                                </div>
                            </div>
                            <label for="name" class="control-label col-lg-1">状态</label>
                            <div class="col-lg-3">
                                <select class="form-control" name="status">
                                    <option value="" selected>全部</option>
                                    @foreach($status as $key => $value)
                                        @if(!empty($search['status']) && $key == $search['status'])
                                            <option value="{{$key}}" selected>{{$value}}</option>
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
    <form class="cmxform form-horizontal adminex-form" method="post" action="/exchange/fallback/audit/save" >
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="row">
            <div class="col-sm-12">
                <section class="panel">
                    <header class="panel-heading">
                        退券列表
                    </header>
                    <div class="panel-body">
                        <section id="flip-scroll">
                            <table class="display table table-bordered dataTable" id="hidden-table-info">
                                <thead class="cf">
                                <tr>
                                    <th>退券单号</th>
                                    <th>申请仓库</th>
                                    <th>销售单号</th>
                                    <th>券种简称</th>
                                    <th>客户类型</th>
                                    <th>客户/部门名称</th>
                                    <th>开始券号</th>
                                    <th>结束券号</th>
                                    <th>销售金额</th>
                                    <th>退券数量</th>
                                    <th>状态</th>
                                </tr>
                                </thead>
                                <tbody class="select_count">
                                @if(count($list) > 0)
                                    @foreach($list as $key => $value)
                                        <tr class="level1-box closed">
                                            <td><a class="btn-link data_link" href="/exchange/fallback/audit/show?from=1&id={{$value->id}}&sale_seq={{$value->sale_seq}}">{{$value->seq}}</a></td>
                                            <td>{{$value->request_storage_name}}</td>
                                            <td>{{$value->sale_seq}}</td>
                                            <td>{{$value->coupon_type_name}}</td>
                                            <td>{{$customerType[$value->customer_type]['name']}}</td>
                                            <td>{{$value->customer_name}}</td>
                                            <td>{{$value->fallback_start_flow_no}}</td>
                                            <td>{{$value->fallback_end_flow_no}}</td>
                                            <td>{{priceShow($value->sale_price)}}</td>
                                            <td>{{$value->fallback_amount}}</td>
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