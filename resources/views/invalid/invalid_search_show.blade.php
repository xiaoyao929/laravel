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
                    作废信息
                </header>
                <div class="panel-body">
                    <div class="form">
                        <form class="cmxform form-horizontal adminex-form">
                            <div class="form-group ">
                                <label class="control-label col-lg-2">作废单号</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'seq')}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">券种简称</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'coupon_type_name')}}</div>
                                </div>
                                <label class="control-label col-lg-1">券种类别</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'coupon_class_name')}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">起始券号</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'start_flow_no')}}</div>
                                </div>
                                <label for="name" class="control-label col-lg-1">结束券号</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'end_flow_no')}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">数量</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'amount')}}</div>
                                </div>
                                <label class="control-label col-lg-1">仓库名称</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'storage_name')}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">作废原因</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >
                                        @if( $coupon['reason'] == '其他' )
                                            {{array_get($coupon,'reason')}}-{{array_get($coupon,'text')}}
                                        @else
                                            {{array_get($coupon,'reason')}}
                                        @endif
                                    </div>
                                </div>
                                <label for="name" class="control-label col-lg-1">备注</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'memo')}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">申请人</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'request_user_name')}}</div>
                                </div>
                                <label for="name" class="control-label col-lg-1">申请日期</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'request_time')}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">审核人</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'approve_user_name')}}</div>
                                </div>
                                <label for="name" class="control-label col-lg-1">审核日期</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'approve_time')}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">状态</label>
                                <div class="col-lg-3">
                                    <div class="state" >
                                        @if( $coupon['status'] == 2 )
                                            <span class="label label-warning" data-val="1">{{$status[$coupon['status']]}}</span>
                                        @elseif( $coupon['status'] == 3 )
                                            <span class="label label-success" data-val="1">{{$status[$coupon['status']]}}</span>
                                        @else
                                            <span class="label label-info" data-val="1">{{$status[$coupon['status']]}}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-lg-offset-2 col-lg-10">
                                    <button class="btn btn-default" onclick="location.href = '/invalid/search{{$urlParam}}'" type="button">确认</button>
                                </div>
                            </div>
                        </form>
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