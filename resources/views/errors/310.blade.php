<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="ThemeBucket">
    <link rel="shortcut icon" href="#" type="image/png">
    <title>noLogin</title>
    <style>
		body{background: #eff0f4;font-family: "microsoft yahei",arial,sans-serif;}
		.login-out{
			width: 255px; height: 400px;
			position: absolute;top:45%;left:50%;
			margin-top: -200px;margin-left: -127.5px;
			background: url({{ URL::asset('/images/login-out.png') }}) center top no-repeat;
		}
    	.login-out p{padding-top: 230px;font:normal 18px/32px "microsoft yahei",arial,sans-serif; color: #424f62; text-align: center;}
		.login-out a{display: block; width: 80px; color: #424f62; text-decoration: none; border:1px solid #424F62; border-radius: 5px; padding:10px; margin: 0 auto;text-align: center; cursor: pointer;transition: 0.4s;}
		.login-out a:hover{color: #fff; background:#424F62; transition: 0.4s;}
    </style>
	</head>
	<body>
		<div class="login-out">
			<p>您的账号已经在其他地方登录！<br /> 请检查账号安全并稍后尝试登录</p>
			<a href="/login">重新登录</a>
		</div>
	</body>
</html>
