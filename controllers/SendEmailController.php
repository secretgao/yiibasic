<?php

namespace app\controllers;


use app\commond\Constants;
use app\commond\Email;
use app\commond\PHPMailer\PHPMailer;
use Yii;


use yii\base\Exception;

/**
 * 发送邮件
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

        $fileAdress = $this->getParam('file',true);
        $email = $this->getParam('email',true);
        var_dump($fileAdress);
        exit();
        $mail = new PHPMailer(true);
        try {

            $mail = new PHPMailer();
            // 是否启用smtp的debug进行调试 开发环境建议开启 生产环境注释掉即可 默认关闭debug调试模式
            $mail->SMTPDebug = 1;
            // 使用smtp鉴权方式发送邮件
            $mail->isSMTP();
            // smtp需要鉴权 这个必须是true
            $mail->SMTPAuth = true;
            // 链接qq域名邮箱的服务器地址
            $mail->Host =$config['mail']['host'] ;
            // 设置使用ssl加密方式登录鉴权
            $mail->SMTPSecure = 'ssl';
            // 设置ssl连接smtp服务器的远程服务器端口号
            $mail->Port = $config['mail']['port'];
            // 设置发送的邮件的编码
            $mail->CharSet = 'UTF-8';
            // 设置发件人昵称 显示在收件人邮件的发件人邮箱地址前的发件人姓名
            $mail->FromName = 'ssss';
            // smtp登录的账号 QQ邮箱即可
            $mail->Username = $config['mail']['username'];
            // smtp登录的密码 使用生成的授权码
            $mail->Password = $config['mail']['password'];
            // 设置发件人邮箱地址 同登录账号
            $mail->From = '891841626@qq.com';
            // 邮件正文是否为html编码 注意此处是一个方法
            $mail->isHTML(true);
            // 设置收件人邮箱地址
            $mail->addAddress($email);
            // 添加多个收件人 则多次调用方法即可
       //     $mail->addAddress('87654321@163.com');
            // 添加该邮件的主题
            $mail->Subject = '邮件主题';
            // 添加邮件正文
            $mail->Body = '<h1>Hello World</h1>';
            // 为该邮件添加附件
            $mail->addAttachment($fileAdress);
            // 发送邮件 返回状态
            $status = $mail->send();
            $this->Success();
           // echo 'Message has been sent';
            @unlink($fileAdress);
        } catch (Exception $e) {
            $this->Error(Constants::RET_ERROR, 'Message could not be sent. Mailer Error: '.$mail->ErrorInfo);
           // echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
        }
    }
}
