@extends('temp.index')

@section('style')
    <style>

    </style>
    <link href="{{ URL::asset('/css/bootstrap-treeview.css') }}" rel="stylesheet">
@stop

@section('scripts')
    <script>
        $(function () {
            function getTree() {
                // Some logic to retrieve, or generate tree structure
                var data = '{!! $storages !!}';
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
                    alert( '请选择仓库！' );
                    return false;
                }
                $("#storage_name").val(obj[0].text);
                $("#storage_id").val(obj[0].id);
                $('#myModal').modal('hide');
            })
            $("#start").change(function () {
                var start_seq = $('#start').val();
                var end_seq   = $('#end').val();
                if( start_seq == '' || end_seq == '' || start_seq < 0 || end_seq < 0 ) return false;
                if( end_seq < start_seq )
                {
                    alert('起始号不可以小于结束号');
                    return false;
                }
                var nums = end_seq - start_seq + 1;
                $( '#nums' ).val(nums);
            });
            $("#end").change(function () {
                var start_seq = $('#start').val();
                var end_seq   = $('#end').val();
                if( start_seq == '' || end_seq == '' || start_seq < 0 || end_seq < 0 ) return false;
                if( end_seq < start_seq )
                {
                    alert('起始号不可以小于结束号');
                    return false;
                }
                var nums = end_seq - start_seq + 1;
                $( '#nums' ).val(nums);
            });
        })
    </script>
    <script src="{{ URL::asset('/js/jquery.validate.min.js') }}"></script>
    <script src="{{ URL::asset('/js/bootstrap-treeview.js') }}"></script>
@stop

@section('body')
    <div class="row">
        <div class="col-sm-12">
            <section class="panel">
                <header class="panel-heading">
                    调拨申请
                </header>
                <div class="panel-body">
                    <div class="form">
                        <form class="cmxform form-horizontal adminex-form" method="post" action="/inventory/transfers/apply/save">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="id" value="{{array_get($coupon,'id')}}">

                            <div class="form-group ">
                                <label for="nickname" class="control-label col-lg-2">券简称</label>
                                <div class="col-lg-4">
                                    <div class="form-control" >{{array_get($coupon,'coupon_type_name')}}</div>
                                    <input type="hidden" name="coupon_type_name" value="{{array_get($coupon,'coupon_type_name')}}">
                                </div>
                            </div>

                            <div class="form-group ">
                                <label for="start" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 开始券号</label>
                                <div class="col-lg-4">
                                    <input class="form-control" id="start" name="start" type="number" value="{{array_get($coupon,'start')}}" autocomplete="off" required minlength="2"/>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="end" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 结束券号</label>
                                <div class="col-lg-4">
                                    <input class="form-control" id="end" name="end" type="number" value="{{array_get($coupon,'end')}}" autocomplete="off" required minlength="2"/>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="storage_id" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 接受仓库</label>
                                <div class="col-lg-2">
                                    <input type="hidden" id="storage_id" name="storage_id" value="{{array_get($coupon,'storage_id')}}">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="storage_name" name="storage_name" value="{{array_get($coupon,'storage_name')}}" readonly="readonly">
                                        <span class="input-group-btn">
                                        <button class="btn btn-default" type="button" data-toggle="modal" data-target="#myModal">选择</button>
                                    </span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label for="nums" class="control-label col-lg-2">数量</label>
                                <div class="col-lg-2">
                                    <input class="form-control" id="nums" name="nums" type="text" value="{{array_get($coupon,'nums')}}" readonly="readonly"/>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-lg-offset-2 col-lg-10">
                                    <button class="btn btn-primary" type="submit">确定</button>
                                    <button class="btn btn-default" onclick="location.href = '/inventory/transfers/apply{{$urlParam}}'" type="button">返回</button>
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
@stop

@section('footer')
    <footer>
        2014 &copy; AdminEx by ThemeBucket
    </footer>
@stop