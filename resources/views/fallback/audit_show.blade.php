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
                        退券信息查询
                    @else
                        退券审核
                    @endif
                </header>
                <div class="panel-body">
                    <div class="form">
                        <div class="cmxform form-horizontal adminex-form" >
                            <div class="form-group ">
                                <label class="control-label col-lg-1"><span class="red"><i class="fa fa-square"></i></span> 退券信息</label>
                            </div>
                            <div class="form-group ">
                                <label class="control-label col-lg-2">退券单号</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$detail->seq}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label  class="control-label col-lg-2">券简称</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$detail->coupon_type_name}}</div>
                                </div>
                                <label  class="control-label col-lg-1">退券数量</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$detail->amount}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">开始券号</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$detail->start_flow_no}}</div>
                                </div>
                                <label for="name" class="control-label col-lg-1">结束券号</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$detail->end_flow_no}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">退券原因</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >
                                        @if($detail->reason_type == '3')
                                            {{$detail->reason_content}}
                                        @else
                                            {{$reasonType[$detail->reason_type]}}
                                        @endif
                                    </div>
                                </div>
                                <label class="control-label col-lg-1">备注</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$detail->memo}}</div>
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
                                    <label class="control-label col-lg-2">审核仓库</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{$detail->approve_storage_name}}</div>
                                    </div>
                                    <label class="control-label col-lg-1">审核状态</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{$status[$detail->status]}}</div>
                                    </div>
                                </div>
                            @endif
                            <div class="form-group ">
                                <label class="control-label col-lg-1"><span class="red"><i class="fa fa-square"></i></span> 销售信息</label>
                            </div>
                            <div class="form-group ">
                                <label  class="control-label col-lg-2">销售单号</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$detail->sale_seq}}</div>
                                </div>
                                <label  class="control-label col-lg-1">客户类型</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$customerType[$detail->customer_type]['name']}}</div>
                                </div>
                            </div>
                            <!--  内部员工 -->
                            @if($detail->customer_type == '1')
                                <div class="form-group ">
                                    <label  class="control-label col-lg-2">公司名称</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{$customerInfo['company_name']}}</div>
                                    </div>
                                    <label  class="control-label col-lg-1">公司编号</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{$customerInfo['company_id']}}</div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label  class="control-label col-lg-2">部门名称</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{$customerInfo['sector_name']}}</div>
                                    </div>
                                    <label  class="control-label col-lg-1">部门编号</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{$customerInfo['sector_id']}}</div>
                                    </div>
                                </div>
                            @endif
                            <!--  个人用户 -->
                            @if($detail->customer_type == '2')
                                <div class="form-group ">
                                    <label  class="control-label col-lg-2">客户名称</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{$customerInfo['name']}}</div>
                                    </div>
                                    <label  class="control-label col-lg-1">联系人手机</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{$customerInfo['contact_mobile']}}</div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label  class="control-label col-lg-2">联系地址</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{$customerInfo['contact_addr']}}</div>
                                    </div>
                                    <label  class="control-label col-lg-1">联系人邮箱</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{$customerInfo['contact_email']}}</div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label  class="control-label col-lg-2">证件类型</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >
                                            @if($customerInfo['certificate_type'] == '5')
                                                {{$customerInfo['certificate_other_type']}}
                                            @else
                                                {{empty($certificateType[$customerInfo['certificate_type']])?'':$certificateType[$customerInfo['certificate_type']]}}
                                            @endif
                                        </div>
                                    </div>
                                    <label  class="control-label col-lg-1">证件号码</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{$customerInfo['certificate_code']}}</div>
                                    </div>
                                </div>
                            @endif
                            <!--  公司用户 -->
                            @if($detail->customer_type == '3')
                                <div class="form-group ">
                                    <label  class="control-label col-lg-2">客户名称</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{$customerInfo['name']}}</div>
                                    </div>
                                    <label  class="control-label col-lg-1">联系人</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{$customerInfo['contact_name']}}</div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label  class="control-label col-lg-2">联系电话</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{$customerInfo['contact_tel']}}</div>
                                    </div>
                                    <label  class="control-label col-lg-1">地址</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{$customerInfo['contact_addr']}}</div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label  class="control-label col-lg-2">联系邮箱</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{$customerInfo['contact_email']}}</div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label  class="control-label col-lg-2">证件类型</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >
                                            @if($customerInfo['certificate_type'] == '5')
                                                {{$customerInfo['certificate_other_type']}}
                                            @else
                                                {{empty($certificateType[$customerInfo['certificate_type']])?'':$certificateType[$customerInfo['certificate_type']]}}
                                            @endif
                                        </div>
                                    </div>
                                    <label  class="control-label col-lg-1">证件号码</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{$customerInfo['certificate_code']}}</div>
                                    </div>
                                </div>
                            @endif
                            <div class="form-group ">
                                <label  class="control-label col-lg-2">支付方式</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$detail->pay_type}}</div>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label  class="control-label col-lg-2">备注</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$detail->sale_memo}}</div>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label class="control-label col-lg-1"><span class="red"><i class="fa fa-square"></i></span> 订单信息汇总</label>
                            </div>
                            <div class="form-group ">
                                <label  class="control-label col-lg-2">GC券数量</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$detail->gc_amount}}</div>
                                </div>
                                <label  class="control-label col-lg-1">原价总金额</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{priceShow($detail->price)}}</div>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label  class="control-label col-lg-2">折扣总金额</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{priceShow($detail->price-$detail->sale_price)}}</div>
                                </div>
                                <label  class="control-label col-lg-1">折后总金额</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{priceShow($detail->sale_price)}}</div>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label  class="control-label col-lg-2">BOG券数量</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$detail->bog_amount}}</div>
                                </div>
                            </div>
                            @if($from != '1')
                                <div class="form-group">
                                    <form action="/exchange/fallback/audit/save" method="post">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="hidden" name="select_list[]" value="{{$detail->sale_seq.'-'.$detail->id}}">
                                        <div class="form-group">
                                            <div class="col-lg-offset-2 col-lg-10">
                                                <button class="btn btn-success" type="submit" name="pass" value="1" >通过</button>
                                                <button class="btn btn-warning" type="submit" name="pass" value="0" >不通过</button>
                                                <button class="btn btn-default" onclick="location.href = '/exchange/fallback/audit/list{{$urlParam}}'" type="button">返回</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            @else
                                <div class="form-group">
                                    <div class="col-lg-offset-2 col-lg-10">
                                        <button class="btn btn-default" onclick="location.href = '/exchange/fallback/list{{$urlParam}}'" type="button">确定</button>
                                    </div>
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