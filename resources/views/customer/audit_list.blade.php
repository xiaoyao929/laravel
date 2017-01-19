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
        //触发取消全选和全部选中
        $('.select_one').click(function (d) {
            var check_num = $("input[type='checkbox']:checked").length;
            //当前选中的数量是为一页，就认为全选，反过来一样
            if(check_num == $('.select_count tr').length && $(this).prop('checked') == true){
                $("input[type='checkbox']").attr('checked',true);
            }else{
                $('#select_all').attr('checked',false);
            }
        });

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
                    <form class="cmxform form-horizontal adminex-form" method="get" action="/sale/customer/audit/list" >
                        <div class="form-group">
                            <label for="name" class="control-label col-lg-1">客户类型</label>
                            <div class="col-lg-3">
                                <input class="form-control" type="text" value="{{$search['customer_type']}}" name="customer_type" />
                            </div>
                            <label for="name" class="control-label col-lg-1">客户/公司名称</label>
                            <div class="col-lg-3">
                                <input class="form-control" type="text" value="{{$search['name']}}" name="name" />
                            </div>
                            <label for="name" class="control-label col-lg-1">联系电话</label>
                            <div class="col-lg-3">
                                <input class="form-control" type="text" value="{{$search['contact_tel']}}" name="contact_tel" />
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
    <form class="cmxform form-horizontal adminex-form" method="post" action="/client/customer/audit/save" >
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="row">
            <div class="col-sm-12">
                <section class="panel">
                    <header class="panel-heading">
                        客户审核
                    </header>
                    <div class="panel-body">
                        <section id="flip-scroll">
                            <table class="table table-bordered table-striped table-condensed cf table-hover">
                                <thead class="cf">
                                <tr>
                                    <th><input type="checkbox" id="select_all" ></th>
                                    <th>客户编号</th>
                                    <th>客户类型</th>
                                    <th>客户/公司名称</th>
                                    <th>联络人</th>
                                    <th>联系电话</th>
                                    <th>联系人手机</th>
                                    <th>联系人邮箱</th>
                                    <th>状态</th>
                                </tr>
                                </thead>
                                <tbody class="select_count">
                                @if(count($list) > 0)
                                    @foreach($list as $key => $value)
                                        <tr>
                                            <td><input type="checkbox" class="select_one" value="{{$value->id}}" name="select_list[]"></td>
                                            <td><a class="btn-link data_link" href="/client/customer/audit/show?id={{$value->id}}">{{$value->seq}}</a></td>
                                            <td>{{$customerType[$value->customer_type]}}</td>
                                            <td>{{$value->name}}</td>
                                            <td>{{$value->contact_name}}</td>
                                            <td>{{$value->contact_tel}}</td>
                                            <td>{{$value->contact_mobile}}</td>
                                            <td>{{$value->contact_email}}</td>
                                            <td>{{$auditStatus[$value->audit_status]}}</td>
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
                    <div class="panel-body">
                        <button class="btn btn-success" type="submit" name="pass" value="1">通过</button>
                        <button class="btn btn-warning" type="submit" name="pass" value="0">不通过</button>
                    </div>
                </section>
            </div>
        </div>
    </form>
@stop

@section('footer')
    <footer>
        2014 &copy; AdminEx by ThemeBucket
    </footer>
@stop