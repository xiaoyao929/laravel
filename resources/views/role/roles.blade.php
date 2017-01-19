@extends('temp.index')

@section('style')
    <style>
        .btn-heading{padding-bottom:6px; }
    </style>
@stop

@section('scripts')
    <script>
        $(".edit").click(function () {
            var id = $(this).data('id');
            location.href = '/role/edit?id='+ id;
        })
        $(".accredit").click(function () {
            var id = $(this).data('id');
            location.href = '/role/accredit/edit?id='+ id;
        })
    </script>
@stop

@section('body')
    <div class="row">
        <div class="col-md-12">
            <!--pagination start-->
            <section class="panel">
                <div class="panel-body">
                    <form class="cmxform form-horizontal adminex-form" method="get" action="" >
                        <div class="form-group">
                            <label for="name" class="control-label col-lg-1">角色名</label>
                            <div class="col-lg-3">
                                <input type="text" class="form-control" id="name" name="name" value="{{array_get( $get, 'name' )}}">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-lg-offset-1 col-lg-10">
                                <button class="btn btn-primary" type="submit">搜索</button>
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
                    角色列表
                </header>
                <div class="panel-body">
                    <section id="flip-scroll">
                        <table class="table table-bordered table-striped table-condensed cf">
                            <thead class="cf">
                            <tr>
                                <th>组名</th>
                                <th>描述</th>
                                <th>创建时间</th>
                                <th>更新时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(  $roles-> count() > 0 )
                            @foreach ( $roles as $role )
                            <tr>
                                <td>{{$role-> name}}</td>
                                <td>{{$role-> description}}</td>
                                <td>{{$role-> created_at}}</td>
                                <td>{{$role-> updated_at}}</td>
                                <td>
                                    <button class="btn btn-default btn-xs edit" data-id="{{$role-> id}}" type="button"><span class="glyphicon glyphicon-pencil"></span> 编辑</button>
                                    <button class="btn btn-info btn-xs accredit" data-id="{{$role-> id}}" type="button"><span class="glyphicon glyphicon-pencil"></span> 授权</button>
                                </td>
                            </tr>
                            @endforeach
                            @endif
                            </tbody>
                        </table>
                        <div class="text-right">
                            {{ $roles-> appends( $get )-> links() }}
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