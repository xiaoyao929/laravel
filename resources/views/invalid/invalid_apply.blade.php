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
        $("#start").change(function () {
            var start_seq = $('#start').val();
            var end_seq   = $('#end').val();
            if( start_seq == '' || end_seq == '' || start_seq < 0 || end_seq < 0 ) return false;
            if( end_seq < start_seq )
            {
                alert('起始号不可以小于结束号');
                return false;
            }
            var nums = end_seq - start_seq + 1;
            $( '#nums' ).val(nums);
        });
        $("#end").change(function () {
            var start_seq = $('#start').val();
            var end_seq   = $('#end').val();
            if( start_seq == '' || end_seq == '' || start_seq < 0 || end_seq < 0 ) return false;
            if( end_seq < start_seq )
            {
                alert('起始号不可以小于结束号');
                return false;
            }
            var nums = end_seq - start_seq + 1;
            $( '#nums' ).val(nums);
        });
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
                    券作废申请
                </header>
                <div class="panel-body">
                    <div class="form">
                        <form class="cmxform form-horizontal adminex-form" method="post" action="/invalid/apply/save">
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
                                <label for="start" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 开始券号</label>
                                <div class="col-lg-4">
                                    <input class="form-control" id="start" name="start" type="number" value="{{array_get($coupon,'start')}}" autocomplete="off" required minlength="2"/>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label for="end" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 结束券号</label>
                                <div class="col-lg-4">
                                    <input class="form-control" id="end" name="end" type="number" value="{{array_get($coupon,'end')}}" autocomplete="off" required minlength="2"/>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label for="nums" class="control-label col-lg-2">数量</label>
                                <div class="col-lg-2">
                                    <input class="form-control" id="nums" name="nums" type="text" value="{{array_get($coupon,'nums')}}" readonly="readonly"/>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label for="name" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 原因</label>
                                <div class="col-lg-4">
                                    <div class="input-group">
                                        <div class="input-group-btn">
                                            @if( empty( $coupon['reason'] ))
                                                <button type="button" class="btn btn-default dropdown-toggle" id="pass-show" data-toggle="dropdown">过期 <span class="caret"></span></button>
                                                <input type="hidden" id="pass-select" name="reason" value="过期">
                                            @else
                                                <button type="button" class="btn btn-default dropdown-toggle" id="pass-show" data-toggle="dropdown">{{array_get($coupon,'reason')}} <span class="caret"></span></button>
                                                <input type="hidden" id="pass-select" name="reason" value="{{array_get($coupon,'reason')}}">
                                            @endif
                                            <ul class="dropdown-menu pass-select">
                                                <li><a href="javascript:void(0)">过期</a></li>
                                                <li><a href="javascript:void(0)">遗失</a></li>
                                                <li><a href="javascript:void(0)">质量问题</a></li>
                                                <li><a href="javascript:void(0)">其他</a></li>
                                            </ul>
                                        </div>
                                        <input type="text" class="form-control" name="text" value="{{array_get($coupon,'text')}}" autocomplete="off" placeholder="(填写原因)" minlength="2" >
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