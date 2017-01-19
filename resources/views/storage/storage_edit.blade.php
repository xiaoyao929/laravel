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
        })
        $("#save").click(function () {
            var obj = $('#tree').treeview('getSelected');
            if( obj == null || obj.length == 0 )
            {
                alert( '请选择仓库！' );
                return false;
            }
            $("#parent_name").val(obj[0].text);
            $("#parent_id").val(obj[0].id);
            $('#myModal').modal('hide');
        })
    </script>
    <script>
        $(function(){
            $(".ui-select").chosen();
            $("#province").change(function () {
                var code = $(this).val();
                var html;
                if( code == '' || code.length == 0 )
                {
                    html = '<option value="">城市</option>';
                    $("#city").html(html);
                    html = '<option value="">区</option>';
                    $("#town").html(html);
                    $(".ui-select").trigger("chosen:updated");
                    alert( '请选择省份' );
                    return false;
                }
                $.post( '/ajax', {
                            method:'city',
                            code:code
                        },
                        function ( obj ){
                            html = '<option value="">城市</option>';
                            $.each( obj.data, function ( k,v ) {
                                html += '<option value="'+ v.city_code +'">'+ v.city +'</option>';
                            })
                            $("#city").html(html);
                            html = '<option value="">区</option>';
                            $("#town").html(html);
                            $(".ui-select").trigger("chosen:updated");
                        },
                        'json'
                )
            })
            $("#city").change(function () {
                var code = $(this).val();
                var html;
                if( code == '' || code.length == 0 )
                {
                    html = '<option value="">区</option>';
                    $("#town").html(html);
                    $(".ui-select").trigger("chosen:updated");
                    alert( '请选择城市' );
                    return false;
                }
                $.post( '/ajax', {
                            method:'town',
                            code:code
                        },
                        function ( obj ){
                            html = '<option value="">区</option>';
                            $.each( obj.data, function ( k,v ) {
                                html += '<option value="'+ v.town_code +'">'+ v.town +'</option>';
                            })
                            $("#town").html(html);
                            $(".ui-select").trigger("chosen:updated");
                        },
                        'json'
                )
            })
        });
    </script>

    <script src="{{ URL::asset('/js/bootstrap-treeview.js') }}"></script>
    <script src="{{ URL::asset('/js/jquery.validate.min.js') }}"></script>
@stop

@section('body')
    <div class="row">
        <div class="col-sm-12">
            <section class="panel">
                <header class="panel-heading">
                    @if( empty( $storage['id'] ) )
                        新增仓库
                    @else
                        仓库编辑
                    @endif
                </header>
                <div class="panel-body">
                    <div class="form">
                        <form class="cmxform form-horizontal adminex-form" method="post" action="/storage/save" >
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="id" value="{{array_get($storage,'id')}}">

                            <div class="form-group ">
                                <label for="name" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 仓库名称</label>
                                <div class="col-lg-4 col-xs-12">
                                    <input class="form-control" id="name" name="name" type="text" value="{{array_get($storage,'name')}}" autocomplete="off" required="required" minlength="2" maxlength="20"/>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label for="acronym" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 仓库缩写</label>
                                <div class="col-lg-4 col-xs-12">
                                    <input class="form-control" id="acronym" name="acronym" type="text" value="{{array_get($storage,'acronym')}}" autocomplete="off" required="required" minlength="2" maxlength="5"/>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label for="parent_name" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 上级仓库</label>
                                <div class="col-lg-2 col-xs-8">
                                    <input type="hidden" id="parent_id" name="parent_id" value="{{array_get($storage,'parent_id')}}">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="parent_name" name="parent_name" value="{{array_get($storage,'parent_name')}}" readonly="readonly">
                                        @if( empty( $storage['id'] ) )
                                        <span class="input-group-btn">
                                            <button class="btn btn-default" type="button" data-toggle="modal" data-target="#myModal">选择</button>
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label for="custom_id" class="control-label col-lg-2">自定义编号</label>
                                <div class="col-lg-4 col-xs-12">
                                    <input class="form-control" id="custom_id" name="custom_id" type="text" value="{{array_get($storage,'custom_id')}}" autocomplete="off" minlength="2" maxlength="20"/>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label for="custom_id" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 省市区</label>
                                <div class="col-lg-6 col-xs-12">
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <select class="form-control ui-select" name="province_code" id="province" required="required">
                                                <option value="">省份</option>
                                                @foreach( $province as $v )
                                                    @if( !empty( $storage['province_code'] ) && $v['province_code'] == $storage['province_code'] )
                                                        <option value="{{$v['province_code']}}" selected="selected">{{$v['province']}}</option>
                                                    @else
                                                        <option value="{{$v['province_code']}}">{{$v['province']}}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-lg-4">
                                            <select class="form-control ui-select" name="city_code" id="city" required="required">
                                                <option value="">城市</option>
                                                @if( !empty( $city ))
                                                @foreach( $city as $v )
                                                    @if( !empty( $storage['city_code'] ) && $v['city_code'] == $storage['city_code'] )
                                                        <option value="{{$v['city_code']}}" selected="selected">{{$v['city']}}</option>
                                                    @else
                                                        <option value="{{$v['city_code']}}">{{$v['city']}}</option>
                                                    @endif
                                                @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-lg-4">
                                            <select class="form-control ui-select" name="town_code" id="town" required="required">
                                                <option value="">区</option>
                                                @if( !empty( $town ))
                                                    @foreach( $town as $v )
                                                        @if( !empty( $storage['town_code'] ) && $v['town_code'] == $storage['town_code'] )
                                                            <option value="{{$v['town_code']}}" selected="selected">{{$v['town']}}</option>
                                                        @else
                                                            <option value="{{$v['town_code']}}">{{$v['town']}}</option>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label for="address" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 详细地址</label>
                                <div class="col-lg-6 col-xs-12">
                                    <input class="form-control" id="address" name="address" type="text" value="{{array_get($storage,'address')}}" placeholder="（填写详细地址）" autocomplete="off" required="required" minlength="2" maxlength="50"/>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-lg-offset-2 col-lg-10">
                                    <button class="btn btn-primary" type="submit">保存</button>
                                    <button class="btn btn-default" onclick="location.href = '/storage/list{{$urlParam}}'" type="button">返回</button>
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