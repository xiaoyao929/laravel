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
                    仓库查看
                </header>
                <div class="panel-body">
                    <div class="form">
                        <form class="cmxform form-horizontal adminex-form" action="" >
                            <div class="form-group ">
                                <label class="control-label col-lg-2">仓库名称</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($storage,'name')}}</div>
                                </div>
                                <label for="name" class="control-label col-lg-1">仓库缩写</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($storage,'acronym')}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">上级仓库名</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($storage,'parent_name')}}</div>
                                </div>
                                <label for="name" class="control-label col-lg-1">仓库级别</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($storage,'level')}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">自定义编号</label>
                                <div class="col-lg-3">
                                    <div class="form-control" >{{array_get($storage,'custom_id')}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">省市区</label>
                                <div class="col-lg-6">
                                    <div class="form-control" >{{array_get($storage,'province')}}-{{array_get($storage,'city')}}-{{array_get($storage,'town')}} {{array_get($storage,'address')}}</div>
                                </div>
                            </div>

                            <div class="form-group ">
                                <label class="control-label col-lg-2">状态</label>
                                <div class="col-lg-4">
                                    <div class="state" >
                                    @if(  $storage['status'] == 1 )
                                        <span class="label label-success">正常</span>
                                    @else
                                        <span class="label label-warning">停用</span>
                                    @endif
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-lg-offset-2 col-lg-10">
                                    <button class="btn btn-default" onclick="location.href = '/storage/list{{$urlParam}}'" type="button">确定</button>
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