<?php

namespace app\controllers;

use app\components\Aliyunoss;

use app\models\AFile;
use Yii;
use app\commond\Constants;
use OSS\OssClient;
use OSS\Core\OssException;
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


}
