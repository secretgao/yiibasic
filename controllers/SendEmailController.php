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

        $mail = new Email();
        $mail->setTo("a@a.com"); //收件人

        $mail->setFrom("gaoxinyu@imooc.com");//发件人
        $mail->setSubject("subject") ; //主题
        $mail->setText('文本格式') ;//发送文本格式也可以是变量
        $mail->setHTML('html格式') ;//发送html格式也可以是变量
        $mail->setAttachments('vvv') ;//添加附件,需表明路径
        $mail->send(); //发送邮件
        /*
        $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
        try {
            //Server settings
            $mail->SMTPDebug = 2;                                 // Enable verbose debug output
            $mail->isSMTP();
            $mail->CharSet='utf-8';     //设置字符集// Set mailer to use SMTP
            $mail->Host = SMTP_SERVER;  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = SMTP_USER_NAME;                 // SMTP username
            $mail->Password = SMTP_USER_PASS;                           // SMTP password
            $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = SMTP_SERVER_PORT;                                    // TCP port to connect to

            //Recipients
            $mail->setFrom('from@example.com', 'Mailer');
            $mail->addAddress('joe@example.net', 'Joe User');     // Add a recipient
            $mail->addAddress('ellen@example.com');               // Name is optional
            $mail->addReplyTo('info@example.com', 'Information');
            $mail->addCC('cc@example.com');
            $mail->addBCC('bcc@example.com');

            //Attachments
            $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
            $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

            //Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = 'Here is the subject';
            $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
        }*/
    }
}
