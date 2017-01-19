@extends('temp.index')

@section('style')
<style>
</style>
@stop

@section('scripts')
<script>
</script>
@stop

@section('body')
    <div class="row">
        <div class="col-sm-12">
            <section class="panel">
                <header class="panel-heading">
                    换券审核
                </header>
                <div class="panel-body">
                    <div class="form">
                        <form class="cmxform form-horizontal adminex-form" id="form" action="/exchange/replace/audit/save" method="post">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">

                            <div class="form-group ">
                                <label class="control-label col-lg-2">换券单号</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'seq')}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">券种简称</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'coupon_type_name')}}</div>
                                </div>
                                <label for="name" class="control-label col-lg-1">券种类别</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'coupon_class_name')}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label for="name" class="control-label col-lg-2">旧券券号</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'from_flow_no')}}</div>
                                </div>
                                <label for="name" class="control-label col-lg-1">销售仓库</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'from_storage_name')}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">新券券号</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'to_flow_no')}}</div>
                                </div>
                                <label for="name" class="control-label col-lg-1">所属仓库</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'to_storage_name')}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">换券原因</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >
                                        @if( $coupon['reason'] == '其他' )
                                            {{array_get($coupon,'reason')}}-{{array_get($coupon,'text')}}
                                        @else
                                            {{array_get($coupon,'reason')}}
                                        @endif</div>
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
                                <label for="name" class="control-label col-lg-2">申请仓库</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'to_storage_name')}}</div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-lg-offset-2 col-lg-10">
                                    <input type="hidden" name="id[]" value="{{array_get($coupon,'id')}}">
                                    <button class="btn btn-success" type="submit" name="action" value="pass">通过</button>
                                    <button class="btn btn-danger" type="submit" name="action" value="no_pass">不通过</button>
                                    <button class="btn btn-default" onclick="location.href = '/sale/replace/audit{{$urlParam}}'" type="button">返回</button>
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