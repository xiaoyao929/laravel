@extends('temp.index')

@section('style')
    <style>

    </style>
    <link href="{{ URL::asset('/css/bootstrap-treeview.css') }}" rel="stylesheet">
@stop

@section('scripts')
    <script src="{{ URL::asset('/js/jquery.validate.min.js') }}"></script>
    <script src="{{ URL::asset('/js/bootstrap-treeview.js') }}"></script>
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
            $scope.select = "";
            $scope.dropdown = false;
            $scope.ishttp = true;
            $scope.search = [];
            $scope.formFn = {
                res_num:function(v){
                    var run_start = parseInt($('#run_start').val());
                    var run_end = parseInt($('#run_end').val());
                    var back_num = 0;
                    if(run_start == '' || run_end == '' || !run_start || !run_end){
                        return ;
                    }else{
                        back_num = run_end - run_start+1;
                        if(!back_num){
                            back_num = '券号错误';
                        }
                    }
                    $('#back_num').val(back_num);
                }
            }

        }]);

    </script>
@stop

@section('body')
    <div class="row" ng-app="app" ng-controller="card" ng-cloak>
        <div class="col-sm-12">
            <section class="panel">
                <header class="panel-heading">
                    退券申请
                </header>
                <div class="panel-body">
                    <div class="form">
                        <form class="cmxform form-horizontal adminex-form" method="post" action="/exchange/fallback/save" autocomplete="off">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <div class="form-group ">
                                <label for="nickname" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 券简称</label>
                                <div class="col-lg-4 col-xs-12">
                                    <select class="form-control" name="coupon_type_id">
                                        @foreach($couponType as $key => $value)
                                            <option value="{{$value['id']}}">{{$value['name']}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <label for="nickname" class="control-label col-lg-2"> 退券数量</label>
                                <div class="col-lg-4 col-xs-12">
                                    <input class="form-control" id="back_num" readonly name="back_num" type="text" value="0" autocomplete="off" required minlength="2"/>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="tel" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 开始券号</label>
                                <div class="col-lg-4 col-xs-12">
                                    <input class="form-control" id="run_start" name="start_flow_no" type="text" ng-model="v.start" ng-change="formFn.res_num(value)" value="{{array_get($rowData,'start_flow_no')}}" autocomplete="off" required minlength="2"/>
                                </div>
                                <label for="tel" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 结束券号</label>
                                <div class="col-lg-4 col-xs-12">
                                    <input class="form-control" id="run_end" name="end_flow_no" type="text" ng-model="v.end" ng-change="formFn.res_num(value)" value="{{array_get($rowData,'end_flow_no')}}" autocomplete="off" required minlength="2"/>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="tel" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 退券原因</label>
                                <div class="col-lg-4 col-xs-12">
                                    <div class="input-group" >
                                        <div class="input-group-btn">
                                            <select class="btn btn-default dropdown-toggle" name="reason_type">
                                                @foreach($reason as $key => $value)
                                                    <option value="{{$key}}">{{$value}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <input class="form-control" name="reason_content" type="text" value="{{array_get($rowData,'reason_content')}}" autocomplete="off" minlength="2"/>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="tel" class="control-label col-lg-2"> 备注</label>
                                <div class="col-lg-4 col-xs-12">
                                    <textarea class="form-control" name="memo"  value="{{array_get($rowData,'memo')}}" autocomplete="off" required minlength="2"> </textarea>
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
@stop

@section('footer')
    <footer>
        2014 &copy; AdminEx by ThemeBucket
    </footer>
@stop