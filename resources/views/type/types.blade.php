@extends('temp.index')

@section('style')
    <style>

    </style>
@stop

@section('scripts')
    <script src="{{ URL::asset('/js/jquery.validate.min.js') }}"></script>
    <script>
        $(".state").click(function () {
            var id    = $(this).data('id');
            var state = $(this).data('state');
            location.href = '/type/state?id='+ id +'&state='+ state;
        })
        //券种状态的样式控制
        var typeStatus = [
            'label label-warning',
            'label label-success'
        ];
        $.each($(".type-status"),function(index,data){
            $(data).addClass(typeStatus[$(data).attr('data-val')]);
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
                            <label for="name" class="control-label col-lg-1">券类别</label>
                            <div class="col-lg-3">
                                    <select class="form-control" name="class_id">
                                        <option value="">全部</option>
                                        @foreach($class as $key => $value)
                                            @if(!empty($search['class_id']) && $search['class_id'] == $value['id'])
                                                <option value="{{$value['id']}}" selected >{{$value['name']}}</option>
                                            @else
                                                <option value="{{$value['id']}}">{{$value['name']}}</option>
                                            @endif
                                        @endforeach
                                    </select>
                            </div>
                            <label for="name" class="control-label col-lg-1">券简称</label>
                            <div class="col-lg-3">
                                <input class="form-control" type="text" value="{{$search['name']}}" name="name" />
                            </div>
                            <label for="name" class="control-label col-lg-1">自定义编号</label>
                            <div class="col-lg-3">
                                <input class="form-control" type="text" value="{{$search['custom_no']}}" name="custom_no" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="name" class="control-label col-lg-1">券种状态</label>
                            <div class="col-lg-3">
                                <select class="form-control" name="status">
                                    @foreach($status as $key => $value)
                                        @if($search['status'] == $key)
                                            <option value="{{$key}}" selected >{{$value}}</option>
                                        @else
                                            <option value="{{$key}}">{{$value}}</option>
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
    <div class="row">
        <div class="col-sm-12">
            <section class="panel">
                <header class="panel-heading">
                    券种查询
                </header>
                <div class="panel-body">
                    <section id="flip-scroll">
                        <table class="table table-bordered table-striped table-condensed cf">
                            <thead class="cf">
                            <tr>
                                <th>券种编号</th>
                                <th>券类别</th>
                                <th>简称</th>
                                <th>详称</th>
                                <th>自定义编号</th>
                                <th>单价</th>
                                <th>终端组名称</th>
                                <th>状态</th>
                                <th class="numeric">操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if( count($list) > 0)
                                @foreach ( $list as $key => $value )
                                    <tr>
                                        <td><a class="btn-link data_link" href="/type/show?id={{$value->id}}">{{$value->id}}</a></td>
                                        <td>
                                            @foreach($class as $key2 => $value2)
                                                @if($value->class_id == $value2['id'])
                                                    {{$value2['name']}}
                                                @endif
                                            @endforeach
                                        </td>
                                        <td>{{$value->name}}</td>
                                        <td>{{$value->detail_name}}</td>
                                        <td>{{$value->custom_no}}</td>
                                        <td>{{priceShow($value->price)}}</td>
                                        <td>{{$value->type_name}}</td>
                                        <td>
                                            <span class="type-status" data-val="{{ $value->status }}">
                                                {{$status[$value->status]}}
                                            </span>
                                        </td>
                                        <td>
                                            @if($value->status == '1')
                                                <button class="btn btn-warning btn-xs state" data-id="{{$value->id}}" data-state="{{$value->status}}" type="button"><span class="fa fa-pause"></span> 停用</button>
                                            @else
                                                <button class="btn btn-success btn-xs state" data-id="{{$value->id}}" data-state="{{$value->status}}"  type="button"><span class="fa fa-play"></span> 启用</button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
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
@stop

@section('footer')
    <footer>
        2014 &copy; AdminEx by ThemeBucket
    </footer>
@stop