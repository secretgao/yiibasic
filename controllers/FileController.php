<?php

namespace app\controllers;

use app\components\Aliyunoss;

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
        
    
    public function init(){
       parent::init();
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
        $accessKeyId = "LTAI529J1kb66aY2";
        $accessKeySecret = "TveIiq6hkO24UGWymYZ50aVR8MMj16";
        // Endpoint以杭州为例，其它Region请按实际情况填写。
        $endpoint = "http://oss-cn-hangzhou.aliyuncs.com";
        // 存储空间名称
        $bucket= "sycalcs";
        // 文件名称
        $object = "test.log";
        // 文件内容
        $content = "Hello OSS";
        try{
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $ossClient->putObject($bucket, $object, $content);
        } catch(OssException $e) {
            printf(__FUNCTION__ . ": FAILED\n");
            printf($e->getMessage() . "\n");
            return;
        }
        print(__FUNCTION__ . ": OK" . "\n");
     

    }


    /**
     * 创建项目
     */
    
    public function actionAdd(){
     
        
    }
    
    /**
     * 编辑
     */
    public function actionEdit(){
   
    }


    
    public function actionLookFileList(){
        
        
    }


}
