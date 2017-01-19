@extends('temp.index')

@section('style')
    <style>

    </style>
    <link href="{{ URL::asset('/css/bootstrap-treeview.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('/js/bootstrap-datepicker/css/datepicker-custom.css') }}" rel="stylesheet">
@stop

@section('scripts')
    <script>
        //状态控制
        $(".state").click(function () {
            var id    = $(this).data('id');
            var status = $(this).data('state');
            location.href = '/client/customer/audit/status?id='+ id +'&status='+ status;
        })
        //编辑
        $('.edit').click(function () {
            var id    = $(this).data('id');
            location.href = '/client/customer/edit?id='+ id;

        });

    </script>
    <script src="{{ URL::asset('/js/bootstrap-treeview.js') }}"></script>
@stop

@section('body')
    <div class="row">
        <div class="col-md-12">
            <!--pagination start-->
            <section class="panel">
                <div class="panel-body">
                    <form class="cmxform form-horizontal adminex-form" method="get" action="/client/customer/list" >
                        <div class="form-group">
                            <label for="name" class="control-label col-lg-1">客户类型</label>
                            <div class="col-lg-3">
                                <select class="form-control" name="customer_type">
                                    <option value="" selected>全部</option>
                                    @foreach($customerType as $key => $value)
                                        @if($key == $search['customer_type'])
                                            <option value="{{$key}}" selected >{{$value}}</option>
                                        @else
                                            <option value="{{$key}}">{{$value}}</option>
                                        @endif
                                    @endforeach
                                </select>
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
                            <label for="name" class="control-label col-lg-1">状态</label>
                            <div class="col-lg-3">
                                <select class="form-control" name="status">
                                    <option value="" selected>全部</option>
                                    @foreach($status as $key => $value)
                                        @if($key == $search['status'])
                                            <option value="{{$key}}" selected >{{$value}}</option>
                                        @else
                                            <option value="{{$key}}">{{$value}}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <label for="name" class="control-label col-lg-1">审核状态</label>
                            <div class="col-lg-3">
                                <select class="form-control" name="audit_status">
                                    <option value="" selected>全部</option>
                                    @foreach($auditStatus as $key => $value)
                                        @if($key == $search['audit_status'])
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
                    客户信息查询
                </header>
                <div class="panel-body">
                    <section id="flip-scroll">
                        <table class="table table-bordered table-striped table-condensed cf table-hover">
                            <thead class="cf">
                            <tr>
                                <th>客户编号</th>
                                <th>客户类型</th>
                                <th>客户名称</th>
                                <th>联系电话</th>
                                <th>联系人手机</th>
                                <th>联系人邮箱</th>
                                <th>状态</th>
                                <th>审核状态</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody class="select_count">
                            @if(count($list) > 0)
                                @foreach($list as $key => $value)
                                    <tr>
                                        <td><a class="btn-link data_link" href="/client/customer/audit/show?from=1&id={{$value->id}}">{{$value->seq}}</a></td>
                                        <td>{{$customerType[$value->customer_type]}}</td>
                                        <td>{{$value->name}}</td>
                                        <td>{{$value->contact_tel}}</td>
                                        <td>{{$value->contact_mobile}}</td>
                                        <td>{{$value->contact_email}}</td>
                                        <td>{{$status[$value->status]}}</td>
                                        <td>{{$auditStatus[$value->audit_status]}}</td>
                                        <td>
                                            @if(($value->audit_status == '1' || $value->audit_status == '4') && $value->sale_status != '1')
                                                <button class="btn btn-success btn-xs edit" data-id="{{$value->id}}" data-state="{{$value->status}}"  type="button"><span class="fa fa-play"></span> 编辑</button>
                                                @if($value->audit_status == '1')
                                                    @if($value->status == '2')
                                                        <button class="btn btn-success btn-xs state" data-id="{{$value->id}}" data-state="{{$value->status}}"  type="button"><span class="fa fa-play"></span> 启用</button>
                                                    @else
                                                        <button class="btn btn-warning btn-xs state" data-id="{{$value->id}}" data-state="{{$value->status}}" type="button"><span class="fa fa-pause"></span> 停用</button>
                                                    @endif
                                                @endif
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
@stop

@section('footer')
    <footer>
        2014 &copy; AdminEx by ThemeBucket
    </footer>
@stop