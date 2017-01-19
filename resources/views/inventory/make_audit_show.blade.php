@extends('temp.index')

@section('style')
<style>
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
                    制券信息
                </header>
                <div class="panel-body">
                    <div class="form">
                        <form class="cmxform form-horizontal adminex-form" id="form" action="/inventory/make/audit/save" method="post">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="pass_select" value="">
                            <input type="hidden" name="pass_text" value="">
                            <input type="hidden" name="pass_msg" value="">

                            <div class="form-group ">
                                <label class="control-label col-lg-2">入库单号</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'seq')}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">制券单号</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'make_seq')}}</div>
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

                            <div class="form-group">
                                <div class="col-lg-offset-2 col-lg-10">
                                    <input type="hidden" name="id[]" value="{{array_get($coupon,'id')}}">
                                    <button class="btn btn-success" type="submit" name="action" value="pass">通过</button>
                                    <button class="btn btn-danger no_pass" type="button" data-toggle="modal" data-target="#nopass">不通过</button>
                                    <button class="btn btn-default" onclick="location.href = '/inventory/make/audits{{$urlParam}}'" type="button">返回</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="nopass" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title" id="myModalLabel">不通过原因</h4>
                </div>
                <div class="modal-body">
                    <div class="form">
                        <form class="cmxform form-horizontal adminex-form" >
                            <div class="form-group ">
                                <label for="name" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 原因</label>
                                <div class="col-lg-8">
                                    <div class="input-group">
                                        <div class="input-group-btn">
                                            <button type="button" class="btn btn-default dropdown-toggle" id="pass-show" data-toggle="dropdown">质量 <span class="caret"></span></button>
                                            <input type="hidden" id="pass-select" value="质量">
                                            <ul class="dropdown-menu pass-select">
                                                <li><a href="javascript:void(0)">质量</a></li>
                                                <li><a href="javascript:void(0)">其他</a></li>
                                            </ul>
                                        </div>
                                        <input type="text" class="form-control" id="pass-text" autocomplete="off" placeholder="(拒绝理由)" >
                                    </div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label for="acronym" class="control-label col-lg-2">备注</label>
                                <div class="col-lg-8">
                                    <textarea rows="6" class="form-control" id="pass-msg"></textarea>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="pass_save">确认</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
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