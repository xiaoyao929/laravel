@extends('temp.index')

@section('style')
    <style>

    </style>
    <link href="{{ URL::asset('/css/bootstrap-treeview.css') }}" rel="stylesheet">
@stop

@section('scripts')
    <script>
        $(function () {
            $(".ui-select").chosen();
        })
    </script>
    <script>
        $(".transfers").click(function () {
            var id = $(this).data('id');
            location.href = '/inventory/transfers/apply/edit?id='+ id;
        })
    </script>
    <script src="{{ URL::asset('/js/bootstrap-treeview.js') }}"></script>
@stop

@section('body')
    <div class="row">
        <div class="col-md-12">
            <!--pagination start-->
            <section class="panel">
                <div class="panel-body">
                    <form class="cmxform form-horizontal adminex-form" method="get" action="" >
                        <div class="form-group">
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
    <form class="cmxform form-horizontal adminex-form" id="form" method="post" action="/inventory/make/audit/save" >
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="pass_select" value="">
        <input type="hidden" name="pass_text" value="">
        <input type="hidden" name="pass_msg" value="">
        <div class="row">
            <div class="col-sm-12">
                <section class="panel">
                    <header class="panel-heading">
                        调拨申请
                    </header>
                    <div class="panel-body">
                        <section id="flip-scroll">
                            <table class="table table-bordered table-striped table-condensed cf table-hover">
                                <thead class="cf">
                                <tr>
                                    <th>所属仓库</th>
                                    <th>券类别</th>
                                    <th>券种简称</th>
                                    <th>可用库存</th>
                                    <th>待审核</th>
                                    <th>调拨在途</th>
                                    <th>操作</th>
                                </tr>
                                </thead>
                                <tbody class="select_count">
                                @if( $list-> count() > 0 )
                                    @foreach( $list as $key => $value )
                                        <tr>
                                            <td>{{$value-> storage_name}}</td>
                                            <td>{{$value-> coupon_class_name}}</td>
                                            <td>{{$value-> coupon_type_name}}</td>
                                            <td>{{$value-> amount_no_sale}}</td>
                                            <td>{{$value-> amount_audit}}</td>
                                            <td>{{$value-> amount_transfers}}</td>
                                            <td>
                                                <button class="btn btn-primary btn-xs transfers" data-id="{{$value-> id}}" type="button"><span class="fa fa-truck"></span> 调拨</button>
                                            </td>
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
                        </section>
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