@extends('temp.index')

@section('style')
    <style>

    </style>
    <link href="{{ URL::asset('/css/bootstrap-treeview.css') }}" rel="stylesheet">
@stop

@section('scripts')
    <script src="{{ URL::asset('/js/jquery.validate.min.js') }}"></script>
    <script src="{{ URL::asset('/js/bootstrap-treeview.js') }}"></script>
@stop

@section('body')
    <div class="row">
        <div class="col-sm-12">
            <section class="panel">
                <header class="panel-heading">
                    公司新增
                </header>
                <section class="panel">
                    <div class="panel-body">
                        <button class="btn btn-default" type="button" data-toggle="modal" data-target="#myModal">导入数据</button>
                    </div>
                </section>

                <div class="panel-body">
                    <div class="form">
                        <form class="cmxform form-horizontal adminex-form" method="post" action="/client/inside/sector/save" autocomplete="off">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <div class="form-group ">
                                <label for="nickname" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 公司名称</label>
                                <div class="col-lg-4 col-xs-12">
                                    <input class="form-control" id="nickname" name="company_name" type="text" value="{{array_get($rowData,'company_name')}}" autocomplete="off" required />
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="tel" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 公司编号</label>
                                <div class="col-lg-4 col-xs-12">
                                    <input class="form-control" name="company_id" type="text" value="{{array_get($rowData,'company_id')}}" autocomplete="off" required />
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="tel" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 部门名称</label>
                                <div class="col-lg-4 col-xs-12">
                                    <input class="form-control" name="sector_name" type="text" value="{{array_get($rowData,'sector_name')}}" autocomplete="off" required />
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="tel" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 部门编号</label>
                                <div class="col-lg-4 col-xs-12">
                                    <input class="form-control" name="sector_id" type="text" value="{{array_get($rowData,'sector_id')}}" autocomplete="off" required  />
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
            <form class="cmxform form-horizontal adminex-form" method="post" action="/client/inside/sector/file/save" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title text-center">上传文件</h4>
                    </div>
                    <div class="modal-body">
                        <p class="text-left">批量导入您的客户信息，请使用模板文件导入。<a href="/templat/inside_sector.csv">下载模板文件</a><br>注意：<br>1. 每次导入数据限1000条以内<br>2.如部门编号已存在，则该行数据不会被导入</p><hr>
                        <div class="form-group text-left" style="margin-left:0"><label for="file2">选择文件</label><input type="file" name="csvFile" id="csvFile"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" id="save">确认</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('footer')
    <footer>
        2014 &copy; AdminEx by ThemeBucket
    </footer>
@stop