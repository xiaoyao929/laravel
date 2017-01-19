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

@stop

@section('body')
    <div class="row">
        <div class="col-md-12">
            <!--pagination start-->
            <section class="panel">
                <header class="panel-heading">
                    券详情查询
                </header>
                <div class="panel-body">
                    <form class="cmxform form-horizontal adminex-form" method="get" action="" >

                        <div class="form-group">
                            <label for="parent_id" class="control-label col-lg-1"><span class="red"><i class="fa fa-asterisk"></i></span> 券号</label>
                            <div class="col-lg-4">
                                <input type="number" class="form-control" name="flow_no" value="{{array_get( $search, 'flow_no' )}}">
                            </div>
                            <div class="col-lg-7">
                                <button class="btn btn-primary" type="submit">开始搜索</button>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
            <!--pagination end-->
        </div>
    </div>
    @if( !empty( $info ))
    <div class="row">
        <div class="col-sm-12">
            <section class="panel">
                <div class="panel-body">
                    <div class="form">
                        <form class="cmxform form-horizontal adminex-form">
                            <div class="form-group">
                                <label class="control-label col-lg-2 title"><span class="red"><i class="fa fa-square"></i></span> 单券信息</label>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-lg-2">券号</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($info['info'],'coupon_flow_no')}}</div>
                                </div>
                                <label for="name" class="control-label col-lg-1">券状态</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$info['info_status'][$info['info']['status']]}}</div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-lg-2">开始日期</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{!! date("Y-m-d",strtotime($info['info']['begin_time'])) !!}</div>
                                </div>
                                <label for="name" class="control-label col-lg-1">结束日期</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{!! date("Y-m-d",strtotime($info['info']['end_time'])) !!}</div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-lg-2">所属仓库</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($info['info'],'storage_name')}}</div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-lg-2 title"><span class="red"><i class="fa fa-square"></i></span> 券种信息</label>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">编号</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($info['info'],'coupon_type_id')}}</div>
                                </div>
                                <label for="name" class="control-label col-lg-1">自定义编号</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($info['info'],'custom_no')}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">券种简称</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($info['info'],'coupon_type_name')}}</div>
                                </div>
                                <label for="name" class="control-label col-lg-1">券种详称</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($info['info'],'detail_name')}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">类别</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($info['info'],'coupon_class_name')}}</div>
                                </div>
                                <label for="name" class="control-label col-lg-1">单价</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{priceShow(array_get($info['info'],'coupon_price'))}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">终端组编号</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($info['info'],'group_id')}}</div>
                                </div>
                                <label for="name" class="control-label col-lg-1">终端组名称</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($info['info'],'group_name')}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">备注</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($info['info'],'type_memo')}}</div>
                                </div>
                            </div>
                            @if( !empty( $info['make'] ) )
                                <div class="form-group">
                                    <label class="control-label col-lg-2 title"><span class="red"><i class="fa fa-square"></i></span> 制券信息</label>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label col-lg-2">制券单号</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['make'],'seq')}}</div>
                                    </div>
                                    <label for="name" class="control-label col-lg-1">制券总数量</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['make'],'amount')}}</div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label col-lg-2">申请人</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['make'],'request_user_name')}}</div>
                                    </div>
                                    <label for="name" class="control-label col-lg-1">申请日期</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{$info['make']['request_time']}}</div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label col-lg-2">申请仓库</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['make'],'request_storage_name')}}</div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label col-lg-2">审核人</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['make'],'approve_user_name')}}</div>
                                    </div>
                                    <label for="name" class="control-label col-lg-1">审核日期</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >@if( !empty( $info['make']['approve_time'] ) ){{$info['make']['approve_time']}} @endif</div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label col-lg-2">审核仓库</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['make'],'approve_storage_name')}}</div>
                                    </div>
                                    <label for="name" class="control-label col-lg-1">状态</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{$info['make_status'][$info['make']['status']]}}</div>
                                    </div>
                                </div>
                            @endif
                            @if( !empty( $info['putin'] ) )
                                <div class="form-group">
                                    <label class="control-label col-lg-2 title"><span class="red"><i class="fa fa-square"></i></span> 入库信息</label>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label col-lg-2">入库单号</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['putin'],'seq')}}</div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label col-lg-2">申请人</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['putin'],'request_user_name')}}</div>
                                    </div>
                                    <label for="name" class="control-label col-lg-1">申请日期</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['putin'],'request_time')}}</div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label col-lg-2">审核人</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['putin'],'approve_user_name')}}</div>
                                    </div>
                                    <label for="name" class="control-label col-lg-1">审核日期</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >@if( !empty( $info['putin']['approve_time'] ) ){{array_get($info['putin'],'approve_time')}} @endif</div>
                                    </div>
                                </div>
                            @endif

                            @if( !empty( $info['transfers'] ) )
                                <div class="form-group">
                                    <label class="control-label col-lg-2 title"><span class="red"><i class="fa fa-square"></i></span> 调拨信息</label>
                                </div>
                                @foreach( $info['transfers'] as $v )
                                    <div class="form-group ">
                                        <label class="control-label col-lg-2">调拨单号</label>
                                        <div class="col-lg-3">
                                            <div class="form-control" >{{array_get($v,'seq')}}</div>
                                        </div>
                                        <label for="name" class="control-label col-lg-1">调拨数量</label>
                                        <div class="col-lg-3">
                                            <div class="form-control" >{{array_get($v,'amount')}}</div>
                                        </div>
                                    </div>
                                    <div class="form-group ">
                                        <label class="control-label col-lg-2">开始编号</label>
                                        <div class="col-lg-3">
                                            <div class="form-control" >{{array_get($v,'start_flow_no')}}</div>
                                        </div>
                                        <label for="name" class="control-label col-lg-1">结束编号</label>
                                        <div class="col-lg-3">
                                            <div class="form-control" >{{array_get($v,'end_flow_no')}}</div>
                                        </div>
                                    </div>
                                    <div class="form-group ">
                                        <label class="control-label col-lg-2">调拨仓库</label>
                                        <div class="col-lg-3">
                                            <div class="form-control" >{{array_get($v,'from_storage_name')}}</div>
                                        </div>
                                        <label for="name" class="control-label col-lg-1">接受仓库</label>
                                        <div class="col-lg-3">
                                            <div class="form-control" >{{array_get($v,'to_storage_name')}}</div>
                                        </div>
                                    </div>
                                    <div class="form-group ">
                                        <label class="control-label col-lg-2">申请人</label>
                                        <div class="col-lg-3">
                                            <div class="form-control" >{{array_get($v,'request_user_name')}}</div>
                                        </div>
                                        <label for="name" class="control-label col-lg-1">申请日期</label>
                                        <div class="col-lg-3">
                                            <div class="form-control" >{{array_get($v,'request_time')}}</div>
                                        </div>
                                    </div>
                                    <div class="form-group ">
                                        <label class="control-label col-lg-2">审核人</label>
                                        <div class="col-lg-3">
                                            <div class="form-control" >{{array_get($v,'approve_user_name')}}</div>
                                        </div>
                                        <label for="name" class="control-label col-lg-1">审核日期</label>
                                        <div class="col-lg-3">
                                            <div class="form-control" >@if( !empty( $v['approve_time'] ) ){{array_get($v,'approve_time')}} @endif</div>
                                        </div>
                                    </div>
                                    <div class="form-group ">
                                        <label class="control-label col-lg-2">确认人</label>
                                        <div class="col-lg-3">
                                            <div class="form-control" >{{array_get($v,'confirm_user_name')}}</div>
                                        </div>
                                        <label for="name" class="control-label col-lg-1">确认日期</label>
                                        <div class="col-lg-3">
                                            <div class="form-control" >@if( !empty( $v['confirm_time'] ) ){{array_get($v,'confirm_time')}} @endif</div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                            @if( !empty( $info['invalid'] ) )
                                <div class="form-group">
                                    <label class="control-label col-lg-2 title"><span class="red"><i class="fa fa-square"></i></span> 作废信息</label>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label col-lg-2">作废单号</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['invalid'],'seq')}}</div>
                                    </div>
                                    <label for="name" class="control-label col-lg-1">作废数量</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['invalid'],'amount')}}</div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label col-lg-2">开始编号</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['invalid'],'start_flow_no')}}</div>
                                    </div>
                                    <label for="name" class="control-label col-lg-1">结束编号</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['invalid'],'end_flow_no')}}</div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label col-lg-2">作废原因</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >
                                            @if( $info['invalid']['reason'] == '其他' )
                                                {{array_get($info['invalid'],'reason')}}-{{array_get($info['invalid'],'text')}}
                                            @else
                                                {{array_get($info['invalid'],'reason')}}
                                            @endif
                                        </div>
                                    </div>
                                    <label for="name" class="control-label col-lg-1">备注</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['invalid'],'memo')}}</div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label col-lg-2">申请人</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['invalid'],'request_user_name')}}</div>
                                    </div>
                                    <label for="name" class="control-label col-lg-1">申请日期</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['invalid'],'request_time')}}</div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label col-lg-2">审核人</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['invalid'],'approve_user_name')}}</div>
                                    </div>
                                    <label for="name" class="control-label col-lg-1">审核日期</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >@if( !empty( $info['invalid']['approve_time'] ) ){{array_get($info['invalid'],'approve_time')}} @endif</div>
                                    </div>
                                </div>
                            @endif

                            @if( !empty( $info['sale'] ) )
                                @if( $info['identity'] == 'orther' )
                                    <div class="form-group">
                                        <label class="control-label col-lg-2 title"><span class="red"><i class="fa fa-square"></i></span> 订单信息</label>
                                    </div>
                                    <div class="form-group ">
                                        <label class="control-label col-lg-2">销售单号</label>
                                        <div class="col-lg-3">
                                            <div class="form-control" >{{array_get($info['sale'],'seq')}}</div>
                                        </div>
                                    </div>
                                @elseif( $info['identity'] == 'available' )
                                    <div class="form-group">
                                        <label class="control-label col-lg-2 title"><span class="red"><i class="fa fa-square"></i></span> 订单信息</label>
                                    </div>
                                    <div class="form-group ">
                                        <label class="control-label col-lg-2">销售单号</label>
                                        <div class="col-lg-3">
                                            <div class="form-control" >{{array_get($info['sale'],'seq')}}</div>
                                        </div>
                                        <label for="name" class="control-label col-lg-1">客户类型</label>
                                        <div class="col-lg-3">
                                            @if( $info['sale']['customer_type'] == 1 )<div class="form-control" >内部部门</div>
                                            @elseif( $info['sale']['customer_type'] == 2 )<div class="form-control" >个人</div>
                                            @elseif( $info['sale']['customer_type'] == 3 )<div class="form-control" >单位</div>
                                            @endif
                                        </div>
                                    </div>
                                @if( $info['sale']['customer_type'] == 1 )
                                    <div class="form-group ">
                                        <label class="control-label col-lg-2">公司名称</label>
                                        <div class="col-lg-3">
                                            <div class="form-control" >{{array_get($info['sale']['customer_info'],'company_name')}}</div>
                                        </div>
                                        <label for="name" class="control-label col-lg-1">公司编号</label>
                                        <div class="col-lg-3">
                                            <div class="form-control" >{{array_get($info['sale']['customer_info'],'company_id')}}</div>
                                        </div>
                                    </div>

                                    <div class="form-group ">
                                        <label class="control-label col-lg-2">部门名称</label>
                                        <div class="col-lg-3">
                                            <div class="form-control" >{{array_get($info['sale']['customer_info'],'sector_name')}}</div>
                                        </div>
                                        <label for="name" class="control-label col-lg-1">部门编号</label>
                                        <div class="col-lg-3">
                                            <div class="form-control" >{{array_get($info['sale']['customer_info'],'sector_id')}}</div>
                                        </div>
                                    </div>

                                    <div class="form-group ">
                                        <label class="control-label col-lg-2">领用人</label>
                                        <div class="col-lg-3">
                                            <div class="form-control" >{{array_get($info['sale']['customer_info'],'recipients')}}</div>
                                        </div>
                                    </div>

                                    <div class="form-group ">
                                        <label class="control-label col-lg-2">费用承担部门</label>
                                        <div class="col-lg-3">
                                            @if( $info['sale']['customer_info']['is_pay'] == 1 )
                                                <div class="form-control" >是</div>
                                            @else()
                                                <div class="form-control" >否</div>
                                            @endif
                                        </div>
                                        @if( $info['sale']['customer_info']['is_pay'] == 2 )
                                            <label for="name" class="control-label col-lg-1">费用承担</label>
                                            <div class="col-lg-3">
                                                <div class="form-control" >{{array_get($info['sale']['customer_info'],'pay_sector')}}</div>
                                            </div>
                                        @endif
                                    </div>
                                @elseif( $info['sale']['customer_type'] == 2 )
                                    <div class="form-group ">
                                        <label class="control-label col-lg-2">客户名称</label>
                                        <div class="col-lg-3">
                                            <div class="form-control" >{{array_get($info['sale']['customer_info'],'name')}}</div>
                                        </div>
                                        <label for="name" class="control-label col-lg-1">联系人手机</label>
                                        <div class="col-lg-3">
                                            <div class="form-control" >{{array_get($info['sale']['customer_info'],'contact_mobile')}}</div>
                                        </div>
                                    </div>

                                    <div class="form-group ">
                                        <label class="control-label col-lg-2">联系地址</label>
                                        <div class="col-lg-3">
                                            <div class="form-control" >{{array_get($info['sale']['customer_info'],'contact_addr')}}</div>
                                        </div>
                                        <label for="name" class="control-label col-lg-1">联系人邮箱</label>
                                        <div class="col-lg-3">
                                            <div class="form-control" >{{array_get($info['sale']['customer_info'],'contact_email')}}</div>
                                        </div>
                                    </div>

                                    <div class="form-group ">
                                        <label class="control-label col-lg-2">证件类型</label>
                                        <div class="col-lg-3">
                                            @if( $info['sale']['customer_info']['certificate_type'] == 1 )
                                                <div class="form-control" >身份证</div>
                                            @elseif( $info['sale']['customer_info']['certificate_type'] == 2 )
                                                <div class="form-control" >护照</div>
                                            @elseif( $info['sale']['customer_info']['certificate_type'] == 3 )
                                                <div class="form-control" >营业执照</div>
                                            @elseif( $info['sale']['customer_info']['certificate_type'] == 4 )
                                                <div class="form-control" >机构代码证</div>
                                            @elseif( $info['sale']['customer_info']['certificate_type'] == 5 )
                                                <div class="form-control" >{{array_get($info['sale']['customer_info'],'certificate_other_type')}}</div>
                                            @else
                                                <div class="form-control" ></div>
                                            @endif
                                        </div>
                                        <label for="name" class="control-label col-lg-1">证件号码</label>
                                        <div class="col-lg-3">
                                            <div class="form-control" >{{array_get($info['sale']['customer_info'],'certificate_code')}}</div>
                                        </div>
                                    </div>

                                    <div class="form-group ">
                                        <label class="control-label col-lg-2">支付方式</label>
                                        <div class="col-lg-3">
                                            @if( $info['sale']['pay_type'] == '其他' )
                                                <div class="form-control" >{{array_get( $info['sale'], 'pay_text' )}}</div>
                                            @else
                                                <div class="form-control" >{{array_get( $info['sale'], 'pay_type' )}}</div>
                                            @endif
                                        </div>
                                    </div>
                                @elseif( $info['sale']['customer_type'] == 3 )
                                    <div class="form-group ">
                                        <label class="control-label col-lg-2">客户名称</label>
                                        <div class="col-lg-3">
                                            <div class="form-control" >{{array_get($info['sale']['customer_info'],'name')}}</div>
                                        </div>
                                        <label for="name" class="control-label col-lg-1">联络人</label>
                                        <div class="col-lg-3">
                                            <div class="form-control" >{{array_get($info['sale']['customer_info'],'contact_name')}}</div>
                                        </div>
                                    </div>

                                    <div class="form-group ">
                                        <label class="control-label col-lg-2">联系电话</label>
                                        <div class="col-lg-3">
                                            <div class="form-control" >{{array_get($info['sale']['customer_info'],'contact_tel')}}</div>
                                        </div>
                                        <label for="name" class="control-label col-lg-1">联系人手机</label>
                                        <div class="col-lg-3">
                                            <div class="form-control" >{{array_get($info['sale']['customer_info'],'contact_mobile')}}</div>
                                        </div>
                                    </div>

                                    <div class="form-group ">
                                        <label class="control-label col-lg-2">联系地址</label>
                                        <div class="col-lg-3">
                                            <div class="form-control" >{{array_get($info['sale']['customer_info'],'contact_addr')}}</div>
                                        </div>
                                        <label for="name" class="control-label col-lg-1">联系人邮箱</label>
                                        <div class="col-lg-3">
                                            <div class="form-control" >{{array_get($info['sale']['customer_info'],'contact_email')}}</div>
                                        </div>
                                    </div>

                                    <div class="form-group ">
                                        <label class="control-label col-lg-2">证件类型</label>
                                        <div class="col-lg-3">
                                            @if( $info['sale']['customer_info']['certificate_type'] == 1 )
                                                <div class="form-control" >身份证</div>
                                            @elseif( $info['sale']['customer_info']['certificate_type'] == 2 )
                                                <div class="form-control" >护照</div>
                                            @elseif( $info['sale']['customer_info']['certificate_type'] == 3 )
                                                <div class="form-control" >营业执照</div>
                                            @elseif( $info['sale']['customer_info']['certificate_type'] == 4 )
                                                <div class="form-control" >机构代码证</div>
                                            @elseif( $info['sale']['customer_info']['certificate_type'] == 5 )
                                                <div class="form-control" >{{array_get($info['sale']['customer_info'],'certificate_other_type')}}</div>
                                            @else
                                                <div class="form-control" ></div>
                                            @endif
                                        </div>
                                        <label for="name" class="control-label col-lg-1">证件号码</label>
                                        <div class="col-lg-3">
                                            <div class="form-control" >{{array_get($info['sale']['customer_info'],'certificate_code')}}</div>
                                        </div>
                                    </div>

                                    <div class="form-group ">
                                        <label class="control-label col-lg-2">支付方式</label>
                                        <div class="col-lg-3">
                                            @if( $info['sale']['pay_type'] == '其他' )
                                                <div class="form-control" >{{array_get( $info['sale'], 'pay_text' )}}</div>
                                            @else
                                                <div class="form-control" >{{array_get( $info['sale'], 'pay_type' )}}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                @endif

                                <div class="form-group ">
                                    <label class="control-label col-lg-2">备注</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['sale'],'memo')}}</div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label col-lg-2">申请人</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['sale'],'request_user_name')}}</div>
                                    </div>
                                    <label for="name" class="control-label col-lg-1">申请日期</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['sale'],'request_time')}}</div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label for="name" class="control-label col-lg-2">申请仓库</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['sale'],'storage_name')}}</div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label col-lg-2">审核人</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['sale'],'approve_user_name')}}</div>
                                    </div>
                                    <label for="name" class="control-label col-lg-1">审核日期</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >@if( !empty( $info['sale']['approve_time'] ) ){{array_get($info['sale'],'approve_time')}} @endif</div>
                                    </div>
                                </div>
                            @endif

                            @if( !empty( $info['fallback'] ) )
                                <div class="form-group">
                                    <label class="control-label col-lg-2 title"><span class="red"><i class="fa fa-square"></i></span> 退券信息</label>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label col-lg-2">退券单号</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['fallback'],'seq')}}</div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label col-lg-2">券简称</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['fallback'],'coupon_type_name')}}</div>
                                    </div>
                                    <label for="name" class="control-label col-lg-1">退券数量</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['fallback'],'fallback_amount')}}</div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label col-lg-2">开始券号</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['fallback'],'start_flow_no')}}</div>
                                    </div>
                                    <label for="name" class="control-label col-lg-1">结束券号</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['fallback'],'end_flow_no')}}</div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label col-lg-2">退券原因</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >
                                            @if( $info['fallback']['reason_type'] == 3 )
                                                {{array_get($info['fallback'],'reason_content')}}
                                            @elseif( $info['fallback']['reason_type'] == 2 )
                                                客户无条件退券
                                            @else
                                                券质量问题
                                            @endif
                                        </div>
                                    </div>
                                    <label for="name" class="control-label col-lg-1">备注</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['fallback'],'memo')}}</div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label col-lg-2">申请人</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['fallback'],'request_user_name')}}</div>
                                    </div>
                                    <label for="name" class="control-label col-lg-1">申请日期</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['fallback'],'request_time')}}</div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label col-lg-2">申请仓库</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['fallback'],'request_storage_name')}}</div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label col-lg-2">审核人</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['fallback'],'approve_user_name')}}</div>
                                    </div>
                                    <label for="name" class="control-label col-lg-1">审核日期</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >@if( !empty( $info['fallback']['approve_time'] ) ){{array_get($info['fallback'],'approve_time')}} @endif</div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label col-lg-2">审核仓库</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['fallback'],'approve_storage_name')}}</div>
                                    </div>
                                </div>
                            @endif


                            @if( !empty( $info['replace'] ) )
                                <div class="form-group">
                                    <label class="control-label col-lg-2 title"><span class="red"><i class="fa fa-square"></i></span> 换券信息</label>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label col-lg-2">换券单号</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['replace'],'seq')}}</div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label col-lg-2">旧券券号</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['replace'],'from_flow_no')}}</div>
                                    </div>
                                    <label for="name" class="control-label col-lg-1">销售仓库</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['replace'],'from_storage_name')}}</div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label col-lg-2">新券券号</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['replace'],'to_flow_no')}}</div>
                                    </div>
                                    <label for="name" class="control-label col-lg-1">所属仓库</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['replace'],'to_storage_name')}}</div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label col-lg-2">换券原因</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >
                                            @if( $info['replace']['reason'] == '其他' )
                                                {{array_get($info['replace'],'reason')}}-{{array_get($info['replace'],'text')}}
                                            @else
                                                {{array_get($info['replace'],'reason')}}
                                            @endif
                                        </div>
                                    </div>
                                    <label for="name" class="control-label col-lg-1">备注</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['replace'],'memo')}}</div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label col-lg-2">申请人</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['replace'],'request_user_name')}}</div>
                                    </div>
                                    <label for="name" class="control-label col-lg-1">申请日期</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['replace'],'request_time')}}</div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label col-lg-2">审核人</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >{{array_get($info['replace'],'approve_user_name')}}</div>
                                    </div>
                                    <label for="name" class="control-label col-lg-1">审核日期</label>
                                    <div class="col-lg-3">
                                        <div class="form-control" >@if( !empty( $info['replace']['approve_time'] ) ){{array_get($info['replace'],'approve_time')}} @endif</div>
                                    </div>
                                </div>
                            @endif

                        </form>
                    </div>
                </div>
            </section>
        </div>
    </div>
    @endif
@stop

@section('footer')
    <footer>
        2014 &copy; AdminEx by ThemeBucket
    </footer>
@stop