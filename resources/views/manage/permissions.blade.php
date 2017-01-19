@extends('temp.index')

@section('style')

@stop

@section('scripts')
    <script>
        $(".del").click(function () {
            if( confirm("确认删除吗?"))
            {
                var id = $(this).data('id');
                location.href = '/manage/permission/del?id='+ id;
            }
            else
            {
                return false;
            }
        })
        $(".edit").click(function () {
            var id = $(this).data('id');
            location.href = '/manage/permission/edit?id='+ id;
        })
    </script>
@stop

@section('body')
    <div class="row">
        <div class="col-sm-12">
            <section class="panel">
                <header class="panel-heading">
                    权限列表
                </header>
                <div class="panel-body">
                    <section id="flip-scroll">
                        <table class="table table-bordered table-striped table-condensed cf">
                            <thead class="cf">
                            <tr>
                                <th>序号</th>
                                <th>地址</th>
                                <th>名称</th>
                                <th>说明</th>
                                <th>所属菜单</th>
                                <th>创建时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(  $permissions-> count() > 0 )
                            @foreach ( $permissions as $k=> $permission )
                            <tr>
                                <td>{{$k+1}}</td>
                                <td>{{$permission-> name}}</td>
                                <td>{{$permission-> display_name}}</td>
                                <td>{{$permission-> description}}</td>
                                <td>@if( $permission-> menu_parent == 0 )其他@else{{$permission-> menu_name}}@endif</td>
                                <td>{{$permission-> created_at}}</td>
                                <td>
                                    <button class="btn btn-danger btn-xs del" data-id="{{$permission-> id}}" type="button"><span class="glyphicon glyphicon-remove"></span> 删除</button>
                                    <button class="btn btn-default btn-xs edit" data-id="{{$permission-> id}}" type="button"><span class="glyphicon glyphicon-pencil"></span> 编辑</button>
                                </td>
                            </tr>
                            @endforeach
                            @endif
                            </tbody>
                        </table>
                        <div class="text-right">
                            {{ $permissions->render() }}
                        </div>
                    </section>
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