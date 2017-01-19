@extends('temp.index')

@section('style')
    <style>
        .main-content {background: #f1f7ff;}
    </style>
@stop

@section('scripts')
    <!--Sparkline Chart-->
    <script src="{{ URL::asset('/js/sparkline/jquery.sparkline.js') }}"></script>
    <script src="{{ URL::asset('/js/sparkline/sparkline-init.js') }}"></script>
    <script>

    </script>
@stop

@section('body')
    <div class="row">
        <div class="col-md-12 text-center">
            <p><img src="{{ URL::asset('/images/welcome.png') }}"></p>
        </div>
    </div>
@stop

@section('footer')
@stop