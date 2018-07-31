<?php

namespace app\controllers;


use app\commond\Email;
use app\commond\PHPMailer\PHPMailer;
use Yii;


use yii\base\Exception;

/**
 * 文件操作
 * @author Administrator
 *
 */

class SendEmailController extends BasicController
{

    public function init(){
       parent::init();
    }



    public function actionSend()
    {


        $config = YII::$app->params;

        $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
        try {


            $mail = new PHPMailer();
// 是否启用smtp的debug进行调试 开发环境建议开启 生产环境注释掉即可 默认关闭debug调试模式
            $mail->SMTPDebug = 1;
// 使用smtp鉴权方式发送邮件
            $mail->isSMTP();
// smtp需要鉴权 这个必须是true
            $mail->SMTPAuth = true;
// 链接qq域名邮箱的服务器地址
            $mail->Host = 'smtp.qq.com';
// 设置使用ssl加密方式登录鉴权
            $mail->SMTPSecure = 'ssl';
// 设置ssl连接smtp服务器的远程服务器端口号
            $mail->Port = 465;
// 设置发送的邮件的编码
            $mail->CharSet = 'UTF-8';
// 设置发件人昵称 显示在收件人邮件的发件人邮箱地址前的发件人姓名
            $mail->FromName = 'ssss';
// smtp登录的账号 QQ邮箱即可
            $mail->Username = '891841626@qq.com';
// smtp登录的密码 使用生成的授权码
            $mail->Password = 'srdunpdhnzntbfei';
// 设置发件人邮箱地址 同登录账号
            $mail->From = '891841626@qq.com';
// 邮件正文是否为html编码 注意此处是一个方法
            $mail->isHTML(true);
// 设置收件人邮箱地址
            $mail->addAddress('784617405@qq.com');
// 添加多个收件人 则多次调用方法即可
       //     $mail->addAddress('87654321@163.com');
// 添加该邮件的主题
            $mail->Subject = '邮件主题';
// 添加邮件正文
            $mail->Body = '<h1>Hello World</h1>';
// 为该邮件添加附件
         //   $mail->addAttachment('./example.pdf');
// 发送邮件 返回状态
            $status = $mail->send();

            echo 'Message has been sent';








            /*


            //Server settings
            $mail->SMTPDebug = 2;                                 // Enable verbose debug output
            $mail->isSMTP();
            $mail->CharSet='utf-8';                                 //设置字符集// Set mailer to use SMTP
            $mail->Host = $config['mail']['host'];                          // Specify main and backup SMTP servers
            $mail->SMTPAuth = false;                                 // Enable SMTP authentication
            $mail->Username = $config['mail']['userName'];                  // SMTP username
            $mail->Password = $config['mail']['password'];                           // SMTP password
            $mail->SMTPSecure = 'ssl';                              // Enable TLS encryption, `ssl` also accepted
            $mail->Port = $config['mail']['port'];                                    // TCP port to connect to

            //Recipients
           // $mail->setFrom('from@example.com', 'Mailer');
            $mail->addAddress('gaoxinyu@imooc.com', 'Joe User');     // Add a recipient
        //    $mail->addAddress('gaoxinyu@imooc.com');               // Name is optional


            //Attachments
            $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
            $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

            //Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = 'Here is the subject';
            $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $res = $mail->send();*/
          //  var_dump($res);
//            echo 'Message has been sent';
        } catch (Exception $e) {
            echo '<pre>';print_r($mail);
            echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
        }
    }
}
