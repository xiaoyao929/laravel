@extends('temp.index')

@section('style')
    <style>
        .error-wrapper .back-btn {color: #424f62;border: 1px solid #424f62;}
        .error-wrapper .back-btn:hover {background: #424f62;color: #fff;}
        h4 { color: #424f62;}
    </style>
@stop

@section('scripts')
    <!--Sparkline Chart-->
    <script>
    
    </script>
@stop

@section('body')
    <div class="row">
        <div class="col-md-12 error-wrapper text-center">
            <p><img src="{{ URL::asset('/images/1000.png') }}"></p>
            <h4>恭喜您~</h4>
            <h4>提交成功</h4>
            <a class="back-btn" href="javascript:window.history.go(-1)">返回上一页</a>
        </div>
    </div>
@stop

@section('footer')
    <footer>
        2014 &copy; AdminEx by ThemeBucket
    </footer>
@stop