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
            //Server settings
            $mail->SMTPDebug = 2;                                 // Enable verbose debug output
            $mail->isSMTP();
            $mail->CharSet='utf-8';                                 //设置字符集// Set mailer to use SMTP
            $mail->Host = $config['mail']['host'];                          // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                                 // Enable SMTP authentication
            $mail->Username = $config['mail']['userName'];                  // SMTP username
            $mail->Password = $config['mail']['password'];                           // SMTP password
            $mail->SMTPSecure = 'tls';                              // Enable TLS encryption, `ssl` also accepted
            $mail->Port = $config['mail']['port'];                                    // TCP port to connect to

            //Recipients
           // $mail->setFrom('from@example.com', 'Mailer');
            $mail->addAddress('gaoxinyu@imooc.com', 'Joe User');     // Add a recipient
            $mail->addAddress('gaoxinyu@imooc.com');               // Name is optional


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
            echo '<pre>';print_r($mail);
            echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
        }
    }
}
