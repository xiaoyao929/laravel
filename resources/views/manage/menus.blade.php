@extends('temp.index')

@section('style')
    <style>
        .level2-box{ background: #424F63;}
        .details .table{ background: #424F63; border: none;}
    </style>

@stop

@section('scripts')
    <script>
        $(document).ready(function() {

            $(document).on('click','#hidden-table-info tbody td img',function () {
                var nTr = $(this).parents('tr')[0];
                if ($(nTr).hasClass("closed"))
                {
                    this.src = "{{ URL::asset('/images/details_close.png') }}";
                    $(nTr).removeClass("closed").addClass("open");
                    $(nTr).next("tr").removeClass("hidden");
                }
                else
                {
                    this.src = "{{ URL::asset('/images/details_open.png') }}";
                    $(nTr).removeClass("open").addClass("closed");
                    $(nTr).next("tr").addClass("hidden");
                }
            });
        });
        $(".state").click(function () {
            var id    = $(this).data('id');
            var state = $(this).data('state');
            location.href = '/manage/menu/visiable?id='+ id +'&visiable='+ state;
        })
        $(".del").click(function () {
            if( confirm("确认删除吗?"))
            {
                var id = $(this).data('id');
                location.href = '/manage/menu/del?id='+ id;
            }
            else
            {
                return false;
            }
        })
        $(".edit").click(function () {
            var id = $(this).data('id');
            location.href = '/manage/menu/edit?id='+ id;
        })
    </script>
@stop

@section('body')
    <div class="row">
        <div class="col-sm-12">
            <section class="panel">
                <header class="panel-heading">
                    菜单管理
                </header>
                <div class="panel-body">
                    <div class="adv-table">
                        <table class="display table table-bordered dataTable" id="hidden-table-info">
                            <thead>
                            <tr>
                                <th width="3%"></th>
                                <th width="5%">ID</th>
                                <th width="13%">菜单名称</th>
                                <th width="10%" class="hidden-phone">排序</th>
                                <th width="13%" class="hidden-phone">图标</th>
                                <th width="13%" class="hidden-phone">地址</th>
                                <th width="13%" class="hidden-phone">前缀</th>
                                <th width="10%" class="hidden-phone">是否显示</th>
                                <th width="20%" class="hidden-phone">操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach( $menus as $menu )
                            <tr class="level1-box closed">
                                @if( !empty($menu['menus']))
                                <td><img src="{{ URL::asset('/images/details_open.png') }}"/></td>
                                @else
                                <td></td>
                                @endif
                                <td>{{$menu['id']}}</td>
                                <td>{{$menu['name']}}</td>
                                <td class="center hidden-phone">{{$menu['sort']}}</td>
                                <td class="center hidden-phone">@if( !empty( $menu['icon'] ) )<i class="{{$menu['icon']}}"></i> {{$menu['icon']}}@endif</td>
                                <td class="center hidden-phone">{{$menu['url']}}</td>
                                <td class="center hidden-phone">{{$menu['prefix']}}</td>
                                <td class="center hidden-phone">
                                    @if( $menu['visiable'] == 1 )
                                        <span class="label label-success">显示</span>
                                    @else
                                        <span class="label label-warning">不显示</span>
                                    @endif
                                </td>
                                <td class="center hidden-phone">
                                    @if( $menu['visiable'] == 1 )
                                        <button class="btn btn-warning btn-xs state" data-id="{{$menu['id']}}" data-state="off" type="button"><span class="fa fa-pause"></span> 不显示</button>
                                    @else
                                        <button class="btn btn-success btn-xs state" data-id="{{$menu['id']}}" data-state="on" type="button"><span class="fa fa-play"></span> 显示</button>
                                    @endif
                                    <button class="btn btn-danger btn-xs del" data-id="{{$menu['id']}}" type="button"><span class="glyphicon glyphicon-remove"></span> 删除</button>
                                    <button class="btn btn-default btn-xs edit" data-id="{{$menu['id']}}" type="button"><span class="glyphicon glyphicon-pencil"></span> 编辑</button>
                                </td>
                            </tr>
                            @if( !empty($menu['menus']) )
                            <tr class="level2-box hidden">
                                <td colspan="9" class="details">
                                    <table class="table table-bordered">
                                        @foreach( $menu['menus'] as $childMenu )
                                        <tr class="level2-content">
                                            <td width="3%"></td>
                                            <td width="5%">{{$childMenu['id']}}</td>
                                            <td width="13%">{{$childMenu['name']}}</td>
                                            <td width="10%" class="center hidden-phone">{{$childMenu['sort']}}</td>
                                            <td width="13%" class="center hidden-phone">@if( !empty( $childMenu['icon'] ) )<i class="{{$childMenu['icon']}}"></i> {{$childMenu['icon']}}@endif</td>
                                            <td width="13%" class="center hidden-phone">{{$childMenu['url']}}</td>
                                            <td width="13%" class="center hidden-phone">{{$childMenu['prefix']}}</td>
                                            <td width="10%" class="center hidden-phone">
                                                @if( $childMenu['visiable'] == 1 )
                                                    <span class="label label-success">显示</span>
                                                @else
                                                    <span class="label label-warning">不显示</span>
                                                @endif
                                            </td>
                                            <td width="20%" class="center hidden-phone">
                                                @if( $childMenu['visiable'] == 1 )
                                                    <button class="btn btn-warning btn-xs state" data-id="{{$childMenu['id']}}" data-state="off" type="button"><span class="fa fa-pause"></span> 不显示</button>
                                                @else
                                                    <button class="btn btn-success btn-xs state" data-id="{{$childMenu['id']}}" data-state="on" type="button"><span class="fa fa-play"></span> 显示</button>
                                                @endif
                                                <button class="btn btn-danger btn-xs del" data-id="{{$childMenu['id']}}" type="button"><span class="glyphicon glyphicon-remove"></span> 删除</button>
                                                <button class="btn btn-default btn-xs edit" data-id="{{$childMenu['id']}}" type="button"><span class="glyphicon glyphicon-pencil"></span> 编辑</button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </table>
                                </td>
                            </tr>
                            @endif
                            @endforeach
                            </tbody>
                        </table>

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