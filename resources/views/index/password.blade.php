@extends('temp.index')

@section('style')
    <style>

    </style>
@stop

@section('scripts')
    <script src="{{ URL::asset('/js/jquery.validate.min.js') }}"></script>
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
                        <form class="cmxform form-horizontal adminex-form" method="post" action="/password/save" autocomplete="off">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">

                            <div class="form-group ">
                                <label for="old" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 旧密码</label>
                                <div class="col-lg-4 col-xs-12">
                                    <input class="form-control" name="old" type="password" value="{{array_get($user,'old')}}" autocomplete="off" required/>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="new" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 新密码</label>
                                <div class="col-lg-4 col-xs-12">
                                    <input class="form-control" name="new" type="password" value="" autocomplete="off" required minlength="8"/>
                                </div>
                            </div>
                            <div class="form-group ">
                                <label for="confirm" class="control-label col-lg-2"><span class="red"><i class="fa fa-asterisk"></i></span> 确认密码</label>
                                <div class="col-lg-4 col-xs-12">
                                    <input class="form-control" name="confirm" type="password" value="" autocomplete="off" required minlength="8"/>
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