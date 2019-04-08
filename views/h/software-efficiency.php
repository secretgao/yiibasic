<?php
/**
 * Created by PhpStorm.
 * User: gaoxinyu
 * Date: 2019/3/26
 * Time: 12:19
 */

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>软件效能</title>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport">
    <script>
        window.onresize = textSize;
        function textSize() {
            var width = document.documentElement.clientWidth || document.body.clientWidth;
            var ratio = 750 / width;
            var con = document.getElementsByTagName('html')[0];
            con.style.fontSize = 100 / ratio + 'px';
        }
        textSize();
    </script>
    <style>
        body,div,dl,dt,dd,ul,ol,li,h1,h2,h3,h4,h5,h6,pre,code,form,fieldset,legend,input,button,textarea,p,blockquote,th,td { margin:0; padding:0;box-sizing: border-box;}
        body { background:#fff; color:#555; font-size:14px; font-family: "Microsoft YaHei","Arial","Microsoft YaHei","黑体","宋体",sans-serif; }
        td,th,caption { font-size:14px; }
        h1, h2, h3, h4, h5, h6 { font-weight:normal; font-size:100%; }
        address, caption, cite, code, dfn, em, strong, th, var { font-style:normal; font-weight:normal;}
        a { color:#555; text-decoration:none; }
        a:hover { text-decoration:underline; }
        img { border:none; }
        ol,ul,li { list-style:none; }
        input, textarea, select, button { font:14px "Microsoft YaHei","Arial","黑体","宋体",sans-serif; }
        table { border-collapse:collapse; }
        html {overflow-y: scroll;}

        .cf:after {content: "."; display: block; height:0; clear:both; visibility: hidden;}
        .cf { *zoom:1; }/*公共类*/
        .fl { float:left}
        .fr {float:right}
        .al {text-align:left}
        .ac {text-align:center}
        .ar {text-align:right}
        .hide {display:none}
        html,body{
            font-size: 16px;
            background-color: #393697;
            min-width: 100%;height: 100%;

        }
        .banner{
            display: block;width: 100%;margin: 0;
        }
        .text-img{
            display: block;
            width: 55%;
            margin: 0.5rem auto;
        }
        .text{
            width: 	88%;
            margin: 0.3rem auto;
            color: #fff;
            word-break: break-all;
            line-height: 1.5;
            color: rgba(255,255,255,0.7);
        }
    </style>
</head>
<body>
<img src="../img/banner3.png" alt="" class="banner">
<img src="../img/text3.png" alt="" class="text-img">
<div class="text">
    文联绩效助手App是基于财政绩效管理理念和信息技术手段开发的一款绩效管理手机软件，旨在通过方便、快捷的软件操作，规范预算项目绩效管理和实现对预算项目实施的全程管控，以保证既定绩效目标的实现，绩效资料的及时搜集，提高项目实施水准和财政资金使用效益。
</div>
</body>
</html>
