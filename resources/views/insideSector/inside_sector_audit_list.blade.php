@extends('temp.index')

@section('style')
    <style>

    </style>
    <link href="{{ URL::asset('/css/bootstrap-treeview.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('/js/bootstrap-datepicker/css/datepicker-custom.css') }}" rel="stylesheet">
@stop

@section('scripts')
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
            var check_num = $("input[type='checkbox']:checked").length;
            //当前选中的数量是为一页，就认为全选，反过来一样
            if(check_num == $('.select_count tr').length && $(this).prop('checked') == true){
                $("input[type='checkbox']").attr('checked',true);
            }else{
                $('#select_all').attr('checked',false);
            }
        });
        $(function () {
            //选择仓库
            function getTree() {
                var data = '{!! $select !!}';
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
        })

    </script>
    <script src="{{ URL::asset('/js/bootstrap-treeview.js')}}"></script>
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
                    <form class="cmxform form-horizontal adminex-form" method="get" action="/client/inside/sector/audit/list" >
                        <div class="form-group">
                            <label for="name" class="control-label col-lg-1">公司/部门编号</label>
                            <div class="col-lg-3">
                                <input class="form-control" type="text" value="{{$search['company_sector_id']}}" name="company_sector_id" />
                            </div>
                            <label for="name" class="control-label col-lg-1">公司/部门名称</label>
                            <div class="col-lg-3">
                                <input class="form-control" type="text" value="{{$search['company_sector_name']}}" name="company_sector_name" />
                            </div>
                            <label for="name" class="control-label col-lg-1">申请仓库</label>
                            <div class="col-lg-3">
                                <input type="hidden" id="storage_id" name="request_storage_id" value="{{$search['request_storage_id']}}">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="storage_name" name="storage_name" value="{{$search['storage_name']}}" readonly="readonly">
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="button" data-toggle="modal" data-target="#myModal">选择</button>
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
    <form class="cmxform form-horizontal adminex-form" method="post" action="/client/inside/sector/audit/save" >
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="row">
            <div class="col-sm-12">
                <section class="panel">
                    <header class="panel-heading">
                        内部信息审核
                    </header>
                    <div class="panel-body">
                        <section id="flip-scroll">
                            <table class="table table-bordered table-striped table-condensed cf table-hover">
                                <thead class="cf">
                                <tr>
                                    <th><input type="checkbox" id="select_all" ></th>
                                    <th>序号</th>
                                    <th>申请仓库</th>
                                    <th>公司编号</th>
                                    <th>公司名称</th>
                                    <th>部门编号</th>
                                    <th>部门名称</th>
                                </tr>
                                </thead>
                                <tbody class="select_count">
                                @if(count($list) > 0)
                                    @foreach($list as $key => $value)
                                        <tr>
                                            <td><input type="checkbox" class="select_one" value="{{$value->id}}" name="select_list[]"></td>
                                            <td>{{$value->seq}}</td>
                                            <td>{{$value->request_storage_name}}</td>
                                            <td>{{$value->company_id}}</td>
                                            <td>{{$value->company_name}}</td>
                                            <td>{{$value->sector_id}}</td>
                                            <td>{{$value->sector_name}}</td>
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
                            @if(count($list) > 0 && method_exists($list,'render'))
                                <div class="text-right">
                                    {{ $list-> appends( $search )-> links() }}
                                </div>
                            @endif
                        </section>
                    </div>
                    <div class="panel-body">
                        <button class="btn btn-success" type="submit" name="pass" value="1">通过</button>
                        <button class="btn btn-warning" type="submit" name="pass" value="0">不通过</button>
                    </div>
                </section>
            </div>
        </div>
    </form>
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