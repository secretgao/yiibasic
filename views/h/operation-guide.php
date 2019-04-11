<?php
/**
 * Created by PhpStorm.
 * User: gaoxinyu
 * Date: 2019/3/25
 * Time: 22:30
 */

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>操作指南</title>
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
            background-color: #2B66B8;
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
<img src="../img/banner1.png" alt="" class="banner">
<img src="../img/text1.png" alt="" class="text-img">
<ul class="list">
    <li class="item">
        创建项目流程：首页点击项目管理，添加项目。创建需要填写项目类型，名称，描述以及批复金额、部门、开始时间、选择项目参与人员等。
    </li>
    <li class="item">
        首页项目：首页项目列表可以查看项目备注，转发邮件，项目对应搜索新闻，设置关注，以及点击查看项目问卷调查。
    </li>
    <li class="item">
        项目详情：根据创建项目时填写类型创建对应目录，目录下均可添加文件，选择右上角加号。
        右上角省略号弹窗可以查看本项目上传记录，传输列表，以及管理设置。管理界面可以设置项目开始完成状态以及分管领导，还可以对人员进行增减，设置项目负责人。
    </li>
    <li class="item">
        项目负责人:可以审核当前项目所上传文件。文件经过审核之后才会显示至项目目录。
    </li>
</ul>
</body>
</html>
