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
                    用户编辑
                </header>
                <div class="panel-body">
                    <div class="form">
                        <form class="cmxform form-horizontal adminex-form" action="" >
                            <div class="form-group ">
                                <label class="control-label col-lg-2">用户名</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($user,'account')}}</div>
                                </div>
                                <label for="name" class="control-label col-lg-1">姓名</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($user,'nickname')}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">角色</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($user,'role_name')}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">联系电话</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($user,'tel')}}</div>
                                </div>
                                <label for="name" class="control-label col-lg-1">所属仓库</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($user,'storage_name')}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">状态</label>
                                <div class="col-lg-4">
                                    <div class="state" >
                                        @if(  $user['status'] == 0 )
                                            <span class="label label-success">正常</span>
                                        @else
                                            <span class="label label-warning">停用</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-lg-offset-2 col-lg-10">
                                    <button class="btn btn-default" onclick="location.href = '/user/list{{$urlParam}}'" type="button">确定</button>
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