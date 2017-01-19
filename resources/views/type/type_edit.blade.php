@extends('temp.index')

@section('style')
    <style>

    </style>
@stop

@section('scripts')
    <script>
        $(function(){
            $(".ui-select").chosen();
            $("body").on("click","li.active-result",function(){
                var v = $(this).attr("data-option-array-index");
                v==0 ? $("#price").show() : $("#price").hide();
                v==0 ? $("#price input").attr("required","required") : $("#price input").removeAttr("required");

            });
        });
    </script>
    <script src="{{ URL::asset('/js/jquery.validate.min.js') }}"></script>
@stop

@section('body')
    <div class="row">
        <div class="col-sm-12">
            <section class="panel">
                <header class="panel-heading">
                    新增券种
                </header>
                <div class="panel-body">
                    <div class="form">
                        <form class="cmxform form-horizontal adminex-form" method="post" action="/type/save" autocomplete="off">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            @if(  !empty( $row_type['id'] ) )
                                <input type="hidden" name="id" value="{{$row_type['id']}}">
                            @endif
                                <div class="form-group ">
                                    <label for="class_id" class="control-label col-lg-2">* 类别</label>
                                    <div class="col-lg-4 col-xs-12">
                                        <select class="form-control ui-select" name="class_id" >
                                            @if(  count($class) > 0 )
                                                @foreach ( $class as $key => $value )
                                                    @if( !empty( $row_type['id'] ) &&  $row_type['class_id'] == $value['id'])
                                                        <option value="{{$value['id']}}_{{$value['name']}}" selected>{{$value['name']}}</option>
                                                    @else
                                                        <option value="{{$value['id']}}_{{$value['name']}}">{{$value['name']}}</option>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            <div class="form-group ">
                                <label for="detail_name" class="control-label col-lg-2">* 券种详称</label>
                                <div class="col-lg-4 col-xs-12">
                                    @if(  empty( $row_type['id'] ) )
                                        <input class="form-control" id="detail_name" name="detail_name" type="text" value="{{array_get($row_type,'detail_name')}}" autocomplete="off" required minlength="2" maxlength='20'/>
                                    @else
                                        <input class="form-control" id="detail_name" name="detail_name" type="text" value="{{$row_type['detail_name']}}" autocomplete="off" required minlength="2" maxlength='20'>

                                    @endif
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="name" class="control-label col-lg-2">* 券种简称</label>
                                <div class="col-lg-4 col-xs-12">
                                    @if(  empty( $row_type['id'] ) )
                                        <input class="form-control" id="name" name="name" type="text" value="{{array_get($row_type,'name')}}" autocomplete="off" required minlength="2" maxlength='20'/>
                                    @else
                                        <input class="form-control" id="name" name="name" type="text" value="{{$row_type['name']}}" autocomplete="off" required minlength="2" maxlength='20'/>

                                    @endif
                                </div>
                            </div>
                            <div class="form-group " id="price">
                                <label for="price" class="control-label col-lg-2">* 单价</label>
                                <div class="col-lg-4 col-xs-12">
                                    @if(  empty( $row_type['id'] ) )
                                        <input class="form-control" id="price" name="price" type="text" value="{{array_get($row_type,'price')}}" autocomplete="off"  minlength="2" maxlength='8' required />
                                    @else
                                        <input class="form-control" id="price" name="price" type="text" value="{{$row_type['price']}}" autocomplete="off" minlength="2" maxlength='8' required />

                                    @endif
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="nickname" class="control-label col-lg-2">受理范围</label>
                                <div class="col-lg-3">
                                    <select class="form-control" name="group_id">
                                        @foreach($group as $key => $value)
                                            <option value="{{$value['group_id']}}">{{$value['name']}}</option>
                                        @endforeach
                                    </select>

                                    {{--<input type="hidden" value="{{$group[0]['group_id']}}" name="group_id">--}}
                                        {{--<input class="form-control" name="group_name" type="text" value="{{$group[0]['name']}}" autocomplete="off" readonly /></input>--}}
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="custom_no" class="control-label col-lg-2">自定义编号</label>
                                <div class="col-lg-4 col-xs-12">
                                    @if(  empty( $row_type['id'] ) )
                                        <input class="form-control" id="custom_no" name="custom_no" type="text" value="{{array_get($row_type,'custom_no')}}" autocomplete="off" minlength="2" maxlength='20'/>
                                    @else
                                        <input class="form-control" id="custom_no" name="custom_no" type="text" value="{{$row_type['custom_no']}}" autocomplete="off" minlength="2" maxlength='20'/>

                                    @endif
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="nickname" class="control-label col-lg-2">备注</label>
                                <div class="col-lg-4 col-xs-12">
                                    @if(  empty( $row_type['id'] ) )
                                        <textarea class="form-control" autocomplete="off" name="memo" maxlength='20'>{{array_get($row_type,'memo')}}</textarea>
                                    @else
                                        <textarea class="form-control" autocomplete="off" name="memo" maxlength='20'>{{$row_type['memo']}}</textarea>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-lg-offset-2 col-lg-10">
                                    <button class="btn btn-primary" type="submit">保存</button>
                                </div>
                            </div>
                        </form>
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