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
            @if(!empty($csvUrl) && !empty($returnErr))
                <section class="panel">
                    <div class="panel-body">
                        <section id="flip-scroll">
                            <table class="table table-bordered table-striped table-condensed cf table-hover">
                                <tbody class="select_count">
                                @foreach($returnErr as $key => $value)
                                    <tr>
                                        <td>
                                            第{{$key}}行，导入失败，错误原因：{{$value['err']}}
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </section>
                    </div>
                </section>
                <div class="col-md-6 ">
                    <p class="text-danger">
                        导入完毕<br>共{{$count}}条数据，成功导入{!! $count-$fileCount !!}条，失败{{$fileCount}}条
                    </p>
                </div>
                <section class="panel">
                    <form class="cmxform form-horizontal adminex-form" method="post" action="/client/inside/sector/file/save" autocomplete="off" enctype="multipart/form-data">
                        <div class="panel-body">
                            <a class="btn btn-default" href="{{$csvUrl}}" >下载数据</a>
                        </div>
                    </form>
                </section>
            @else
                <div class="col-md-6 ">
                    <p class="text-danger">
                        导入完毕<br>共{{$count}}条数据，成功导入{!! $count-$fileCount !!}条，失败{{$fileCount}}条
                    </p>
                </div>
            @endif
        </div>
    </div>
@stop

@section('footer')
    <footer>
        2014 &copy; AdminEx by ThemeBucket
    </footer>
@stop