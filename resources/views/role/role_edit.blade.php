@extends('temp.index')

@section('style')
    <style>

    </style>
@stop

@section('scripts')
    <script>

    </script>
    <script src="{{ URL::asset('/js/jquery.validate.min.js') }}"></script>
@stop

@section('body')
    <div class="row">
        <div class="col-sm-12">
            <section class="panel">
                <header class="panel-heading">
                    分组编辑
                </header>
                <div class="panel-body">
                    <div class="form">
                        <form class="cmxform form-horizontal adminex-form" method="post" action="/role/save">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="id" value="{{array_get( $role, 'id' )}}">

                            <div class="form-group ">
                                <label for="name" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 组名</label>
                                <div class="col-lg-4 col-xs-12">
                                    <input class="form-control" id="name" name="name" type="text" value="{{array_get( $role, 'name' )}}" autocomplete="off" required minlength="2" maxlength="20"/>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="description" class="control-label col-lg-2">描述</label>
                                <div class="col-lg-4 col-xs-12">
                                    <input class="form-control" id="description" name="description" type="text" value="{{array_get( $role, 'description' )}}" autocomplete="off"  minlength="5" maxlength="20"/>
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