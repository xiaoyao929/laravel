@extends('temp.index')

@section('style')
    <style>

    </style>
@stop

@section('scripts')
    <script>
        $(function () {
            $(".ui-select").chosen();
        })
    </script>
    <script>
        $('.pass-select li').click(function () {
            var text = $(this).text();
            $('#pass-select').val(text);
            $('#pass-show').html(text+ ' <span class="caret"></span>');
        })
    </script>
    <script src="{{ URL::asset('/js/jquery.validate.min.js') }}"></script>
@stop

@section('body')
    <div class="row">
        <div class="col-sm-12">
            <section class="panel">
                <header class="panel-heading">
                    换券申请
                </header>
                <div class="panel-body">
                    <div class="form">
                        <form class="cmxform form-horizontal adminex-form" method="post" action="/exchange/replace/apply/save">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">

                            <div class="form-group ">
                                <label for="nickname" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 券简称</label>
                                <div class="col-lg-4">
                                    <select class="form-control ui-select" name="type">
                                        @foreach( $type as $v )
                                            @if( !empty( $coupon['type'] ) && $coupon['type'] == $v['id'] )
                                                <option value="{{$v['id']}}" selected="selected">{{$v['name']}}</option>
                                            @else
                                                <option value="{{$v['id']}}">{{$v['name']}}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label for="from" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 旧券券号</label>
                                <div class="col-lg-4">
                                    <input class="form-control" name="from" type="number" value="{{array_get($coupon,'from')}}" autocomplete="off" required minlength="2"/>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label for="to" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 新券券号</label>
                                <div class="col-lg-4">
                                    <input class="form-control" name="to" type="number" value="{{array_get($coupon,'to')}}" autocomplete="off" required minlength="2"/>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label for="name" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 原因</label>
                                <div class="col-lg-4">
                                    <div class="input-group">
                                        <div class="input-group-btn">
                                            @if( empty( $coupon['reason'] ))
                                                <button type="button" class="btn btn-default dropdown-toggle" id="pass-show" data-toggle="dropdown">券质量问题 <span class="caret"></span></button>
                                                <input type="hidden" id="pass-select" name="reason" value="券质量问题">
                                            @else
                                                <button type="button" class="btn btn-default dropdown-toggle" id="pass-show" data-toggle="dropdown">{{array_get($coupon,'reason')}} <span class="caret"></span></button>
                                                <input type="hidden" id="pass-select" name="reason" value="{{array_get($coupon,'reason')}}">
                                            @endif
                                            <ul class="dropdown-menu pass-select">
                                                <li><a href="javascript:void(0)">券质量问题</a></li>
                                                <li><a href="javascript:void(0)">客户损坏</a></li>
                                                <li><a href="javascript:void(0)">自然原因破损</a></li>
                                                <li><a href="javascript:void(0)">其他</a></li>
                                            </ul>
                                        </div>
                                        <input type="text" class="form-control" name="text" value="{{array_get($coupon,'text')}}" autocomplete="off" placeholder="(填写原因)" minlength="2" maxlength="20" >
                                    </div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label for="acronym" class="control-label col-lg-2">备注</label>
                                <div class="col-lg-4">
                                    <textarea rows="6" class="form-control" name="memo">{{array_get($coupon,'memo')}}</textarea>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-lg-offset-2 col-lg-10">
                                    <button class="btn btn-primary" type="submit">确定</button>
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