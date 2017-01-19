@extends('temp.index')

@section('style')
    <style>

    </style>
@stop

@section('scripts')
    <script src="{{ URL::asset('/js/jquery.validate.min.js') }}"></script>
    <script src="{{ URL::asset('/js/bootstrap-treeview.js')}}"></script>
    <script>
        $(function () {
            //选择仓库
            function getTree() {
                var data = '{!! $storageList !!}';
                return data;
            }
            $('#tree').treeview({
                data: getTree(),
                levels: 2,
                selectedIcon:"glyphicon glyphicon-ok",
                collapseIcon:"glyphicon glyphicon-minus-sign",
                expandIcon:"glyphicon glyphicon-plus-sign",
                showTags:true
            });
            $("#save").click(function () {
                var obj = $('#tree').treeview('getSelected');
                if( obj == null || obj.length == 0 )
                {
                    $("#storage_name").val('');
                    $("#storage_id").val('');
                    $('#myModal').modal('hide');
                }
                else
                {
                    $("#storage_name").val(obj[0].text);
                    $("#storage_id").val(obj[0].id);
                    $('#myModal').modal('hide');
                }
            })

        });

    </script>
@stop

@section('body')
    <div class="row">
        <div class="col-md-12">
            <!--pagination start-->
            <section class="panel">
                <div class="panel-body">
                    <form class="cmxform form-horizontal adminex-form" method="get" action="/inventory/search/coupon/noactivated" >
                        <div class="form-group">
                            <label for="name" class="control-label col-lg-1">券类别</label>
                            <div class="col-lg-3">
                                <select class="form-control" name="coupon_class_id">
                                    <option value="">请选择</option>
                                    @foreach($couponClass as $key => $value)
                                        @if(!empty($search['coupon_class_id']) && $search['coupon_class_id'] == $value['id'])
                                            <option value="{{$value['id']}}" selected >{{$value['name']}}</option>
                                        @else
                                            <option value="{{$value['id']}}">{{$value['name']}}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <label for="name" class="control-label col-lg-1">券简称</label>
                            <div class="col-lg-3">
                                <select class="form-control" name="coupon_type_id">
                                    <option value="">请选择</option>
                                    @foreach($couponType as $key => $value)
                                        @if(!empty($search['coupon_type_id']) && $search['coupon_type_id'] == $value['id'])
                                            <option value="{{$value['id']}}" selected >{{$value['name']}}</option>
                                        @else
                                            <option value="{{$value['id']}}">{{$value['name']}}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <label for="name" class="control-label col-lg-1">申请仓库</label>
                            <div class="col-lg-3">
                                <input type="hidden" id="storage_id" name="storage_id" value="{{$search['storage_id']}}">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="storage_name" name="storage_name" value="{{$search['storage_name']}}" readonly="readonly">
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="button" data-toggle="modal" data-target="#myModal">选择</button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="name" class="control-label col-lg-1">开始券号</label>
                            <div class="col-lg-3">
                                <input class="form-control" type="text" value="{{$search['start_flow_no']}}" name="start_flow_no" />
                            </div>
                            <label for="name" class="control-label col-lg-1">结束券号</label>
                            <div class="col-lg-3">
                                <input class="form-control" type="text" value="{{$search['end_flow_no']}}" name="end_flow_no" />
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-lg-offset-1 col-lg-10">
                                <button class="btn btn-primary" type="submit">开始搜索</button>
                                <button class="btn btn-default" type="button" data-toggle="modal" data-target="#myModal2">库存检查</button>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
            <!--pagination end-->
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <section class="panel">
                <header class="panel-heading">
                    未激活券明细
                </header>
                <div class="panel-body">
                    <section id="flip-scroll">
                        <table class="table table-bordered table-striped table-condensed cf">
                            <thead class="cf">
                            <tr>
                                <th>券号</th>
                                <th>券类别</th>
                                <th>单价</th>
                                <th>券种简称</th>
                                <th>所在仓库</th>
                                <th>状态</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if( count($list) > 0)
                                @foreach ( $list as $key => $value )
                                    <tr>
                                        <td>{{$value->coupon_flow_no}}</td>
                                        <td>{{$value->coupon_class_name}}</td>
                                        <td>{{priceShow($value->coupon_price)}}</td>
                                        <td>{{$value->coupon_type_name}}</td>
                                        <td>{{$value->storage_name}}</td>
                                        <td>{{$status[$value->status]}}</td>
                                    </tr>
                                @endforeach
                            @endif
                            </tbody>
                        </table>
                        @if(count($list) > 0 && method_exists($list,'render'))
                            <div class="text-right">
                                {{ $list-> appends( $search )-> links() }}
                            </div>
                        @endif
                    </section>
                </div>
            </section>

        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title" id="myModalLabel">仓库选择</h4>
                </div>
                <div class="modal-body">
                    <div id="tree"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="save">确认</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="cmxform form-horizontal adminex-form" method="post" action="/inventory/search/coupon/file/inspect/storage" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title text-center">上传文件</h4>
                    </div>
                    <div class="modal-body">
                        <a href="/templat/search_coupon.csv">【模板下载】</a>
                        <br>
                        <br>
                        <input type="file" name="csvFile" value="">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">确认</button>
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