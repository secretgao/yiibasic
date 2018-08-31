<?php

namespace app\controllers;


use app\commond\Constants;
use app\commond\Email;
use app\commond\PHPMailer\PHPMailer;
use app\models\ASendEmail;
use Yii;
use app\models\AProject;
use app\commond\helps;
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




    /**
     * 打包项目发送邮件
     */
    public function actionSend()
    {
        $email = $this->getParam('email',true);
        $projectId = $this->getParam('projectId',true);

        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->Error(Constants::EMAIL_IS_ERROR,Constants::$error_message[Constants::EMAIL_IS_ERROR]);
        }

        $project = AProject::find()
            ->where(['id'=>$projectId])
            ->andWhere(['<>','status',4])
            ->asArray()->one();


        if (empty($project)) {
            $this->Error(Constants::PROJECT_NOT_FOUND,Constants::$error_message[Constants::PROJECT_NOT_FOUND]);
        }

        $projectName = $project['name'].'-'.date('YmdHis',$project['create_time']);

        $sendEmail = new ASendEmail();
        $sendEmail->project_id = $projectId;
        $sendEmail->address = $email;
        $sendEmail->create_time = time();
        $sendEmail->project_name = $projectName;

        if ($sendEmail->save(false)) {
             $this->Success();
        } else {
            $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);

        }

    }


    /**
     * 打包项目
     */
    public function actionPack()
    {

        $sendEmail = ASendEmail::find()
            ->where(['status'=>0])->orderBy('id ASC')->one();

        if (empty($sendEmail)){
            exit('not found send email ');
        }

        $projectId = $sendEmail->project_id;
        $project = AProject::find()->select('name,create_time')
            ->where(['id'=>$projectId])->asArray()->one();

        $projectName = $project['name'].'-'.date('YmdHis',$project['create_time']);

        //  $projectName = iconv("UTF-8", "GBK", $projectName);   //汉字转码 防止乱码
        //$projectPath = $dir.DIRECTORY_SEPARATOR.$projectName;
        $projectPath = '.'.DIRECTORY_SEPARATOR.$projectName;


        //创建项目根目录
        if (!is_dir($projectPath)) {
            mkdir($projectPath,0777,true);
        }

        //获取项目所有的模板和目录
        $allStep = helps::getProjectModelAndCateLog($projectId);
        //获取所有文件
        $allfile = helps::getProjectAllFile($projectId,1);
        //echo '<pre>';print_r($allStep);
        //项目预览 复制到打包文件中
        $preview = '.'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'tree';
        helps::xCopy($preview, $projectPath);
        //生成预览数据格式
        $jsonData = json_encode(array_merge($allStep,$allfile));
        $json = " var json = ".$jsonData;
        file_put_contents($projectPath.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'data.js',
            $json);
        //生成个人工作日志
        helps::createUserDiary($projectPath,$projectId);
        //创建文件夹 把文件复制到指定目录下
        helps::createDirectory($projectPath,$allStep,$allfile,0);

        //打包
        $zip = new \ZipArchive();
        $zipName = $projectName.'.zip';
        $rec = fopen($zipName,'wb');
        fclose($rec);

        if($zip->open($zipName, \ZipArchive::OVERWRITE)=== TRUE){
            $this->addFileToZip($projectPath, $zip);
            //调用方法，对要打包的根目录进行操作，并将ZipArchive的对象传递给方法
            $zip->close(); //关闭处理的zip文件
        }

        if (!file_exists($zipName)){
            $this->Error(Constants::PROJECT_PACK_FAIL,Constants::$error_message[Constants::PROJECT_PACK_FAIL]);
        }

        $sendEmail->status = 1;
        $sendEmail->pack_time = time();
        $sendEmail->project_file = $zipName;
        if($sendEmail->save(false)) {
            error_log('pack_file'.date('Y-m-d H:i:s').'#####'.$zipName
                .PHP_EOL,3,'/tmp/packfile.log');
            //@unlink($projectPath);
            $this->Success();
        } else {
            $this->Error(Constants::PROJECT_PACK_FAIL,Constants::$error_message[Constants::PROJECT_PACK_FAIL]);
        }

    }

    private  function addFileToZip($path,$zip) {
        $handler = opendir($path); //打开当前文件夹由$path指定。
        while (($filename=readdir($handler))!==false) {
            //文件夹文件名字为'.'和‘..'，不要对他们进行操作
            if ($filename != "." && $filename != "..") {
                // 如果读取的某个对象是文件夹，则递归
                if (is_dir($path."/".$filename)) {
                    $this->addFileToZip($path."/".$filename, $zip);
                } else{ //将文件加入zip对象
                    $zip->addFile($path."/".$filename);
                }
            }
        }
        @closedir($path);
    }


    public function actionSendEmail(){
        $config = YII::$app->params;

        $sendEmail = ASendEmail::find()
            ->where(['status'=>1])->orderBy('id ASC')->one();

        if (empty($sendEmail)){
            exit('not found send email ');
        }
        $projectName =$sendEmail->project_name;

        $email = $sendEmail->address;
        $zipName = $sendEmail->project_file;


        $mail = new PHPMailer(true);
        try {

            $mail = new PHPMailer();
            // 是否启用smtp的debug进行调试 开发环境建议开启 生产环境注释掉即可 默认关闭debug调试模式
            // $mail->SMTPDebug = 1;
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
            $mail->FromName = 'sycalc123';
            // smtp登录的账号 QQ邮箱即可
            $mail->Username = $config['mail']['username'];
            // smtp登录的密码 使用生成的授权码
            $mail->Password = $config['mail']['password'];
            // 设置发件人邮箱地址 同登录账号
            $mail->From = $config['mail']['username'];
            // 邮件正文是否为html编码 注意此处是一个方法
            $mail->isHTML(true);
            // 设置收件人邮箱地址
            $mail->addAddress($email);
            // 添加多个收件人 则多次调用方法即可
            //     $mail->addAddress('87654321@163.com');
            // 添加该邮件的主题
            $mail->Subject = $projectName;
            // 添加邮件正文
            $mail->Body = 'hello';
            // 为该邮件添加附件
            $mail->addAttachment($zipName,$projectName.'.zip');
            // 发送邮件 返回状态
            $status = $mail->send();
            @unlink($zipName);

            $sendEmail->status = 2;
            $sendEmail->send_time = time();
            if($sendEmail->save(false)) {
                error_log('send_file'.date('Y-m-d H:i:s').'#####'.$zipName
                    .PHP_EOL,3,'/tmp/send_file.log');
                $this->Success();
            }

            // echo 'Message has been sent';

        } catch (Exception $e) {
            $this->Error(Constants::RET_ERROR, 'Message could not be sent. Mailer Error: '.$mail->ErrorInfo);
            // echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
        }
    }

}
