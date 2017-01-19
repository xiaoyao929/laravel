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
@stop

@section('body')
    <div class="row">
        <div class="col-sm-12">
            <section class="panel">
                <header class="panel-heading">
                    授权管理
                </header>
                <div class="panel-body">
                    <section id="flip-scroll">
                        <form method="post" action="/role/accredit/save">

                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="role_id" value="{{$id}}">

                            <div class="form-group ">
                                <label for="name" class="control-label col-lg-1">角色名</label>
                                <div class="col-lg-4 col-xs-12 m-bot15">
                                    <input class="form-control" type="text" value="{{array_get( $role, 'name' )}}" readonly="readonly"/>
                                </div>
                            </div>

                            <table class="table table-bordered table-striped table-condensed cf">
                                <thead class="cf">
                                <tr>
                                    <th>所属菜单</th>
                                    <th>权限</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach( $permission as $k=> $value )
                                    <tr>
                                        <td><div class="checkbox"><input type="checkbox" class="checkbox_all" @if( $value['checked'] == 'on' )checked="checked"@endif data-count="{{count($value['child'])}}"> {{$value['name']}}</div></td>
                                        <td>
                                            @foreach( $value['child'] as $v )
                                                <div class="col-sm-3 checkbox"><label for="{{$v['id']}}"><input type="checkbox" class="child" @if( $v['checked'] == 'on' )checked="checked"@endif id="{{$v['id']}}" name="permission[]" value="{{$v['id']}}"> {{$v['display_name']}}</label></div>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            <div class="text-center">
                                <button class="btn btn-primary" type="submit">权限 保存</button>
                            </div>
                        </form>
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