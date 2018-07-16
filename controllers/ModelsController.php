<?php

namespace app\controllers;

use app\commond\Constants;
use app\models\APosition;
use app\models\AUser;
use Yii;
use app\models\AModel;


class ModelsController extends BasicController
{
        
    
    public function init(){
       parent::init();
    }

    /**
     * http://www.api.com/position/index
     * 获取
     */
    public function actionIndex(){

        $id   = $this->getParam('id',true);
        $Obj = AModel::findOne($id);

        if (!$Obj){
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);          
        }
        
        
        $this->Success(['data'=>$Obj->content]);

    }

    /**
     *  添加
     * http://www.api.com/models/add
     */
    public function actionAdd(){
       // $this->isPost();
        $data = $this->getParam('data',true);




        $Obj = new AModel();
        $Obj->content = $data;
        $Obj->create_time = time();

        if ($Obj->insert()) {
            $this->Success();
        }

        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }

    /**
     * 编辑
     */
    public function actionEdit(){
        $this->isPost();
        $id      = $this->getParam('id',true);
        $content = $this->getParam('data',true);

        $Obj = AModel::findOne($id);

        if (!$Obj){
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);

        }

        $Obj->content = $content;
        $Obj->update_time = time();
        if ($Obj->save(false)) {
            $this->Success();
        }

        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }


    /**
     * 删除
     */
    public function actionDel(){
        $this->isPost();
        $id          = $this->getParam('id',true);
        $Obj = AModel::findOne($id);

        if (!$Obj){
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }

        $Obj->status =-1;
        if ($Obj->save(false)) {
            $this->Success();
        }

        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }
}
