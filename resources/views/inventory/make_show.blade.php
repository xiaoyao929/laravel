@extends('temp.index')

@section('style')
<style>
</style>
@stop

@section('scripts')

@stop

@section('body')
    <div class="row">
        <div class="col-sm-12">
            <section class="panel">
                <header class="panel-heading">
                    制券信息
                </header>
                <div class="panel-body">
                    <div class="form">
                        <form class="cmxform form-horizontal adminex-form" action="/inventory/make/save" method="post">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <div class="form-group ">
                                <label class="control-label col-lg-2">制券单号</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'seq')}}</div>
                                </div>
                                <label for="name" class="control-label col-lg-1">券种类别</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'coupon_class_name')}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">券种简称</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'coupon_type_name')}}</div>
                                </div>
                                <label for="name" class="control-label col-lg-1">制券总数量</label>
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
                                <label class="control-label col-lg-2">券开始日期</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{!! date("Y-m-d",strtotime($coupon['begin_time'])) !!}</div>
                                </div>
                                <label for="name" class="control-label col-lg-1">券结束日期</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{!! date("Y-m-d",strtotime($coupon['end_time'])) !!}</div>
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
                                <label class="control-label col-lg-2">申请仓库</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'request_storage_name')}}</div>
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
                                <label class="control-label col-lg-2">审核仓库</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'approve_storage_name')}}</div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-lg-offset-2 col-lg-10">
                                    <input type="hidden" name="id[]" value="{{array_get($coupon,'id')}}">
                                    <button class="btn btn-primary" type="submit">制券完成</button>
                                    <button class="btn btn-default" onclick="location.href = '/inventory/make/list{{$urlParam}}'" type="button">返回</button>
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