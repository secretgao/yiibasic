<?php

namespace app\controllers;

use app\commond\Constants;
use app\commond\helps;
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
       // $this->isPost();
        $uid = $this->getParam('userId',true);
        $data = AModel::find()->select('id,name,pid')
            ->where(['status'=>0,'create_uid'=>$uid])->asArray()->all();
        
        if (empty($data)){
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);    
        }     
       
        $new = Helps::getson($data,0,1);             
        $result = Helps::make_tree($new);   
        $this->Success(['data'=>$result]);
    }

    /**
     *  添加
     * http://www.api.com/models/add
     */
    public function actionAdd(){
    
        //$this->isPost();
        $name = $this->getParam('name',true);
        $pid  = $this->getParam('pid',false,0);       
     
       // $projectId = $this->getParam('project_id',false,0);
        $createUid = $this->getParam('userId',true);
        $Obj = new AModel();
        $Obj->name = $name;
        $Obj->create_time = time();
      //  $Obj->project_id = $projectId;
        $Obj->create_uid = $createUid;
        $Obj->pid = empty($pid) ? 0 : $pid;
        
       
        if ($Obj->insert()) {
            $this->Success(['id'=>$Obj->attributes['id']]);
        }

        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }

    /**
     * 编辑
     */
    public function actionEdit(){
        $this->isPost();
        $id      = $this->getParam('id',true);
        $name = $this->getParam('name',true);

        $Obj = AModel::findOne($id);

        if (!$Obj){
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);

        }

        $Obj->name = $name;
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

        $Obj->status = -1;
        if ($Obj->save(false)) {
            $this->Success();
        }

        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }

}
