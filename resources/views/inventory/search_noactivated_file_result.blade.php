@extends('temp.index')

@section('style')
    <style>

    </style>
    <link href="{{ URL::asset('/css/bootstrap-treeview.css') }}" rel="stylesheet">
@stop

@section('scripts')
    <script src="{{ URL::asset('/js/bootstrap-treeview.js') }}"></script>
@stop

@section('body')
    <div class="row">
        <div class="col-sm-12">
            <section class="panel">
                <header class="panel-heading">
                    库存检查
                </header>
                @if(!empty($csvUrl))
                    <div class="panel-body">
                        导入完毕
                        <br>
                        请下载“库存检查数据”查看券状态
                        <br>
                        <a class="btn btn-default" href="{{$csvUrl}}">库存检查数据</a>
                    </div>
                @else
                    <div class="panel-body">
                        导入完毕
                        <br>
                        导入的券都在库存中
                        <br>
                    </div>
                @endif
            </section>
        </div>
    </div>
@stop

@section('footer')
    <footer>
        2014 &copy; AdminEx by ThemeBucket
    </footer>
@stop