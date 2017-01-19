@extends('temp.index')

@section('style')
<style>
    .title{
        text-align: left !important;
        padding-left: 5%;
    }
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
                    销售登记审核
                </header>
                <div class="panel-body">
                    <div class="form">
                        <form class="cmxform form-horizontal adminex-form" id="form" action="/sale/audit/save" method="post">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <div class="form-group ">
                                <label class="control-label col-lg-2 title"><span class="red"><i class="fa fa-square"></i></span> 订单信息</label>
                            </div>
                            <div class="form-group ">
                                <label class="control-label col-lg-2">销售单号</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'seq')}}</div>
                                </div>
                                <label class="control-label col-lg-1">客户类型</label>
                                <div class="col-lg-3">
                                    @if( $coupon['customer_type'] == 1 )
                                        <div class="form-control" >内部员工</div>
                                    @elseif( $coupon['customer_type'] == 2 )
                                        <div class="form-control" >个人用户</div>
                                    @elseif( $coupon['customer_type'] == 3 )
                                        <div class="form-control" >公司用户</div>
                                    @endif
                                </div>
                            </div>
                            @if( $coupon['customer_type'] == 1 )
                                <div class="form-group ">
                                    <label class="control-label col-lg-2">公司名称</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($coupon['customer_info'],'company_name')}}</div>
                                    </div>
                                    <label for="name" class="control-label col-lg-1">公司编号</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($coupon['customer_info'],'company_id')}}</div>
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <label class="control-label col-lg-2">部门名称</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($coupon['customer_info'],'sector_name')}}</div>
                                    </div>
                                    <label for="name" class="control-label col-lg-1">部门编号</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($coupon['customer_info'],'sector_id')}}</div>
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <label class="control-label col-lg-2">领用人</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($coupon['customer_info'],'recipients')}}</div>
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <label class="control-label col-lg-2">费用承担部门</label>
                                    <div class="col-lg-3">
                                        @if( $coupon['customer_info']['is_pay'] == 1 )
                                            <div class="form-control" >是</div>
                                        @else()
                                            <div class="form-control" >否</div>
                                        @endif
                                    </div>
                                    @if( $coupon['customer_info']['is_pay'] == 2 )
                                        <label for="name" class="control-label col-lg-1">费用承担</label>
                                        <div class="col-lg-3">
                                            <div class="form-control" >{{array_get($coupon['customer_info'],'pay_sector')}}</div>
                                        </div>
                                    @endif
                                </div>
                            @elseif( $coupon['customer_type'] == 2 )
                                <div class="form-group ">
                                    <label class="control-label col-lg-2">客户名称</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($coupon['customer_info'],'name')}}</div>
                                    </div>
                                    <label for="name" class="control-label col-lg-1">联系人手机</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($coupon['customer_info'],'contact_mobile')}}</div>
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <label class="control-label col-lg-2">联系地址</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($coupon['customer_info'],'contact_addr')}}</div>
                                    </div>
                                    <label for="name" class="control-label col-lg-1">联系人邮箱</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($coupon['customer_info'],'contact_email')}}</div>
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <label class="control-label col-lg-2">证件类型</label>
                                    <div class="col-lg-3">
                                        @if( $coupon['customer_info']['certificate_type'] == 1 )
                                            <div class="form-control" >身份证</div>
                                        @elseif( $coupon['customer_info']['certificate_type'] == 2 )
                                            <div class="form-control" >护照</div>
                                        @elseif( $coupon['customer_info']['certificate_type'] == 3 )
                                            <div class="form-control" >营业执照</div>
                                        @elseif( $coupon['customer_info']['certificate_type'] == 4 )
                                            <div class="form-control" >机构代码证</div>
                                        @elseif( $coupon['customer_info']['certificate_type'] == 5 )
                                            <div class="form-control" >{{array_get($coupon['customer_info'],'certificate_other_type')}}</div>
                                        @else
                                            <div class="form-control" ></div>
                                        @endif
                                    </div>
                                    <label for="name" class="control-label col-lg-1">证件号码</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($coupon['customer_info'],'certificate_code')}}</div>
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <label class="control-label col-lg-2">支付方式</label>
                                    <div class="col-lg-3">
                                        @if( $coupon['pay_type'] == '其他' )
                                            <div class="form-control" >{{array_get( $coupon, 'pay_text' )}}</div>
                                        @else
                                            <div class="form-control" >{{array_get( $coupon, 'pay_type' )}}</div>
                                        @endif
                                    </div>
                                </div>
                            @elseif( $coupon['customer_type'] == 3 )
                                <div class="form-group ">
                                    <label class="control-label col-lg-2">客户名称</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($coupon['customer_info'],'name')}}</div>
                                    </div>
                                    <label for="name" class="control-label col-lg-1">联络人</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($coupon['customer_info'],'contact_name')}}</div>
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <label class="control-label col-lg-2">联系电话</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($coupon['customer_info'],'contact_tel')}}</div>
                                    </div>
                                    <label for="name" class="control-label col-lg-1">联系人手机</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($coupon['customer_info'],'contact_mobile')}}</div>
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <label class="control-label col-lg-2">联系地址</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($coupon['customer_info'],'contact_addr')}}</div>
                                    </div>
                                    <label for="name" class="control-label col-lg-1">联系人邮箱</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($coupon['customer_info'],'contact_email')}}</div>
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <label class="control-label col-lg-2">证件类型</label>
                                    <div class="col-lg-3">
                                        @if( $coupon['customer_info']['certificate_type'] == 1 )
                                            <div class="form-control" >身份证</div>
                                        @elseif( $coupon['customer_info']['certificate_type'] == 2 )
                                            <div class="form-control" >护照</div>
                                        @elseif( $coupon['customer_info']['certificate_type'] == 3 )
                                            <div class="form-control" >营业执照</div>
                                        @elseif( $coupon['customer_info']['certificate_type'] == 4 )
                                            <div class="form-control" >机构代码证</div>
                                        @elseif( $coupon['customer_info']['certificate_type'] == 5 )
                                            <div class="form-control" >{{array_get($coupon['customer_info'],'certificate_other_type')}}</div>
                                        @else
                                            <div class="form-control" ></div>
                                        @endif
                                    </div>
                                    <label for="name" class="control-label col-lg-1">证件号码</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($coupon['customer_info'],'certificate_code')}}</div>
                                    </div>
                                </div>

                                <div class="form-group ">
                                    <label class="control-label col-lg-2">支付方式</label>
                                    <div class="col-lg-3">
                                        @if( $coupon['pay_type'] == '其他' )
                                            <div class="form-control" >{{array_get( $coupon, 'pay_text' )}}</div>
                                        @else
                                            <div class="form-control" >{{array_get( $coupon, 'pay_type' )}}</div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div class="form-group ">
                                <label for="name" class="control-label col-lg-2">备注</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'memo')}}</div>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="name" class="control-label col-lg-2">申请人</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'request_user_name')}}</div>
                                </div>
                                <label for="name" class="control-label col-lg-1">申请时间</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'request_time')}}</div>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="name" class="control-label col-lg-2">申请仓库</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'storage_name')}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2 title"><span class="red"><i class="fa fa-square"></i></span> 券信息</label>
                            </div>

                            @foreach( $coupon['coupons'] as $v )
                                <div class="form-group ">
                                    <label class="control-label col-lg-2">券简称</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($v,'coupon_type_name')}}</div>
                                    </div>
                                    <label for="name" class="control-label col-lg-1">券类别</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($v,'coupon_class_name')}}</div>
                                    </div>
                                </div>
                                @if( $v['coupon_class_id'] == 1 )
                                    <div class="form-group ">
                                        <label class="control-label col-lg-2">单价</label>
                                        <div class="col-lg-3">
                                            <div class="form-control" >{{priceShow( $v['price'] )}}</div>
                                        </div>
                                    </div>
                                @endif

                                @foreach( $v['flow_no'] as $no )
                                    <div class="form-group ">
                                        <label class="control-label col-lg-2">开始券号</label>
                                        <div class="col-lg-3">
                                            <div class="form-control" >{{array_get($no,'start')}}</div>
                                        </div>
                                        <label for="name" class="control-label col-lg-1">结束券号</label>
                                        <div class="col-lg-3">
                                            <div class="form-control" >{{array_get($no,'end')}}</div>
                                        </div>
                                    </div>

                                    <div class="form-group ">
                                        <label class="control-label col-lg-2">券数量</label>
                                        <div class="col-lg-3">
                                            <div class="form-control" >{{array_get($no,'amount')}}</div>
                                        </div>
                                    </div>
                                @endforeach
                            @endforeach

                            <div class="form-group ">
                                <label class="control-label col-lg-2 title"><span class="red"><i class="fa fa-square"></i></span> 订单信息汇总</label>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">GC券数量</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'gc_amount')}}</div>
                                </div>
                                <label for="name" class="control-label col-lg-1">原价总金额</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{priceShow(array_get($coupon,'price'))}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">折扣总金额</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{priceShow( (int)$coupon['price'] - (int)$coupon['sale_price'] )}}</div>
                                </div>
                                <label for="name" class="control-label col-lg-1">折后总金额</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{priceShow(array_get($coupon,'sale_price'))}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">BOG券数量</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($coupon,'bog_amount')}}</div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-lg-offset-2 col-lg-10">
                                    <input type="hidden" name="id[]" value="{{array_get($coupon,'id')}}">
                                    <button class="btn btn-success" type="submit" name="action" value="pass">通过</button>
                                    <button class="btn btn-danger" type="submit" name="action" value="no_pass">不通过</button>
                                    <button class="btn btn-default" onclick="location.href = '/sale/audit/list{{$urlParam}}'" type="button">返回</button>
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