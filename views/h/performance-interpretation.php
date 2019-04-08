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
<img src="../img/banner2.png" alt="" class="banner">
<img src="../img/text2.png" alt="" class="text-img">
<ul class="list">
    <li class="item">
        一、预算绩效管理是指在预算管理中融入绩效理念，将绩效目标设定、事前绩效评估、绩效跟踪、绩效评价及结果应用纳入预算编制、执行、监督全过程，以提高预算的经济、社会效益为目的的管理活动。
    </li>
    <li class="item">
        二、绩效目标是绩效评价的对象计划在一定期限内达到的产出和效果。绩效目标要与部门职责相吻合，目标设置应科学可行、准确具体、简洁明了。绩效目标应当包括以下主要内容：
        预期产出，包括提供的公共产品和服务的数量目标、质量目标、时效目标、成本目标以及服务对象满意度目标；预期效果，包括经济效益、社会效益和可持续影响等；服务对象或项目受益人满意程度；达到预期产出所需要的成本资源；衡量预期产出、预期效果和服务对象满意程度的绩效指标。
    </li>
    <li class="item">
        三、绩效指标是衡量绩效目标实现程度的考核工具，分为产出指标和效果指标。产出指标反映与目标相关的产品和服务的提供情况；效果指标反映与目标相关的预算支出预期结果的实现程度。绩效指标要与绩效目标密切相关，要尽量使用反映最终结果的指标，指标设置应科学合理、量化可考。
    </li>
    <li class="item">
        四、事前绩效评估是指财政部门根据预算部门战略规划、事业发展规划、项目申报理由等内容，通过委托第三方的方式，运用科学、合理的评估方法，对项目实施必要性、可行性、绩效目标设置的科学性、财政支持的方式、项目预算的合理性等方面进行客观、公正的评估。
    </li>
    <li class="item">
        五、绩效跟踪是指财政部门和预算部门运用科学、合理的绩效信息汇总分析方法，对财政支出的预算执行、管理和绩效目标运行等情况进行跟踪管理和督促检查，及时发现问题并采取有效的措施予以纠正。
    </li>
    <li class="item">
        六、财政支出绩效评价是指财政部门和预算部门根据设定的绩效目标，运用科学、合理的绩效评价指标、评价标准和评价方法，对财政支出的经济性、效率性和效益性进行客观、公正的评价。其中，绩效目标的设立是开展绩效评价的前提和基础，绩效评价指标是绩效评价的工具和手段，财政支出的经济性、效率性和效益性是绩效评价的主要内容。
    </li>
    <li class="item">
        七、绩效评价结果应用：财政部门和预算部门应当及时整理、归纳、分析、反馈绩效评价结果，并将其作为改进预算管理和安排以后年度预算的重要依据。对绩效评价结果较好的，财政部门和预算部门可予以表扬、优先支持或继续支持。对绩效评价发现问题、达不到绩效目标或评价结果较差的，财政部门和预算部门可予以通报批评，并责令其限期整改。不进行整改或整改不到位的，应当根据情况调整项目或相应调减项目预算，直至取消该项财政支出。
    </li>
    <li class="item">
        八、绩效目标执行动态监控措施：一是规范绩效报告制度，预算部门按要求向同级财政部门报告预算绩效管理情况。二是完善绩效监控机制全覆盖，财政部门和预算部门按照确定的绩效目标开展财政资金绩效监控，并定期报告绩效监控信息。三是加强绩效监控信息应用，将绩效监控信息作为预算执行和资金拨付的参考依据，对偏离绩效目标的支出，及时采取措施予以纠正。四是绩效监督与内控建设相结合，将绩效跟踪工作的开展情况作为单位内控建设考核的重要依据。
    </li>

</ul>
</body>
</html>
