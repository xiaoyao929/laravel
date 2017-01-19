@extends('temp.index')

@section('style')
<style>
	.txt-cny input{position: relative;padding-left: 20px;}
	.txt-cny i{position: absolute;left: 27px;top: 50%;margin-top: -10px;width: 12px;height: 100%;font-style: normal;}
</style>
@stop

@section('scripts')
    <script src="{{ URL::asset('/js/angularjs/angular.min.js') }}"></script>
    <script type="text/javascript">
        var app = angular.module('app', []).config(["$sceProvider",function($sceProvider){
            $sceProvider.enabled(false);
        }]).config(["$httpProvider",function($httpProvider){
            $httpProvider.defaults.withCredentials = true;
            $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded';
            $httpProvider.defaults.headers.put['Content-Type'] = 'application/x-www-form-urlencoded';
            $httpProvider.defaults.headers.put['X-CSRF-Token'] = $("meta[name='csrf-token']").attr('content');
        }]);
        app.controller('card',['$scope','$timeout','$http', function($scope,$timeout,$http) {
            $("body").on("focus","input,select",function(){
                $(this).closest(".form-group").removeClass("has-error");
            });
            $scope.select = "";
            $scope.allprice = 0;
            $scope.dropdown = false;
            $scope.ishttp = true;
            $scope.search = [];
            $scope.options = {
                certificate:["","身份证","护照","营业执照","机构代码证","其他"],
                pay_type:["","现金","转账","支付宝","微信","支票","其他"]
            };
            $scope.alert = {
                show:false,
                title:"提示",
                info:"请选择券简称"
            };
            $scope.selectopt = {!! $type !!};
            $.each($scope.selectopt,function(i,n){
                n["ischoose"] = false;
            });
            $scope.form = {
                custom:"",
                internal:{
                    text_name:"",
                    is_pay:1,
                    id:"",
                    pay_sector:"",
                    company_name:"",
                    sector_name:"",
                    company_id:"",
                    sector_id:"",
                    recipients:"",
                    memo:""
                },
                client:{
                    text_name:"",
                    name:"",
                    id:"",
                    contact_mobile:"",
                    contact_addr:"",
                    contact_email:"",
                    pincodes:"",
                    certificate_type:"",
                    certificate_other_type:"",
                    certificate_code:"",
                    pay_text:"",
                    pay_type:1,
                    memo:""
                },
                company:{
                    text_name:"",
                    name:"",
                    id:"",
                    contact_name:"",
                    contact_mobile:"",
                    contact_tel:"",
                    contact_addr:"",
                    contact_email:"",
                    pincodes:"",
                    certificate_type:"",
                    certificate_other_type:"",
                    certificate_code:"",
                    pay_text:"",
                    pay_type:1,
                    memo:""
                },
                product:[],

                //add at 2016-12-28 增加总计和实收字段
                capital:{
                	total:0,
                	collection_price:""
                }
            };
            $scope.save = function(){
                $scope.allprice = 0;
                $.each($scope.form.product,function(i,n){
                    if(n.allprice!=''&&n.allprice){
                        $scope.allprice += n.allprice*1;
                    };
                });

//              if($scope.allprice>=5000){
//                  if($scope.form.company.certificate_type==""){
//                      $scope.form.company.certificate_type = 1;
//                  };
//                  if($scope.form.client.certificate_type==""){
//                      $scope.form.client.certificate_type = 1;
//                  };
//              };

                //modify at 2016-12-28  单位5000，个人50000
                if($scope.allprice>=5000 && $scope.form.company.certificate_type==""){
                    $scope.form.client.certificate_type = 1;
                };

                if($scope.allprice>=50000 && $scope.form.client.certificate_type==""){
                    $scope.form.client.certificate_type = 1;
                };


                $timeout(function(){
                    var isup = false;
                    var isupmsg = "";
                    $("input,select").each(function(){
                        if($(this).attr("required")){
                            if($(this).val()==""){
                                $(this).closest(".form-group").addClass("has-error");
                                isup = true;
                                isupmsg = $(this).closest(".form-group").find(".control-label").text();
                                return false;
                            };
                        }
                    });
                    if(isup){
                        $scope.alert = {
                            show:true,
                            title:"提示",
                            info:"信息出错<br>"+isupmsg+"不能为空"
                        };
                        return false;
                    };
                    if(!$scope.ishttp){return false;}
                    $scope.ishttp = false;
                    $timeout(function(){
                        $scope.ishttp = true;
                    },3000);
                    $.each($scope.form.product,function(i,n){
                        $scope.formFn.price(n);
                    });
                    //组装数据
                    var olddata = angular.copy($scope.form);
                    var data = olddata[olddata.custom];
                    var index1 = Number(data.certificate_type);
                    var index2 = Number(data.pay_type);
                        data["customer_type"] = olddata.custom;
                        data["product"] = $scope.form.product;
                        data.is_pay = data.is_pay ? 1 : 0;
                        data.certificate_type = $scope.options.certificate[index1];
                        data.pay_type = $scope.options.pay_type[index2];
                        data.collection_price = $scope.form.capital.collection_price;
                    if(data.product.length==0){
                        $scope.alert = {
                            show:true,
                            title:"提示",
                            info:"请加入券种信息"
                        };
                        $scope.ishttp = true;
                        return false;
                    }else{
                        var isup = false;
                        var isupmsg = "";
                        $(data.product,function(i,n){
                            if(n.discount==""||n.discount<1|| n.discount.toString().indexOf(".")>=0){
                                isup = true;
                                isupmsg = n.name+"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;折扣率必须是不小于1的正整数";
                                return false;
                            };
                            if(n.allnum==""||n.allnum<=0){
                                isup = true;
                                isupmsg = n.name+"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;券数量不能小于1";
                                return false;
                            };
                        });
                        if(isup){
                            $scope.alert = {
                                show:true,
                                title:"提示",
                                info:"信息出错<br>"+isupmsg
                            };
                            $scope.ishttp = true;
                            return false;
                        };
                    };
                    $scope.alert = {
                        show:true,
                        title:"订单信息",
                        info:"10000",
                        ok:function(){
                            $http.post("/sale/apply/save",data).success(function(data){
                                $scope.ishttp = true;
                                if(data.code==0){
                                    $scope.alert = {
                                        show:true,
                                        title:"提示",
                                        info:"提交成功",
                                        ok:function(){
                                        	window.location.href = location.href;
                                        }
                                    };
                                }else{
                                    $scope.alert = {
                                        show:true,
                                        title:"提示",
                                        info:data.msg || "异常错误"
                                    };
                                }
                            }).error(function(){
                                $scope.alert = {
                                    show:true,
                                    title:"提示",
                                    info:"提交失败"
                                };
                                $scope.ishttp = true;
                            });
                        }
                    };
                },500);
            };
            $scope.formFn = {
                add:function(){
                    if(!$scope.select){
                        $scope.alert = {
                            show:true,
                            title:"提示",
                            info:"请选择券简称"
                        };
                        return false;
                    };
                    var copy;
                    $.each($scope.selectopt,function(i,n){
                        if(n.id==$scope.select){
                            n.ischoose = true;
                            copy=angular.copy(n);
                        };
                    });
                    copy["discount"]="100";  //modify at 2016-12-28移除折扣率，默认100%
                    copy["allprice"]="";
                    copy["allnum"]="";
                    copy["product"] = [{
                        start:"",
                        end:"",
                        num:""
                    }];
                    $scope.select = "";
                    $scope.form.product.push(copy);
                },
                del:function(index){
                    $scope.alert = {
                        show:true,
                        title:"提示",
                        info:"确定要删除么？",
                        ok:function(){
                            var id = $scope.form.product[index].id;
                            $.each($scope.selectopt,function(i,n){
                                if(n.id==id){
                                    n.ischoose = false;
                                };
                            });
                            $scope.form.product.splice(index,1);
                            $scope.formFn.addUpTotal(); //add at 2016-12-28
                        }
                    };
                },
                addcard:function(v){
                    if(v.product.length>=30){
                        $scope.alert = {
                            show:true,
                            title:"提示",
                            info:"最多增加30条"
                        };
                        return false;
                    };
                    v.product.push({
                        begin:"",
                        end:"",
                        num:""
                    });
                },
                delcard:function(v,index){
                    if(v.product.length>1){
                        v.product.splice(index,1);
                    };
                },
                search:function(v){
                    $scope.search = [];
                    var key_word = $scope.form[$scope.form.custom].text_name;
                    if(key_word==""){return false;};
                    $scope.dropdown=true;
                    if(!$scope.ishttp){return false;}
                    $scope.ishttp = false;
                    $http.get("/api/client/info?model="+$scope.form.custom+"&key_word="+key_word).success(function(data){
                        $scope.search = data.data;
                        $scope.ishttp = true;
                    }).error(function(){
                        $scope.search = [];
                        $scope.ishttp = true;
                    });
                },
                name:function(v){
                    $scope.dropdown=false;
                    if(!v){return false;}
                    var copy = angular.copy(v);
                    $scope.form[$scope.form.custom] = $.extend({},$scope.form[$scope.form.custom],copy);
                    $scope.form[$scope.form.custom].customer_id = copy.id;
                    $scope.form[$scope.form.custom].text_name = copy.company_name ? copy.company_name : copy.name;
                    if($scope.form[$scope.form.custom].certificate_type||$scope.form[$scope.form.custom].certificate_type===0){
                        $scope.form[$scope.form.custom].hascertificate_type = true;
                    }else{
                        $scope.form[$scope.form.custom].hascertificate_type = false;
                    };
                    if($scope.form[$scope.form.custom].certificate_code!=''){
                        $scope.form[$scope.form.custom].hascertificate_code = true;
                    }else{
                        $scope.form[$scope.form.custom].hascertificate_code = false;
                    };
                    console.log($scope.form[$scope.form.custom].hascertificate_code)
                },
                price:function(v){
                    v["error"] = "";
                    var allnum = 0;
                    var discount = tonumber(v.discount);
                    var price = tonumber(v.price);
                    $.each(v.product,function(i,n){
                        var start = tonumber(n.start);
                        var end = tonumber(n.end);
                        var num = (end - start+1)*1;
                        if(num<0 || !num){
                            if(num===0){
                                allnum += num;
                                n.num = num;
                            }else{
                                n.num = "券号出错";
                                v["error"] = '有券号出错';
                            };
                        }else{
                            allnum += num;
                            n.num = num;
                        };
                    });
                    v.allnum = allnum;
                    if(!price){return false;}
                    if(v.discount!=""){
                        if(discount<1 || discount.toString().indexOf(".")>=0){
                            v["error"] = '折扣率必须是不小于1的正整数';
                            v.allprice = "";
                        }else{

                            v.allprice = allnum*price*discount/100;

                        }
                    }else{
                        v["error"] = '请填写折扣率';
                        v.allprice = "";
                    };
                    $scope.formFn.addUpTotal(); // add at 2016-12-28
                },
                import:function(){
                    $scope.alert = {
                        show:true,
                        title:"批量导入",
                        info:'<p class="text-left">批量导入您已退回的券信息，请使用模板文件导入。<a href="/sale/apply/batch/temp">下载模板文件</a><br>注意：<br>1. 每次导入数据限1000条以内<br>2. 如券未销售登记或不可销售，则该行数据不会被导入</p><hr><form role="form" id="fileForm" method="post" enctype="multipart/form-data" action="/sale/apply/batch/save"><input type="hidden" name="_token" value="{{ csrf_token() }}"><div class="form-group text-left"><label for="file2">选择文件</label><input type="file" name="file2" id="file2"></div></form>',
                        ok:function(){
                            if($("#file2").val()){
                                $("#fileForm").submit();
                            }else{
                                $scope.alert.show = false;
                            };
                        }
                    };
                },
                //add at 2016-12-28 增加总计金额
                addUpTotal:function(){

                    $scope.form.capital.total = 0;
                    $.each($scope.form.product,function(i,n){
                    	if(n.allprice!=''&&n.allprice){
                    		console.log($scope.form.capital.total,tonumber(n.allprice));
                    		$scope.form.capital.total += tonumber(n.allprice);
                    	}
                    })
                }
            }
        }]);
        function tonumber(num){
            if(num == undefined ){
                num = 0;
                return num;
            }else{
                return Number(parseFloat(num.toString().replace(/[^0-9,.]/g,"").replace(/\,/g,"")).toFixed(4));
            }
        }
    </script>
@stop

@section('body')
    <div class="row" ng-app="app" ng-controller="card" ng-cloak>
        <div class="col-md-12 col-xs-12 col-sm-12">
            <section class="panel">
                <form class="form-horizontal" action="#">
                    <header class="panel-heading">
                        客户信息
                        <span role="button" href="javascript:void(0)" class="btn btn-default" ng-click="formFn.import();"><i class="fa fa-download" href="javascript:;"></i>导入数据</span>
                    </header>
                    <div class="panel-body">
                        <div class="form-group">
                            <label class="control-label col-md-2">客户类型</label>
                            <div class="col-md-4">
                                <select class="form-control" ng-model="form.custom" required>
                                    <option value="">请选择</option>
                                    <option value="internal">内部领用</option>
                                    <option value="client">个人</option>
                                    <option value="company">单位</option>
                                </select>
                            </div>
                        </div>
                        <div ng-if="form.custom==='internal'">
                            <div class="form-group">
                                <label class="control-label col-md-2">部门名称/编号</label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" ng-click="formFn.search();" ng-keyup="formFn.search();" ng-model="form.internal.text_name" required>
                                    <ul class="dropdown-menu pull-left" ng-if="form.internal.text_name && dropdown" style="display:block;left:15px; right:15px; max-height:200px;overflow-y:auto;">
                                        <li ng-repeat="value in search"><a href="javascript:void(0)" ng-click="formFn.name(value);" ng-bind="value.sector_id+'-'+value.sector_name"></a></li>
                                        <li ng-if="search.length==0"><a href="javascript:void(0)" ng-click="formFn.name();">未查询到数据</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-2">费用承担部门</label>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <div class="input-group-btn">
                                            <button class="btn" ng-class="{'btn-primary':form.internal.is_pay==1,'btn-default':form.internal.is_pay==2}" type="button" ng-click="form.internal.is_pay=1">是</button>
                                            <button class="btn" ng-class="{'btn-primary':form.internal.is_pay==2,'btn-default':form.internal.is_pay==1}" type="button" ng-click="form.internal.is_pay=2">否</button>
                                        </div>
                                        <input type="text" class="form-control" ng-model="form.internal.pay_sector" placeholder="填写承担部门" ng-if="form.internal.is_pay==2" ng-required="form.internal.is_pay==2">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-2">公司名称</label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" ng-model="form.internal.company_name" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-2">部门名称</label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" ng-model="form.internal.sector_name" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-2">公司编号</label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" ng-model="form.internal.company_id" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-2">部门编号</label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" ng-model="form.internal.sector_id" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-2">*领用人</label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" ng-model="form.internal.recipients" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-2">备注</label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" ng-model="form.internal.memo">
                                </div>
                            </div>
                            <!-- modify at 2016-12-28 增加总计、实收-->
                            <div class="form-group">
                                <label class="control-label col-md-2">总计</label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" ng-value ="form.capital.total| currency:'&yen;':2 " disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-2">实收</label>
                                <div class="col-md-4 txt-cny">
                                     <input type="text" class="form-control" ng-model="form.capital.collection_price" required>
                                     <i>¥</i>
                                </div>
                            </div>
                        </div>
                        <div ng-if="form.custom==='client'">
                            <div class="form-group">
                                <label class="control-label col-md-2">客户名称</label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" ng-click="formFn.search();" ng-keyup="formFn.search();" ng-model="form.client.text_name" required>
                                    <ul class="dropdown-menu pull-left" ng-if="form.client.text_name && dropdown" style="display:block;left:15px; right:15px; max-height:200px;overflow-y:auto;">
                                        <li ng-repeat="value in search"><a href="javascript:void(0)" ng-click="formFn.name(value);" ng-bind="value.name+'-'+value.contact_mobile"></a></li>
                                        <li ng-if="search.length==0"><a href="javascript:void(0)" ng-click="formFn.name();">未查询到数据</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-2">客户名称</label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" ng-model="form.client.name" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-2">联系人手机</label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" ng-model="form.client.contact_mobile" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-2">联系地址</label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" ng-model="form.client.contact_addr" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-2">联系人邮箱</label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" ng-model="form.client.contact_email" disabled>
                                </div>
                            </div>
                            <div class="form-group" ng-if="form.client.hascertificate_type">
                                <label class="control-label col-md-2">证件类型</label>
                                <div class="col-md-4">
                                    <button data-toggle="dropdown" type="button" class="btn btn-default dropdown-toggle disabled">
                                        <span ng-if="form.client.certificate_type==1">身份证</span>
                                        <span ng-if="form.client.certificate_type==2">护照</span>
                                        <span ng-if="form.client.certificate_type==3">营业执照</span>
                                        <span ng-if="form.client.certificate_type==4">机构代码证</span>
                                        <span ng-if="form.client.certificate_type==5">其他</span>
                                        <span class="caret"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="form-group" ng-if="form.client.hascertificate_code">
                                <label class="control-label col-md-2">证件号码</label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" ng-model="form.client.certificate_code" disabled>
                                </div>
                            </div>
                            <div class="form-group" ng-if="!form.client.hascertificate_type">
                                <label class="control-label col-md-2">证件类型</label>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <div class="input-group-btn">
                                            <button data-toggle="dropdown" type="button" class="btn btn-default dropdown-toggle">
                                                <span ng-if="form.client.certificate_type==''">请选择</span>
                                                <span ng-if="form.client.certificate_type==1">身份证</span>
                                                <span ng-if="form.client.certificate_type==2">护照</span>
                                                <span ng-if="form.client.certificate_type==3">营业执照</span>
                                                <span ng-if="form.client.certificate_type==4">机构代码证</span>
                                                <span ng-if="form.client.certificate_type==5">其他</span>
                                                <span class="caret"></span>
                                            </button>
                                            <ul role="menu" class="dropdown-menu">
                                                <li><a href="javascript:void(0)" ng-click="form.client.certificate_type=1">身份证</a></li>
                                                <li><a href="javascript:void(0)" ng-click="form.client.certificate_type=2">护照</a></li>
                                                <li><a href="javascript:void(0)" ng-click="form.client.certificate_type=3">营业执照</a></li>
                                                <li><a href="javascript:void(0)" ng-click="form.client.certificate_type=4">机构代码证</a></li>
                                                <li class="divider"></li>
                                                <li><a href="javascript:void(0)" ng-click="form.client.certificate_type=5">其他</a></li>
                                            </ul>
                                        </div>
                                        <input type="text" class="form-control" ng-model="form.client.certificate_other_type" placeholder="填写证件类型" ng-if="form.client.certificate_type==5" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group" ng-if="!form.client.hascertificate_code">
                                <label class="control-label col-md-2">证件号码</label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" ng-model="form.client.certificate_code"  ng-required="allprice>=50000">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-2">支付方式</label>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <div class="input-group-btn">
                                            <button data-toggle="dropdown" type="button" class="btn btn-default dropdown-toggle">
                                                <span ng-if="form.client.pay_type==''">请选择</span>
                                                <span ng-if="form.client.pay_type==1">现金</span>
                                                <span ng-if="form.client.pay_type==2">转账</span>
                                                <span ng-if="form.client.pay_type==3">支付宝</span>
                                                <span ng-if="form.client.pay_type==4">微信</span>
                                                <span ng-if="form.client.pay_type==5">支票</span>
                                                <span ng-if="form.client.pay_type==6">其他</span>
                                                <span class="caret"></span>
                                            </button>
                                            <ul role="menu" class="dropdown-menu">
                                                <li><a href="javascript:void(0)" ng-click="form.client.pay_type=1">现金</a></li>
                                                <li><a href="javascript:void(0)" ng-click="form.client.pay_type=2">转账</a></li>
                                                <li><a href="javascript:void(0)" ng-click="form.client.pay_type=3">支付宝</a></li>
                                                <li><a href="javascript:void(0)" ng-click="form.client.pay_type=4">微信</a></li>
                                                <li><a href="javascript:void(0)" ng-click="form.client.pay_type=5">支票</a></li>
                                                <li class="divider"></li>
                                                <li><a href="javascript:void(0)" ng-click="form.client.pay_type=6">其他</a></li>
                                            </ul>
                                        </div>
                                        <input type="text" class="form-control" ng-model="form.client.pay_text" placeholder="填写支付方式" ng-if="form.client.pay_type==6" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-2">备注</label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" ng-model="form.client.memo">
                                </div>
                            </div>
                           <!-- modify at 2016-12-28 增加总计、实收-->
                            <div class="form-group">
                                <label class="control-label col-md-2">总计</label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" ng-value ="form.capital.total| currency:'&yen;':2 " disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-2">实收</label>
                                <div class="col-md-4 txt-cny">
                                     <input type="text" class="form-control" ng-model="form.capital.collection_price" required>
                                     <i>¥</i>
                                </div>
                            </div>
                        </div>
                        <div ng-if="form.custom==='company'">
                            <div class="form-group">
                                <label class="control-label col-md-2">客户名称</label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" ng-click="formFn.search();" ng-keyup="formFn.search();" ng-model="form.company.text_name" required>
                                    <ul class="dropdown-menu pull-left" ng-if="form.company.text_name && dropdown" style="display:block;left:15px; right:15px; max-height:200px;overflow-y:auto;">
                                        <li ng-repeat="value in search"><a href="javascript:void(0)" ng-click="formFn.name(value);" ng-bind="value.name+'-'+value.contact_name+'-'+value.contact_mobile"></a></li>
                                        <li ng-if="search.length==0"><a href="javascript:void(0)" ng-click="formFn.name();">未查询到数据</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-2">客户名称</label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" ng-model="form.company.name" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-2">联系人</label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" ng-model="form.company.contact_name" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-2">联系电话</label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" ng-model="form.company.contact_tel" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-2">联系人手机</label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" ng-model="form.company.contact_mobile" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-2">联系地址</label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" ng-model="form.company.contact_addr" disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-2">联系人邮箱</label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" ng-model="form.company.contact_email" disabled>
                                </div>
                            </div>
                            <div class="form-group" ng-if="form.company.hascertificate_type">
                                <label class="control-label col-md-2">证件类型</label>
                                <div class="col-md-4">
                                    <button data-toggle="dropdown" type="button" class="btn btn-default dropdown-toggle disabled">
                                        <span ng-if="form.company.certificate_type==1">身份证</span>
                                        <span ng-if="form.company.certificate_type==2">护照</span>
                                        <span ng-if="form.company.certificate_type==3">营业执照</span>
                                        <span ng-if="form.company.certificate_type==4">机构代码证</span>
                                        <span ng-if="form.company.certificate_type==5">其他</span>
                                        <span class="caret"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="form-group" ng-if="form.company.hascertificate_code">
                                <label class="control-label col-md-2">证件号码</label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" ng-model="form.company.certificate_code" disabled>
                                </div>
                            </div>
                            <div class="form-group" ng-if="!form.company.hascertificate_type">
                                <label class="control-label col-md-2">证件类型</label>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <div class="input-group-btn">
                                            <button data-toggle="dropdown" type="button" class="btn btn-default dropdown-toggle">
                                                <span ng-if="form.company.certificate_type==''">请选择</span>
                                                <span ng-if="form.company.certificate_type==1">身份证</span>
                                                <span ng-if="form.company.certificate_type==2">护照</span>
                                                <span ng-if="form.company.certificate_type==3">营业执照</span>
                                                <span ng-if="form.company.certificate_type==4">机构代码证</span>
                                                <span ng-if="form.company.certificate_type==5">其他</span>
                                                <span class="caret"></span>
                                            </button>
                                            <ul role="menu" class="dropdown-menu">
                                                <li><a href="javascript:void(0)" ng-click="form.company.certificate_type=1">身份证</a></li>
                                                <li><a href="javascript:void(0)" ng-click="form.company.certificate_type=2">护照</a></li>
                                                <li><a href="javascript:void(0)" ng-click="form.company.certificate_type=3">营业执照</a></li>
                                                <li><a href="javascript:void(0)" ng-click="form.company.certificate_type=4">机构代码证</a></li>
                                                <li class="divider"></li>
                                                <li><a href="javascript:void(0)" ng-click="form.company.certificate_type=5">其他</a></li>
                                            </ul>
                                        </div>
                                        <input type="text" class="form-control" ng-model="form.company.certificate_other_type" placeholder="填写证件类型" ng-if="form.company.certificate_type==5" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group" ng-if="!form.company.hascertificate_code">
                                <label class="control-label col-md-2">证件号码</label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" ng-model="form.company.certificate_code" ng-required="allprice>=5000">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-2">支付方式</label>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <div class="input-group-btn">
                                            <button data-toggle="dropdown" type="button" class="btn btn-default dropdown-toggle">
                                                <span ng-if="form.company.pay_type==''">请选择</span>
                                                <span ng-if="form.company.pay_type==1">现金</span>
                                                <span ng-if="form.company.pay_type==2">转账</span>
                                                <span ng-if="form.company.pay_type==3">支付宝</span>
                                                <span ng-if="form.company.pay_type==4">微信</span>
                                                <span ng-if="form.company.pay_type==5">支票</span>
                                                <span ng-if="form.company.pay_type==6">其他</span>
                                                <span class="caret"></span>
                                            </button>
                                            <ul role="menu" class="dropdown-menu">
                                                <li><a href="javascript:void(0)" ng-click="form.company.pay_type=1">现金</a></li>
                                                <li><a href="javascript:void(0)" ng-click="form.company.pay_type=2">转账</a></li>
                                                <li><a href="javascript:void(0)" ng-click="form.company.pay_type=3">支付宝</a></li>
                                                <li><a href="javascript:void(0)" ng-click="form.company.pay_type=4">微信</a></li>
                                                <li><a href="javascript:void(0)" ng-click="form.company.pay_type=5">支票</a></li>
                                                <li class="divider"></li>
                                                <li><a href="javascript:void(0)" ng-click="form.company.pay_type=6">其他</a></li>
                                            </ul>
                                        </div>
                                        <input type="text" class="form-control" ng-model="form.company.pay_text" placeholder="填写支付方式" ng-if="form.company.pay_type==6" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-2">备注</label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" ng-model="form.company.memo">
                                </div>
                            </div>
                           <!-- modify at 2016-12-28 增加总计、实收-->
                            <div class="form-group">
                                <label class="control-label col-md-2">总计</label>
                                <div class="col-md-4">
                                    <input type="text" class="form-control" ng-value ="form.capital.total| currency:'&yen;':2 " disabled>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-2">实收</label>
                                <div class="col-md-4 txt-cny">
                                    <input type="text" class="form-control" ng-model="form.capital.collection_price" required>
                                    <i>¥</i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <header class="panel-heading">券种信息</header>
                    <div class="panel-body">
                        <div class="form-group">
                            <label class="control-label col-md-2">券简称</label>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <select class="form-control" ng-model="select">
                                        <option value="">请选择</option>
                                        <option ng-repeat="value in selectopt" ng-if="!value.ischoose" ng-value="value.id" ng-bind="value.name"></option>
                                        <option ng-if="selectopt.length==0">未查询到数据</option>
                                    </select>
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="button" ng-click="formFn.add()">添加</button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group" ng-repeat="value in form.product">
                            <div class="col-md-12">
                                <header class="panel-heading gray">券简称：<span><span ng-bind="value.name"></span></span>&nbsp;&nbsp;&nbsp;&nbsp;券类别：<span ng-bind="value.class_name"></span>&nbsp;&nbsp;&nbsp;&nbsp;<span ng-bind="value.error" style="font-size:12px; color:#FF6C60;font-weight:normal;"></span>
                                    <span class="tools pull-right">
                                        <a class="fa fa-times" href="javascript:;" ng-click="formFn.del( $index );"></a>
                                    </span>
                                </header>
                                <div class="panel-body bordered">
                                    <div class="form-group" ng-if="value.class_id==1">
                                        <label class="control-label col-md-1">单价</label>
                                        <div class="col-md-2">
                                            <input type="text" class="form-control" ng-value="value.price | currency:'&yen;':2" disabled>
                                        </div>
                                        <!-- modify at 2016-12-28 移除折扣率-->
                                        <!--<label class="control-label col-md-1">折扣率</label>
                                        <div class="col-md-2">
                                            <input type="number" class="form-control" ng-model="value.discount" maxlength="3" ng-change="formFn.price(value)" placeholder="1(免费)-100(全价)"><span style="position:absolute;right:20px;top:50%;margin-top:-10px;">%</span>
                                        </div>-->
                                        <label class="control-label col-md-1">总金额</label>
                                        <div class="col-md-2">
                                            <input type="text" class="form-control" ng-value="value.allprice | currency:'&yen;':2" disabled>
                                        </div>
                                        <label class="control-label col-md-1">券总数</label>
                                        <div class="col-md-2">
                                            <input type="text" class="form-control" style="width:80%;display:inline-block" ng-model="value.allnum" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group" ng-if="value.class_id!=1">
                                        <label class="control-label col-md-1">券总数</label>
                                        <div class="col-md-2">
                                            <input type="text" class="form-control" style="width:80%;display:inline-block" ng-model="value.allnum" disabled>
                                        </div>
                                    </div>
                                    <div ng-repeat="v in value.product">
                                        <div class="form-group">
                                            <label class="control-label col-md-1">开始券号</label>
                                            <div class="col-md-2">
                                                <input type="text" class="form-control" ng-model="v.start" ng-change="formFn.price(value)">
                                            </div>
                                            <label class="control-label col-md-1">结束券号</label>
                                            <div class="col-md-2">
                                                <input type="text" class="form-control" ng-model="v.end" ng-change="formFn.price(value)">
                                            </div>
                                            <label class="control-label col-md-1">券数量</label>
                                            <div class="col-md-2">
                                                <input type="text" class="form-control" ng-model="v.num" disabled>
                                            </div>
                                            <div class="col-md-3">
                                                <a role="button" href="javascript:void(0)" class="btn btn-primary" ng-click="formFn.addcard(value);formFn.price(value)">增加</a>
                                                <a role="button" href="javascript:void(0)" class="btn btn-default" ng-click="formFn.delcard(value, $index );formFn.price(value)" ng-if="value.product.length>1">删除</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body">
                        <label class="control-label col-md-2"></label>
                        <div class="input-group">
                            <a role="button" href="javascript:void(0)" class="btn btn-primary" ng-click="save()">确定提交</a>
                        </div>
                    </div>
                </form>
            </section>
        </div>
        <div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal3" class="modal fade in" ng-if="alert.show" ng-class="{show:alert.show}" ng-click="alert.show=false;$event.stopPropagation();">
            <div class="modal-dialog" ng-click="$event.stopPropagation();">
                <div class="modal-content">
                    <div class="modal-header">
                        <button aria-hidden="true" class="close" type="button" ng-click="alert.show=false">×</button>
                        <h4 class="modal-title text-center" ng-bind="alert.title"></h4>
                    </div>
                    <div class="modal-body text-center" ng-bind-html="alert.info" ng-if="alert.info!='10000'"></div>
                    <div class="modal-body" ng-if="alert.info=='10000'">
                        <div class="row" ng-repeat="value in form.product" style="margin-bottom:15px;">
                            <div class="col-md-12">
                                <header class="panel-heading gray">券简称：<span><span ng-bind="value.name"></span></span>&nbsp;&nbsp;&nbsp;&nbsp;券类别：<span ng-bind="value.class_name"></span>&nbsp;&nbsp;&nbsp;&nbsp;<span ng-if="value.class_id==1" ng-bind="'单价：'+(value.price | currency:'&yen;':2)"></span>
                                </header>
                                <div class="panel-body bordered">
                                    <div class="col-md-12 text-center" ng-bind="'订单信息不完全，请前去修改：'+value.error" style="font-size:12px; color:#FF6C60;font-weight:normal;margin-bottom:15px;" ng-if="value.error"></div>
                                    <div ng-if="value.class_id==1">
                                        <div class="col-md-6">
                                            <ul class="p-info">
                                                <li>
                                                    <div class="title">券总数</div>
                                                    <div class="desk" ng-bind="value.allnum"></div>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <ul class="p-info">
                                                <li>
                                                    <div class="title">应付总价</div>
                                                    <div class="desk" ng-bind="value.price*value.allnum | currency:'&yen;':2"></div>
                                                </li>
                                                <!--<li>
                                                    <div class="title">折扣率</div>
                                                    <div class="desk" ng-bind="value.discount+'%'"></div>
                                                </li>
                                                <li>
                                                    <div class="title">折后总价</div>
                                                    <div class="desk" ng-bind="value.allprice | currency:'&yen;':2"></div>
                                                </li>-->
                                            </ul>
                                        </div>
                                    </div>
                                    <div ng-if="value.class_id!=1">
                                        <div class="col-md-6">
                                            <ul class="p-info">
                                                <li>
                                                    <div class="title">券总数</div>
                                                    <div class="desk" ng-bind="value.allnum"></div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
						<div class="bordered panel-body bg-warning">
	                        <div class="col-md-6">
	                            <ul class="p-info">
	                                <li>
	                                    <div class="title">总计</div>
	                                    <div class="desk" ng-bind="form.capital.total | currency:'&yen;':2"></div>
	                                </li>
	                            </ul>
	                        </div>
	                        <div class="col-md-6">
	                            <ul class="p-info">
	                                <li>
	                                    <div class="title">实收</div>
	                                    <div class="desk" ng-bind="form.capital.collection_price | currency:'&yen;':2"></div>
	                                </li>
	                            </ul>
	                        </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" ng-if="!alert.ok" ng-click="alert.show=false">确定</button>
                        <button type="button" class="btn btn-danger" ng-if="alert.ok" ng-click="alert.show=false;alert.ok()">确定</button>
                        <button type="button" class="btn btn-defulat" ng-click="alert.show=false">关闭</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade in hide" ng-if="alert.show" ng-class="{show:alert.show}"></div>
    </div>
@stop

@section('footer')
    <footer>
        2014 &copy; AdminEx by ThemeBucket
    </footer>
@stop