<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="ThemeBucket">
    <link rel="shortcut icon" href="#" type="image/png">

    <title>404 Page</title>

    <link href="{{ URL::asset('/css/style.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('/css/style-responsive.css') }}" rel="stylesheet">
    <style>
        body {background: #eff0f4;}
        .error-wrapper .back-btn {color: #424f62;border: 1px solid #424f62;}
        .error-wrapper .back-btn:hover {background: #424f62;color: #fff;}
        h4 { color: #424f62;}
    </style>
</head>

<body>

<section>
    <div class="container ">

        <section class="error-wrapper text-center">
            <p><img src="{{ URL::asset('/images/404.png') }}"></p>
            <h4>糟糕~</h4>
            <h4>页面找不到了</h4>
            <a class="back-btn" href="javascript:window.history.go(-1)">返回上一页</a>
        </section>

    </div>
</section>
</body>
</html>