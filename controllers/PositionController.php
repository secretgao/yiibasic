<?php

namespace app\controllers;

use app\commond\Constants;
use app\models\APosition;
use app\models\AUser;
use Yii;


class PositionController extends BasicController
{
        
    
    public function init(){
       parent::init();
    }

    /**
     * http://www.api.com/position/index
     * 获取所有职位
     */
    public function actionIndex(){

        $parent = APosition::getAll();
        if (! $parent) {
            $this->Error();
        }
        foreach ($parent as &$item){
            $item['children'] = empty(APosition::getChildren($item['id'])) ? [] : APosition::getChildren($item['id']);
        }

        $this->Success(['data'=>$parent]);

    }

    /**
     *  部门添加
     * http://www.api.com/position/add
     */
    public function actionAdd(){
       // $this->isPost();
        $positionName = $this->getParam('name',true);
        $pid          = $this->getParam('pid',false);

        $positionObj = new APosition();
        $positionObj->name = $positionName;
        if (!empty($pid)){
            $positionObj->pid = $pid;

        }
        $positionObj->create_time = time();

        if ($positionObj->insert()) {
            $this->Success();
        }

        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }

    /**
     * 部门编辑
     */
    public function actionEdit(){
        // $this->isPost();
        $id          = $this->getParam('id',true);
        $positionName = $this->getParam('name',true);

        $positionObj = APosition::findOne($id);

        if (!$positionObj){
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);

        }

        $positionObj->name = $positionName;
        if ($positionObj->save(false)) {
            $this->Success();
        }

        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }


    /**
     * 部门删除
     */
    public function actionDel(){
        // $this->isPost();
        $id          = $this->getParam('id',true);

        $positionObj = APosition::findOne($id);

        if (!$positionObj){
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);

        }

        $positionObj->status =-1;
        if ($positionObj->save(false)) {
            $this->Success();
        }

        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }
}
