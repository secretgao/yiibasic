<?php

namespace app\controllers;

use app\commond\Aliyunoss;

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
        
       
        YII::$app->Aliyunoss->test();
        
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


    /**
     * 删除
     */
    public function actionDel(){
       
        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }


}
