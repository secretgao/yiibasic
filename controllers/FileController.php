<?php

namespace app\controllers;

use app\components\Aliyunoss;

use app\models\AFile;
use Yii;

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
        $re = \YII::$app->Aliyunoss->upload('gggggg.log','C:\offline_FtnInfo.txt');
       // $re = \YII::$app->Aliyunoss->multiuploadFile('gggggg.mp4','C:\Users\Administrator\Documents\Tencent Files\891841626\FileRecv\1531814365942920_1531817438125471.mp4');
       
       
        echo '<pre>';print_r($re);
        echo '<hr>';
        echo $re['oss-stringtosign'];
        exit();


    }


    /**
     * 上传
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
        $fileNameExist = AFile::find()->select('id')->where(['name'=>$fileName])->scalar();

        if ($fileNameExist){
          $this->Error(Constants::FILES_ALREADY_EXIST,Constants::$error_message[Constants::FILES_ALREADY_EXIST]);
        }
/*
 * `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT '0' COMMENT '用户id',
  `type` tinyint(3) unsigned NOT NULL COMMENT '文件类型 1图片 2视频 3附件 4 笔记',
  `name` varchar(50) DEFAULT NULL COMMENT '文件名',
  `ext` varchar(5) DEFAULT '' COMMENT '文件后缀',
  `status` tinyint(3) DEFAULT '0' COMMENT '文件状态 0 正常  1删除',
  `create_time` int(11) DEFAULT '0' COMMENT '创建时间',
  `path` varchar(200) DEFAULT '' COMMENT '文件路径',

 */

        $uploadRes = \YII::$app->Aliyunoss->upload('gggggg.log','C:\offline_FtnInfo.txt');

        //if ($uploadRes['info'])
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

        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);

        exit();

        
    }
    
    /**
     * 下载
     */
    public function actionEdit(){
   
    }


    
    public function actionLookFileList(){
        
        
    }


}
