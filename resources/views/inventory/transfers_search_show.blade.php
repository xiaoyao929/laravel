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
<script>
    $('.pass-select li').click(function () {
        var text = $(this).text();
        $('#pass-select').val(text);
        $('#pass-show').html(text+ ' <span class="caret"></span>');
    })
    $('#pass_save').click(function () {
        var select = $('#pass-select').val();
        var text   = $('#pass-text').val();
        var msg    = $('#pass-msg').val();
        $("input[name='pass_select']").val(select);
        $("input[name='pass_text']").val(text);
        $("input[name='pass_msg']").val(msg);
        $('#form').submit();
    })
</script>
@stop

@section('body')
    <div class="row">
        <div class="col-sm-12">
            <section class="panel">
                <header class="panel-heading">
                    调拨确认
                </header>
                <div class="panel-body">
                    <div class="form">
                        <form class="cmxform form-horizontal adminex-form" id="form" action="/inventory/transfers/confirm/save" method="post">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">

                            <div class="form-group ">
                                <label class="control-label col-lg-2">调拨单号</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'seq')}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">券种简称</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'coupon_type_name')}}</div>
                                </div>
                                <label for="name" class="control-label col-lg-1">调拨数量</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'amount')}}</div>
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
                                <label class="control-label col-lg-2">调拨仓库</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'from_storage_name')}}</div>
                                </div>
                                <label for="name" class="control-label col-lg-1">接受仓库</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'to_storage_name')}}</div>
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
                                <label class="control-label col-lg-2">确认人</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'confirm_user_name')}}</div>
                                </div>
                                <label for="name" class="control-label col-lg-1">确认日期</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'confirm_time')}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">状态</label>
                                <div class="col-lg-3">
                                    <div class="state" >
                                        @if( $coupon['status'] == 2 || $coupon['status'] == 5 )
                                            <span class="label label-warning" data-val="1">{{$status[$coupon['status']]}}</span>
                                        @elseif( $coupon['status'] == 3 || $coupon['status'] == 4 )
                                            <span class="label label-success" data-val="1">{{$status[$coupon['status']]}}</span>
                                        @else
                                            <span class="label label-info" data-val="1">{{$status[$coupon['status']]}}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-lg-offset-2 col-lg-10">
                                    <button class="btn btn-default" onclick="location.href = '/inventory/transfers/search{{$urlParam}}'" type="button">确认</button>
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