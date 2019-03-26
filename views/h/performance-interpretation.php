<?php
/**
 * Created by PhpStorm.
 * User: gaoxinyu
 * Date: 2019/3/26
 * Time: 12:17
 */
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>绩效解读</title>
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
            background-color: #2558C1;
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
        .list{
            width: 	88%;
            margin: 0.3rem auto;
            color: #fff;
            word-break: break-all;
            line-height: 1.5;
        }
        .item{
            margin: 0.4rem 0;
            color: rgba(255,255,255,0.7);
        }
    </style>
</head>
<body>
<img src="../img/banner2.png" alt="" class="banner">
<img src="../img/text2.png" alt="" class="text-img">
<ul class="list">
    <li class="item">
        一、绩效目标是指项目实施后能够产生的效果和效益。
    </li>
    <li class="item">
        二、绩效评估是指通过一定的程序和方法，在项目计划实施前，对项目预定目标可实现程度以及必要性的考核和估计。
    </li>
    <li class="item">
        三、绩效跟踪是指根据确定的绩效目标，采取项目跟踪、数据核查和汇总分析等方式，在项目实施过程中，动态了解和掌握绩效目标实现情况的手段，以确保绩效目标的实现。
    </li>
    <li class="item">
        四、绩效评价是指根据设定的绩效目标，运用科学合理的绩效评价指标、评价标准和评价方法，在项目完结后，对经费支出的经济性、效率性和效益性进行客观、公正的评价。
    </li>
</ul>
</body>
</html>
