@extends('temp.index')

@section('style')
    <style>

    </style>
    <link href="{{ URL::asset('/css/bootstrap-treeview.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('/js/bootstrap-datepicker/css/datepicker-custom.css') }}" rel="stylesheet">
@stop

@section('scripts')
    <script>
        $(function () {
            $(".ui-select").chosen();
        })
    </script>
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
        })
        $("#save").click(function () {
            var obj = $('#tree').treeview('getSelected');
            if( obj == null || obj.length == 0 )
            {
                $("#storage_name").val('');
                $("#storage_id").val('');
            }
            else
            {
                $("#storage_name").val(obj[0].text);
                $("#storage_id").val(obj[0].id);
            }
            $('#modal').modal('hide');
        })
    </script>
    <script>
        //全选
        $('#select_all').click(function (d) {
            var checked_all = $(this).prop('checked');
            if(checked_all){
                $("input[type='checkbox']").attr('checked',true);
            }else{
                $("input[type='checkbox']").attr('checked',false);
            }
        })
        //触发取消全选和全部选中
        $('.select_one').click(function (d) {
            if($(this).prop('checked')){
                $(this).attr('checked',true);
            }else{
                $(this).attr('checked',false);
            }
            var check_num = $(".select_count input[type='checkbox']:checked").length;
            //当前选中的数量是为一页，就认为全选，反过来一样
            if(check_num == $('.select_count tr').length){
                $("input[type='checkbox']").attr('checked',true);
            }else{
                $('#select_all').attr('checked',false);
            }
        });
    </script>
    <script>
        $(".show").click(function () {
            var id = $(this).data('id');
            location.href = '/invalid/audit/show?id='+ id;
        })
    </script>
    <script src="{{ URL::asset('/js/bootstrap-treeview.js') }}"></script>
    <!--   时间控件 下面都是   -->
    <script src="{{ URL::asset('/js/bootstrap-datepicker/js/bootstrap-datepicker.js')}}"></script>
    <script src="{{ URL::asset('/js/bootstrap-datetimepicker/js/bootstrap-datetimepicker.js')}}"></script>
    <script src="{{ URL::asset('/js/bootstrap-timepicker/js/bootstrap-timepicker.js')}}"></script>
    <script src="{{ URL::asset('/js/bootstrap-colorpicker/js/bootstrap-colorpicker.js')}}"></script>
    <script src="{{ URL::asset('/js/pickers-init.js')}}"></script>
@stop

@section('body')
    <div class="row">
        <div class="col-md-12">
            <!--pagination start-->
            <section class="panel">
                <div class="panel-body">
                    <form class="cmxform form-horizontal adminex-form" method="get" action="" >

                        <div class="form-group">
                            <label for="from" class="control-label col-lg-1">申请日期</label>
                            <div class="col-md-3">
                                <div class="input-group input-large custom-date-range">
                                    <input type="text" class="form-control dpd1" name="from" value="{{array_get($search,'from')}}">
                                    <span class="input-group-addon">To</span>
                                    <input type="text" class="form-control dpd2" name="to" value="{{array_get($search,'to')}}">
                                </div>
                            </div>
                            <label for="class" class="control-label col-lg-1">券类别</label>
                            <div class="col-lg-3">
                                <select class="form-control ui-select" name="class">
                                    <option value="">全部</option>
                                    @foreach( $class as $v )
                                        @if( !empty($search['class']) && $search['class'] == $v['id'] )
                                            <option value="{{$v['id']}}" selected="selected">{{$v['name']}}</option>
                                        @else
                                            <option value="{{$v['id']}}" >{{$v['name']}}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <label for="name" class="control-label col-lg-1">券简称</label>
                            <div class="col-lg-3">
                                <select class="form-control ui-select" name="name">
                                    <option value="">全部</option>
                                    @foreach( $type as $v )
                                        @if( !empty($search['name']) && $search['name'] == $v['id'] )
                                            <option value="{{$v['id']}}" selected="selected">{{$v['name']}}</option>
                                        @else
                                            <option value="{{$v['id']}}" >{{$v['name']}}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="storage_id" class="control-label col-lg-1">申请仓库</label>
                            <div class="col-lg-3">
                                <input type="hidden" id="storage_id" name="storage_id" value="{{array_get($search,'storage_id')}}">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="storage_name" name="storage_name" value="{{array_get($search,'storage_name')}}" readonly="readonly">
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="button" data-toggle="modal" data-target="#modal">选择</button>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-lg-offset-1 col-lg-10">
                                <button class="btn btn-primary" type="submit">开始搜索</button>
                            </div>
                        </div>

                    </form>
                </div>
            </section>
            <!--pagination end-->
        </div>
    </div>
    <form class="cmxform form-horizontal adminex-form" id="form" method="post" action="/invalid/audit/save" >
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="row">
            <div class="col-sm-12">
                <section class="panel">
                    <header class="panel-heading">
                        券作废审核
                    </header>
                    <div class="panel-body">
                        <section id="flip-scroll">
                            <table class="table table-bordered table-striped table-condensed cf table-hover">
                                <thead class="cf">
                                <tr>
                                    <th><input type="checkbox" id="select_all" ></th>
                                    <th>作废单号</th>
                                    <th>仓库名称</th>
                                    <th>券类别</th>
                                    <th>券种简称</th>
                                    <th>起始券号</th>
                                    <th>结束券号</th>
                                    <th>数量</th>
                                    <th>申请日期</th>
                                </tr>
                                </thead>
                                <tbody class="select_count">
                                @if( $list-> count() > 0 )
                                    @foreach( $list as $key => $value )
                                        <tr>
                                            <td><input type="checkbox" class="select_one" value="{{$value-> id}}" name="id[]"></td>
                                            <td><button class="btn btn-link data_link show" data-id="{{$value-> id}}" type="button">{{$value-> seq}}</button></td>
                                            <td>{{$value-> storage_name}}</td>
                                            <td>{{$value-> coupon_class_name}}</td>
                                            <td>{{$value-> coupon_type_name}}</td>
                                            <td>{{$value-> start_flow_no}}</td>
                                            <td>{{$value-> end_flow_no}}</td>
                                            <td>{{$value-> amount}}</td>
                                            <td>{!! date("Y-m-d",strtotime($value-> request_time)) !!}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="11" align="center" height="150">
                                            <h1>
                                                暂无数据
                                            </h1>
                                        </td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                            <div class="text-right">
                                {{ $list-> appends( $search )-> links() }}
                            </div>
                            <button class="btn btn-success" type="submit" name="action" value="pass">通过</button>
                            <button class="btn btn-danger" type="submit" name="action" value="no_pass">不通过</button>
                        </section>
                    </div>
                </section>

            </div>
        </div>
    </form>
    <!-- Modal -->
    <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
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