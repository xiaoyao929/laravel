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
                    @if($detail->from_list == '1')
                        制券信息
                    @else
                        制券审核
                    @endif
                </header>
                <div class="panel-body">
                    <div class="form cmxform form-horizontal adminex-form">
                            <div class="form-group ">
                                <label class="control-label col-lg-2">制券单号</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$detail->seq}}</div>
                                </div>
                                <label  class="control-label col-lg-1">券种类别</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$detail->coupon_class_name}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label  class="control-label col-lg-2">券种简称</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$detail->coupon_type_name}}</div>
                                </div>
                                <label  class="control-label col-lg-1">制券总数量</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$detail->amount}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">起始券号</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$detail->start_flow_no}}</div>
                                </div>
                                <label for="name" class="control-label col-lg-1">结束券号</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$detail->end_flow_no}}</div>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label class="control-label col-lg-2">券开始日期</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{!! date('Y年m月d日',strtotime($detail->begin_time)) !!}</div>
                                </div>
                                <label for="name" class="control-label col-lg-1">券结束日期</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{!! date('Y年m月d日',strtotime($detail->end_time)) !!}</div>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label class="control-label col-lg-2">申请人</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$detail->request_user_name}}</div>
                                </div>
                                <label for="name" class="control-label col-lg-1">申请日期</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{!! date('Y年m月d日',strtotime($detail->request_time)) !!}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">申请仓库</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$detail->request_storage_name}}</div>
                                </div>
                            </div>
                        @if($detail->from_list != '1')
                            <div class="form-group">
                                <form action="/make/audit/save" method="post">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <input type="hidden" name="select_list[]" value="{{$detail->id}}">
                                    <div class="form-group">
                                        <div class="col-lg-offset-2 col-lg-10">
                                            <button class="btn btn-success" type="submit" name="pass" value="1" >通过</button>
                                            <button class="btn btn-warning" type="submit" name="pass" value="0" >不通过</button>
                                            <button class="btn btn-default" onclick="location.href = '/make/audit/list{{$urlParam}}'" type="button">返回</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        @else
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
                                <label class="control-label col-lg-2">审核仓库</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$detail->approve_storage_name}}</div>
                                </div>
                                <label class="control-label col-lg-1">状态</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$status[$detail->status]}}</div>
                                </div>
                            </div>
                            <div class="form-group ">
                                <div class="col-lg-offset-2 col-lg-10">
                                        <button class="btn btn-default" onclick="location.href = '/make/list{{$urlParam}}'" type="button">确定</button>
                                </div>
                            </div>
                        @endif
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