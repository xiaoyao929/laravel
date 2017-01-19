<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="ThemeBucket">
    <link rel="shortcut icon" href="#" type="image/png">

    <title>Login</title>

    <link href="{{ URL::asset('/css/style.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('/css/style-responsive.css') }}" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="{{ URL::asset('/js/html5shiv.js') }}"></script>
    <script src="{{ URL::asset('/js/respond.min.js') }}"></script>
    <![endif]-->
    <style>
        .code{padding: 0px;}
        .verify_p{position: relative;}
        .verify_p input{width: 50%;}
        .verify_p span{position: absolute;top: 0;right: 0;}
    </style>
</head>

<body class="login-body">

<div class="container">

    <form class="form-signin" action="/login/verify" method="post" >
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="form-signin-heading text-center">
            <img src="{{ URL::asset('/images/login-logo.png') }}" width="200" height="150" alt=""/>
        </div>
        <div class="login-wrap">
            <input type="text" class="form-control" name="account" placeholder="用户名" autofocus>
            <input type="password" class="form-control" name="password" placeholder="密码">

			<p class="verify_p">
				<input type="text" class="form-control" name="verify_code" placeholder="验证码">
				<span>
					<img id="verify_code" src="/login/verify/code" onclick="javascript:getCode();" title="点击更换验证码">
				</span>
			</p>


            @foreach ( $errors->all() as $error )
                <div class="alert alert-danger ">
                    <button type="button" class="close close-sm" data-dismiss="alert">
                        <i class="fa fa-times"></i>
                    </button>
                    {{ $error }}
                </div>
            @endforeach
            <button class="btn btn-lg btn-login btn-block" type="submit">
                <i class="fa fa-check"></i>
            </button>

        </div>

    </form>

</div>



<!-- Placed js at the end of the document so the pages load faster -->

<!-- Placed js at the end of the document so the pages load faster -->
<script src="{{ URL::asset('/js/jquery-1.10.2.min.js') }}"></script>
<script src="{{ URL::asset('/js/bootstrap.min.js') }}"></script>
<script src="{{ URL::asset('/js/modernizr.min.js') }}"></script>
<script>
	function getCode(){
		document.getElementById("verify_code").src = "/login/verify/code?v="+Math.random(1000);
	}
</script>
</body>
</html>
