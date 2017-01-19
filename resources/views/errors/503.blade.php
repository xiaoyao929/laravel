<!DOCTYPE html>
<html>
    <head>
        <title>503 Page</title>
        <style>
            html, body {
                height: 100%;
            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
                height:100%;
                background: #eff0f4;
                font-family: -apple-system,BlinkMacSystemFont,PingFang SC,Microsoft Yahei;
            }

            .container {
                text-align: center;
            }

            .content {
                text-align: center;
                display: inline-block;
                margin-top: 8%;
            }
            h4 {
                font-size: 18px;
            }
            h4 {
                margin-top: 10px;
                margin-bottom: 10px;
            }
            h4 {
                font-family: inherit;
                font-weight: 500;
                line-height: 1.1;
                color: #424f62;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="content">
                <h1><img src="{{ URL::asset('/images/500.png') }}"></h1>
                <h4>糟糕~</h4>
                <h4>页面资源不可用</h4>
            </div>
        </div>
    </body>
</html>
