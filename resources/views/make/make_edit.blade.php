@extends('temp.index')

@section('style')
    <style>

    </style>
@stop

@section('scripts')
    <script>
        $(function(){
            $(".ui-select").chosen();
        });
    </script>
    <script src="{{ URL::asset('/js/jquery.validate.min.js') }}"></script>
@stop

@section('body')
    <div class="row">
        <div class="col-sm-12">
            <section class="panel">
                <header class="panel-heading">
                    制券申请
                </header>
                <div class="panel-body">
                    <div class="form">
                        <form class="cmxform form-horizontal adminex-form" method="post" action="/make/save" autocomplete="off">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            @if(  !empty( $row_type['id'] ) )
                                <input type="hidden" name="id" value="{{$row_type['id']}}">
                            @endif
                            <div class="form-group ">
                                <label for="category" class="control-label col-lg-2">* 选择制券券种</label>
                                <div class="col-lg-4 col-xs-12">
                                    <select class="form-control ui-select" required name="coupon_type" >
                                        @if(  count($typeName) > 0 )
                                            @foreach ( $typeName as $key => $value )
                                                @if(isset($rowMake['coupon_type']) && $rowMake['coupon_type'] == $value['id'].'_'.$value['name'])
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
                                <label for="detail_name" class="control-label col-lg-2">* 制券数量</label>
                                <div class="col-lg-4 col-xs-12">
                                        <input class="form-control" id="detail_name" name="amount" type="number" value="{{array_get($rowMake,'amount')}}" autocomplete="off" required minlength="1"/>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="name" class="control-label col-lg-2">* 有效期开始日期</label>
                                <div class="col-lg-4 col-xs-12">
                                        <input class="form-control" readonly name="start_time" type="text" value="{!! date('Y-m-d', strtotime(date('Y-m')." +2 month -1 day")) !!}" autocomplete="off" minlength="2"/>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="price" class="control-label col-lg-2">* 票面截止日期</label>
                                <div class="col-lg-4 col-xs-12">
                                        <input class="form-control" readonly name="end_time" type="text" value="{!! date('Y-m-d', strtotime(date('Y-m')." +3 year +2 month -1 seconds")) !!}" autocomplete="off" minlength="2"/>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="nickname" class="control-label col-lg-2">备注</label>
                                <div class="col-lg-4 col-xs-12">
                                        <textarea class="form-control" autocomplete="off" name="memo">{{array_get($rowMake,'memo')}}</textarea>
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