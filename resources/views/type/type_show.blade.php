@extends('temp.index')

@section('style')
    <style>
        .state{
            display: block;
            width: 100%;
            height: 34px;
            padding: 6px 0px;
            font-size: 14px;
            line-height: 1.42857143;
            color: #555;
            background-color: #fff;
            background-image: none;
        }
    </style>
@stop

@section('scripts')

@stop

@section('body')
    <div class="row">
        <div class="col-sm-12">
            <section class="panel">
                <header class="panel-heading">
                    券种查询
                </header>
                <div class="panel-body">
                    <div class="form">
                        <form class="cmxform form-horizontal adminex-form" action="" >
                            <div class="form-group ">
                                <label class="control-label col-lg-2">券种编号</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$detail->id}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label  class="control-label col-lg-2">券种简称</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$detail->name}}</div>
                                </div>
                                <label  class="control-label col-lg-1">券种详称</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$detail->detail_name}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">类别</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$detail->class_name}}</div>
                                </div>
                                <label for="name" class="control-label col-lg-1">单价</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{priceShow($detail->price)}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">终端组名称</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$detail->group_name}}</div>
                                </div>
                                <label class="control-label col-lg-2">状态</label>
                                <div class="col-lg-4">
                                    <div class="state" >
                                        @if( $detail->status == 1 )
                                            <span class="label label-success">正常</span>
                                        @else
                                            <span class="label label-warning">停用</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">备注</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{$detail->memo}}</div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-lg-offset-2 col-lg-10">
                                    <button class="btn btn-default" onclick="location.href = '/type/list{{$urlParam}}'" type="button">确定</button>
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