<?php

namespace app\controllers;

use app\commond\Constants;
use app\models\APosition;
use app\models\AUser;
use Yii;
use app\models\AModel;

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
