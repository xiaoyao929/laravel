@extends('temp.index')

@section('style')
    <style>
        .state{
            display: block;
            width: 100%;
            height: 34px;
            padding: 6px 0px;
            font-size: 14px;
            line-height: 1.42857143;
            color: #555;
            background-color: #fff;
            background-image: none;
        }
    </style>
@stop

@section('scripts')

@stop

@section('body')
    <div class="row">
        <div class="col-sm-12">
            <section class="panel">
                <header class="panel-heading">
                    @if($from == '1')
                        客户信息查询
                    @else
                        客户信息审核
                    @endif
                </header>
                <div class="panel-body">
                    <div class="form">
                        <div class="cmxform form-horizontal adminex-form" >
                            <div class="form-group ">
                                <label class="control-label col-lg-2">客户编号</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$detail->seq}}</div>
                                </div>
                                <label  class="control-label col-lg-1">客户类型</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$customerType[$detail->customer_type]}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label  class="control-label col-lg-2">客户名称</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$detail->name}}</div>
                                </div>
                                <label  class="control-label col-lg-1">联系电话</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$detail->contact_tel}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">联系人手机</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$detail->contact_mobile}}</div>
                                </div>
                                <label for="name" class="control-label col-lg-1">联系地址</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$detail->contact_addr}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">证件类型</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$certificateType[$detail->certificate_type]}}</div>
                                </div>
                                <label class="control-label col-lg-1">证件号码</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$detail->certificate_code}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">联系人邮箱</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$detail->contact_email}}</div>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label class="control-label col-lg-2">申请人</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$detail->request_user_name}}</div>
                                </div>
                                <label class="control-label col-lg-1">申请日期</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$detail->request_time}}</div>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label class="control-label col-lg-2">申请仓库</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$detail->request_storage_name}}</div>
                                </div>
                            </div>
                            @if($from == '1')
                                <div class="form-group ">
                                    <label class="control-label col-lg-2">审核人</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{$detail->approve_user_name}}</div>
                                    </div>
                                    <label class="control-label col-lg-1">审核日期</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{$detail->approve_time}}</div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label col-lg-2">审核状态</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{$auditStatus[$detail->audit_status]}}</div>
                                    </div>
                                    <label class="control-label col-lg-1">状态</label>
                                    <div class="col-lg-3">
                                        <div class="state" >
                                            @if( $detail->status == 1 )
                                                <span class="label label-success">正常</span>
                                            @else
                                                <span class="label label-warning">停用</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-lg-offset-2 col-lg-10">
                                        <button class="btn btn-default" onclick="location.href = '/client/customer/list{{$urlParam}}'" type="button">确定</button>
                                    </div>
                                </div>
                            @else
                                <div class="form-group ">
                                    <label class="control-label col-lg-2">状态</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{$auditStatus[$detail->audit_status]}}</div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <form action="/client/customer/audit/save" method="post">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="hidden" name="select_list[]" value="{{$detail->id}}">
                                        <div class="form-group">
                                            <div class="col-lg-offset-2 col-lg-10">
                                                <button class="btn btn-success" type="submit" name="pass" value="1" >通过</button>
                                                <button class="btn btn-warning" type="submit" name="pass" value="0" >不通过</button>
                                                <button class="btn btn-default" onclick="location.href = '/client/customer/audit/list{{$urlParam}}'" type="button">返回</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            @endif
                        </div>
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