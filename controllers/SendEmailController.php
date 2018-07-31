<?php

namespace app\controllers;


use app\commond\Email;
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
        //主題
        $subject = "test send email";

        //收件人
        $sendto = 'gaoxinyu@imooc.com';

        //發件人
        $replyto = '891841626@qq.com';

        //內容 www.jbxue.com
        $message = "test send email content";

        //附件
        $filename = 'test.jpg';

        //附件類別
        $mimetype = "image/jpeg";
        $excelname ='aaa.jpg';

        $mailfile = new Email($subject,$sendto,$replyto,$message,$filename,$excelname,$mimetype);
        $mailfile->sendfile();
    }
}
