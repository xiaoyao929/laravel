<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="ThemeBucket">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="shortcut icon" href="#" type="image/png">

    <title>纸质代金券券管理平台</title>

    <!--responsive table-->
    <link href="{{ URL::asset('/css/table-responsive.css') }}" rel="stylesheet" />

    <link href="{{ URL::asset('/css/style.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('/css/style-responsive.css') }}" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="{{ URL::asset('/js/html5shiv.js') }}"></script>
    <script src="{{ URL::asset('/js/respond.min.js') }}"></script>
    <![endif]-->
    <style>
        .red{color: #d9534f}
        .data_link{padding: 0px;}
    </style>
    @yield('style')
</head>

<body class="sticky-header">

<section>
    <!-- left side start-->
    <div class="left-side sticky-left-side">

        <!--logo and iconic logo start-->
        <div class="logo">
            <a href="/home"><img src="{{ URL::asset('/images/logo.png') }}" alt=""></a>
        </div>

        <div class="logo-icon text-center">
            <a href="/home"><img src="{{ URL::asset('/images/logo_icon.png') }}" alt=""></a>
        </div>
        <!--logo and iconic logo end-->


        <div class="left-side-inner">

            <!-- visible to small devices only -->
            <div class="visible-xs hidden-sm hidden-md hidden-lg">
                <div class="media logged-user">
                    <img alt="" src="{{ URL::asset('/images/photos/user-avatar.png') }}" class="media-object">
                    <div class="media-body">
                        <h4><a href="#">{{$sessionUser['nickname']}}</a></h4>
                    </div>
                </div>

                <h5 class="left-nav-title">Account Information</h5>
                <ul class="nav nav-pills nav-stacked custom-nav">
                    <li><a href="/password"><i class="fa fa-user"></i>修改密码</a></li>
                    <li><a href="/logout"><i class="fa fa-sign-out"></i> <span>登出</span></a></li>
                </ul>
            </div>

            <!--sidebar nav start-->
            <ul class="nav nav-pills nav-stacked custom-nav">
                <li><a href="/home"><i class="fa fa-home"></i> <span>首页</span></a></li>
                @foreach( $system_menus as $menuOne )
                    @if( !empty( $menuOne['menus'] ))
                        @if( !empty( $menuOne['is_active'] ) && $menuOne['is_active'] == 'on' )
                            <li class="menu-list nav-active"><a href="{{$menuOne['url']}}"><i class="{{$menuOne['icon']}}"></i> <span>{{$menuOne['name']}}</span></a>
                        @else
                            <li class="menu-list"><a href="{{$menuOne['url']}}"><i class="{{$menuOne['icon']}}"></i> <span>{{$menuOne['name']}}</span></a>
                        @endif
                    @else
                        @if( !empty( $menuOne['is_active'] ) && $menuOne['is_active'] == 'on' )
                            <li class="active"><a href="{{$menuOne['url']}}"><i class="{{$menuOne['icon']}}"></i> <span>{{$menuOne['name']}}</span></a>
                        @else
                            <li><a href="{{$menuOne['url']}}"><i class="{{$menuOne['icon']}}"></i> <span>{{$menuOne['name']}}</span></a>
                        @endif

                    @endif

                        <ul class="sub-menu-list">
                            @if( !empty( $menuOne['menus'] ) )
                            @foreach( $menuOne['menus'] as $menuTwo )
                            <li ><a href="{{$menuTwo['url']}}"> {{$menuTwo['name']}}</a></li>
                            @endforeach
                            @endif
                        </ul>
                    </li>
                @endforeach
                <li><a href="/logout"><i class="fa fa-sign-in"></i> <span>登出</span></a></li>

            </ul>
            <!--sidebar nav end-->

        </div>
    </div>
    <!-- left side end-->

    <!-- main content start-->
    <div class="main-content" >

        <!-- header section start-->
        <div class="header-section">

            <!--toggle button start-->
            <a class="toggle-btn"><i class="fa fa-bars"></i></a>
            <!--toggle button end-->

            <!--search start-->
{{--            <form class="searchform" action="index.html" method="post">
                <input type="text" class="form-control" name="keyword" placeholder="Search here..." />
            </form>--}}
            <!--search end-->

            <!--notification menu start -->
            <div class="menu-right">
                <ul class="notification-menu">
                    <li>
                        <a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                            <img src="{{ URL::asset('/images/photos/user-avatar.png') }}" alt="" />
                            {{$sessionUser['nickname']}}
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-usermenu pull-right">
                            <li><a href="/password"><i class="fa fa-user"></i>修改密码</a></li>
                            <li><a href="/logout"><i class="fa fa-sign-out"></i>登出</a></li>
                        </ul>
                    </li>

                </ul>
            </div>
            <!--notification menu end -->

        </div>
        <!-- header section end-->

        <!--body wrapper start-->
        <div class="wrapper">
            @if(  !empty( $promptMsg ) )
                <div class="alert alert-{{$promptMsg['level']}} ">
                    <button type="button" class="close close-sm" data-dismiss="alert">
                        <i class="fa fa-times"></i>
                    </button>
                    {{$promptMsg['msg']}}
                </div>
            @endif
            @foreach ( $errors->all() as $error )
                <div class="alert alert-danger ">
                    <button type="button" class="close close-sm" data-dismiss="alert">
                        <i class="fa fa-times"></i>
                    </button>
                    {{ $error }}
                </div>
            @endforeach
            @yield('body')
        </div>
        <!--body wrapper end-->

        <!--footer section start-->
        <footer>
            2016 &copy; 上海翼码 版权所有
        </footer>
        <!--footer section end-->


    </div>
    <!-- main content end-->
</section>

<!-- Placed js at the end of the document so the pages load faster -->
<script src="{{ URL::asset('/js/jquery-1.10.2.min.js') }}"></script>
<script src="{{ URL::asset('/js/jquery-ui-1.9.2.custom.min.js') }}"></script>
<script src="{{ URL::asset('/js/jquery-migrate-1.2.1.min.js') }}"></script>
<script src="{{ URL::asset('/js/bootstrap.min.js') }}"></script>
<script src="{{ URL::asset('/js/modernizr.min.js') }}"></script>
<script src="{{ URL::asset('/js/jquery.nicescroll.js') }}"></script>

<!--common scripts for all pages-->
<script src="{{ URL::asset('/js/scripts.js') }}"></script>
<!--chosen_v1.6.2-->
<link href="{{ URL::asset('/css/chosen.css') }}" rel="stylesheet">
<script src="{{ URL::asset('/js/chosen_v1.6.2/chosen.jquery.min.js') }}"></script>

<script>
    $(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    })
</script>

@yield('scripts')

</body>
</html>
