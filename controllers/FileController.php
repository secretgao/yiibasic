<?php

namespace app\controllers;

use app\components\Aliyunoss;

use app\models\AFile;
use Yii;
use app\commond\Constants;
use app\commond\fileupload;
/**
 * 文件操作
 * @author Administrator
 *
 */

class FileController extends BasicController
{

    public $mainPath = '';
    public $typePath = '';
    
    public function init(){
       parent::init();
    }

    /**
     * 根据uid  type 类型
     * 返回主目录路径和子目录
     * @param $uid
     * @param $type
     */
    public function getPath($uid,$type){

        return $result = [
             'main'=>md5($uid),
             'type'=>md5($uid.$type)
        ];
    }
    /**
     * http://www.api.com/position/index
     * 获取
     */
    public function actionIndex(){
        
      /*   $r=  \YII::$app->Aliyunoss->listObjects();//\YII::$app->Aliyunoss->createObjectDir('testaa');
        echo '<pre>';print_r($r);
        exit(); */
       // $file = '/Users/gaoxinyu/Downloads/52317.jpg';
        $file = '/usr/local/var/www/basic/README.md';

        $re = \YII::$app->Aliyunoss->upload('gggggg.log',$file);
       // $re = \YII::$app->Aliyunoss->multiuploadFile('gggggg.mp4','C:\Users\Administrator\Documents\Tencent Files\891841626\FileRecv\1531814365942920_1531817438125471.mp4');

       
        echo '<pre>';print_r($re);
        echo '<hr>';
        echo $re['oss-stringtosign'];
        exit();


    }


    /**
     * 上传
     * /usr/local/var/www/basic/README.md
     */
    
    public function actionUpload(){
//`type` tinyint(3) DEFAULT NULL COMMENT '文件类型 1图片 2视频 3附件 4 笔记'
        $uid = $this->getParam('userId',true);
        $type = $this->getParam('type',true);
        $filePath = $this->getParam('filePath',true);
        $catalogId = $this->getParam('catalogId',true);
        $projectId = $this->getParam('projectId',true);
        $ext = $this->getParam('ext',true);
        $fileName = $this->getParam('fileName',true);
        $mainPath  = md5($uid);
        $typePath = '/'.md5($uid.$type);
        $fileNameExist = AFile::find()->select('id')->where(['name'=>$fileName,'status'=>0])->scalar();

        if ($fileNameExist){
          $this->Error(Constants::FILES_ALREADY_EXIST,Constants::$error_message[Constants::FILES_ALREADY_EXIST]);
        }

        $uploadRes = \YII::$app->Aliyunoss->upload($fileName.$ext,$filePath);

        if ($uploadRes['info'] && $uploadRes['info']['http_code'] == 200) {
            $file = new AFile();
            $file->uid = $uid;
            $file->type = $type;
            $file->name = $fileName;
            $file->ext = $ext;
            $file->path = $filePath;
            $file->create_time = time();
            $file->project_id = $projectId;

            if ($file->insert()) {
                $this->Success();
            }
        }

        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }
    
    /**
     * 下载
     */
    public function actionEdit(){
   
    }


    
    public function actionFileList(){
        $uid = $this->getParam('userId',true);

        $columns = '*';
        $file = AFile::find()->select($columns)->where(['uid'=>$uid])->asArray()->all();

        if (!$file){
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }
        $this->Success(['data'=>$file]);

    }

    public function actionDelFile()
    {
        $uid = $this->getParam('userId',true);
        $fileId = $this->getParam('fileId',true);

        $file = AFile::findOne(['id'=>$fileId,'uid'=>$uid,'status'=>0]);

        if (!$file){
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }
        $delRes = \YII::$app->Aliyunoss->delete($file->name.$file->ext);

        $file->status = 1;
        if ($file->save(false)){
            $this->Success();
        }
        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);

    }


    public function actionTest()
    {
        return $this->render('test');
    }
/*
    public function actionUploads()
    {

       // echo '<pre>';print_r($_FILES);
        if (PHP_OS == 'Linux'){
            error_log('test--start_Time'.date('Y-m-d H:i:s').PHP_EOL,3,'/tmp/test.log');
        }


        $ext  = $this->getParam('ext',true);
        if (empty($_FILES)){
            $this->Error();
        }
       // $a = '{"file":{"name":".txt","type":"multipart\/form-data","tmp_name":"\/tmp\/phpFGIWWm","error":0,"size":165199}';

        $image = $_FILES['file']["tmp_name"];
        $fp = fopen($image, "r");


        $file = fread($fp, $_FILES['file']["size"]); //二进制数据流
        // error_log('test--post'.json_encode($_POST).PHP_EOL,3,'/tmp/test.log');
       //  error_log('test--file'.json_encode($_FILES).PHP_EOL,3,'/tmp/test.log');
        //保存地址
        if (PHP_OS == 'Linux'){
            error_log('test--post'.json_encode($_POST).PHP_EOL,3,'/tmp/test.log');
            error_log('test--file'.json_encode($_FILES).PHP_EOL,3,'/tmp/test.log');
        }

            $content = file_get_contents('php://input');    // 不需要php.ini设置，内存压力小
        if (PHP_OS == 'Linux'){
            error_log('test--cont'.json_encode($content).PHP_EOL,3,'/tmp/test.log');
        }


        $imgDir = './Uploads/';

        //要生成的图片名字

        $filename = md5(time().mt_rand(10, 99)).$ext; //新图片名称

        $newFilePath = $imgDir.$filename;

        $data = $file;

        $newFile = fopen($newFilePath,"w"); //打开文件准备写入

        fwrite($newFile,$data); //写入二进制流到文件

        fclose($newFile); //关闭文件

        $this->Success();
    }
*/
    public function actionUploads()
    {

        $userId = $this->getParam('userId',true);
        $projectId = $this->getParam('projectId',true);
        $catalogId = $this->getParam('catalogId',true);
        $type = $this->getParam('type',true);
        $fileUpload = new fileupload();
        $fileInfo = $fileUpload->getFileInfo($userId);

        if (isset($fileInfo['status'])){
            $file = new AFile();
            $file->uid = $userId;
            $file->type = $type;
            $file->name = $fileInfo['fileInfo']['name'];
            $file->ext = $fileInfo['fileInfo']['ext'];
            $file->create_time = time();
            $file->path = $fileInfo['fileInfo']['path'];
            $file->project_id = $projectId;
            $file->catalog_id = $catalogId;

            if ($file->save()){
                $this->Success($fileInfo);
            } else {
                $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
            }
        }
        $this->Error($fileInfo['errorId'],$fileInfo['errorMsg']);

    }
}
