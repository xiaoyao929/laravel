@extends('temp.index')

@section('style')
    <style>

    </style>
    <link href="{{ URL::asset('/css/bootstrap-treeview.css') }}" rel="stylesheet">
@stop

@section('scripts')
    <script>
        $("[name='customer_type']").change(function(){
            var this_val = $(this).val();
            if(this_val == '1'){        //个人
                $('.customer_type_2').closest(".form-group").hide();
                $("[name='contact_tel']").attr("required",false).closest(".form-group").find("label span").hide();
                $("[name='contact_mobile']").attr("required",true).closest(".form-group").find("label span").show();
            }else{                      //单位
                $('.customer_type_2').closest(".form-group").show();
                $("[name='contact_tel']").attr("required",true).closest(".form-group").find("label span").show();
                $("[name='contact_mobile']").attr("required",false).closest(".form-group").find("label span").hide();
            };
            $("input:hidden").attr("required",false);
        });
        $("[name='customer_type']").change();
        $("[name='certificate_type']").change(function(){
            var v = $(this).val();
            if(v==5){
                $("[name='certificate_other_type']").show().attr("required",true);
            }else{
                $("[name='certificate_other_type']").hide().attr("required",false);
            };
            if(v!=""){
                $("[name='certificate_code']").attr("required",true)
            }else{
                $("[name='certificate_code']").attr("required",false)
            };
            $("input:hidden").attr("required",false);
        });
        $("[name='certificate_type']").change();

    </script>
    <script src="{{ URL::asset('/js/jquery.validate.min.js') }}"></script>
    <script src="{{ URL::asset('/js/bootstrap-treeview.js') }}"></script>
@stop

@section('body')
    <div class="row">
        <div class="col-sm-12">
            <section class="panel">
                <header class="panel-heading">
                    @if(empty($rowData['id']))
                        客户信息新增
                    @else
                        客户信息修改
                    @endif
                </header>
                <section class="panel">
                    <div class="panel-body">
                        <button class="btn btn-default" type="button" data-toggle="modal" data-target="#myModal">导入数据</button>
                    </div>
                </section>

                <div class="panel-body">
                    <div class="form">
                        <form class="cmxform form-horizontal adminex-form" method="post" action="/client/customer/save" autocomplete="off">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            @if(isset($rowData['id']))
                                <input type="hidden" name="id" value="{{$rowData['id']}}">
                                <input type="hidden" name="action" value="2">
                            @else
                                <input type="hidden" name="action" value="1">
                            @endif
                            <div class="form-group ">
                                <label for="nickname" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 客户类型</label>
                                <div class="col-lg-4 col-xs-12">
                                    <select class="form-control" name="customer_type" id="customer_type">
                                        @foreach($customerType as $key => $value)
                                            @if(isset($rowData['customer_type']) && $rowData['customer_type'] == $key)
                                                <option value="{{$key}}" selected>{{$value}}</option>
                                            @else
                                                <option value="{{$key}}" >{{$value}}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="tel" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 客户名称</label>
                                <div class="col-lg-4 col-xs-12">
                                    <input class="form-control" name="name" type="text" value="{{array_get($rowData,'name')}}" autocomplete="off" required minlength="2"/>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="tel" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 联络人</label>
                                <div class="col-lg-4 col-xs-12">
                                    <input class="form-control customer_type_2" required name="contact_name" type="text" value="{{array_get($rowData,'contact_name')}}" autocomplete="off" minlength="2"/>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="tel" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 联系电话</label>
                                <div class="col-lg-4 col-xs-12">
                                    <input class="form-control" name="contact_tel" type="text" value="{{array_get($rowData,'contact_tel')}}" autocomplete="off" required minlength="8" maxlength="12" />
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="tel" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 联系人手机</label>
                                <div class="col-lg-4 col-xs-12">
                                    <input class="form-control" name="contact_mobile" type="number" value="{{array_get($rowData,'contact_mobile')}}" autocomplete="off" required minlength="11" maxlength="11"/>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="tel" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 联系地址</label>
                                <div class="col-lg-4 col-xs-12">
                                    <input class="form-control" name="contact_addr" type="text" value="{{array_get($rowData,'contact_addr')}}" autocomplete="off" required minlength="2" maxlength="30" />
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="tel" class="control-label col-lg-2"> 证件类型</label>
                                <div class="col-lg-2 col-xs-12">
                                    <select class="form-control" name="certificate_type">
                                        <option value="" selected>请选择</option>
                                        @foreach($certificateType as $key => $value)
                                            @if(isset($rowData['certificate_type']) && $rowData['certificate_type'] == $key)
                                                <option value="{{$key}}" selected>{{$value}}</option>
                                            @else
                                                <option value="{{$key}}" >{{$value}}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2 col-xs-12">
                                    <input class="form-control" name="certificate_other_type" type="text" value="{{array_get($rowData,'certificate_other_type')}}" autocomplete="off" minlength="2" style="display:none;" />
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="tel" class="control-label col-lg-2"> 证件号码</label>
                                <div class="col-lg-4 col-xs-12">
                                    <input class="form-control" name="certificate_code" type="text" value="{{array_get($rowData,'certificate_code')}}" autocomplete="off" minlength="2" maxlength="36"/>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="tel" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 联系人邮箱</label>
                                <div class="col-lg-4 col-xs-12">
                                    <input class="form-control" name="contact_email" type="email" value="{{array_get($rowData,'contact_email')}}" autocomplete="off" required minlength="2" />
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-lg-offset-2 col-lg-10">
                                    <button class="btn btn-primary" type="submit">确定</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

        </div>
    </div>
    <!-- Modal -->        
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="cmxform form-horizontal adminex-form" method="post" action="/client/customer/file/save" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title text-center">上传文件</h4>
                    </div>
                    <div class="modal-body">
                        <p class="text-left">批量导入您的客户信息，请使用模板文件导入。<a href="/templat/customer.csv">下载模板文件</a><br>注意：<br>1. 每次导入数据限1000条以内<br>2. 需保证以下条件，若不唯一，则改行数据不会被导入<br>&nbsp;&nbsp;&nbsp;个人：姓名+手机号 唯一<br>&nbsp;&nbsp;&nbsp;单位：公司名称+联络人 唯一</p><hr>
                        <div class="form-group text-left" style="margin-left:0"><label for="file2">选择文件</label><input type="file" name="csvFile" id="csvFile"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" id="save">确认</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('footer')
    <footer>
        2014 &copy; AdminEx by ThemeBucket
    </footer>
@stop