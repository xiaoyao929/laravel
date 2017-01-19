@extends('temp.index')

@section('style')
    <style>
        .checkbox+.checkbox {
            margin-top: 10px;
        }
    </style>
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
        })
        $("#save").click(function () {
            var obj = $('#tree').treeview('getSelected');
            if( obj == null || obj.length == 0 )
            {
                $("#parent_name").val('');
                $("#parent_id").val('');
            }
            else
            {
                $("#parent_name").val(obj[0].text);
                $("#parent_id").val(obj[0].id);
            }

            $('#myModal').modal('hide');
        })
    </script>
    <script>
        $(".checkbox_all").change(function () {
            var id     = $(this).data('id');
            var state  = $(this).attr('checked');
            var childs = $(this).parent().parent().next().find(".child");
            if( state == 'checked' )
            {
                childs_checked( childs, 'on' );
            }
            else
            {
                childs_checked( childs, 'off' );
            }
        })
        $(".child").change(function () {
            var child_num     = $(this).parent().parent().parent().find('.child').length;
            var child_checked = $(this).parent().parent().parent().find('.child:checked').length;
            var checkbox_all  = $(this).parent().parent().parent().prev().parent().find('.checkbox_all');
            if( child_num == child_checked )
            {
                checkbox_all.attr("checked",true);
            }
            else
            {
                checkbox_all.attr("checked",false);
            }
        })
        function childs_checked ( childs, action ) {
            $.each( childs, function (){
                if( action == 'on' )
                {
                    $(this).attr("checked",true);
                }
                else
                {
                    $(this).attr("checked",false);
                }
            })
        }
    </script>
    <script src="{{ URL::asset('/js/bootstrap-treeview.js') }}"></script>
@stop

@section('body')
    <div class="row">
        <div class="col-sm-12">
            <section class="panel">
                <header class="panel-heading">
                    未销售报表下载
                </header>
                <div class="panel-body">
                    <section id="flip-scroll">
                        <form method="post" class="mxform form-horizontal adminex-form" action="/stat/no_sales/down">
                            <div class="form-group">
                                <label for="parent_id" class="control-label col-lg-1">仓库名</label>
                                <div class="col-lg-3">
                                    <input type="hidden" id="parent_id" name="parent_id" value="">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="parent_name" name="parent_name" value="" readonly="readonly">
                                        <span class="input-group-btn">
                                        <button class="btn btn-default" type="button" data-toggle="modal" data-target="#myModal">选择</button>
                                    </span>
                                    </div>
                                </div>
                                <label for="class" class="control-label col-lg-1">周期</label>
                                <div class="col-lg-3">
                                    <select class="form-control ui-select" name="cycle">
                                        @foreach( $cycle as $v )
                                            <option value="{{$v['id']}}" >{!! date( 'Y-m-d', strtotime( $v['time'] )) !!}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <table class="table table-bordered table-striped table-condensed cf">
                                <tbody>
                                    <tr>
                                        <td><div class="checkbox"><input type="checkbox" class="checkbox_all"  data-count="{{count($type)}}"> 全选</div></td>
                                        <td>
                                            @foreach( $type as $v )
                                                <div class="col-sm-3 checkbox"><label for="{{$v['id']}}"><input type="checkbox" class="child" id="{{$v['id']}}" name="type_id[]" value="{{$v['id']}}"> {{$v['name']}}</label></div>
                                            @endforeach
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="text-center">
                                <button class="btn btn-primary" type="submit">下载</button>
                            </div>
                        </form>
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
@stop

@section('footer')
@stop