<?php

namespace app\controllers;

use app\commond\Constants;
use app\commond\helps;
use app\models\AUser;
use Yii;
use app\models\AModel;


class ModelsController extends BasicController
{

    public function init()
    {
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
            ->where(['status'=>0,'project_id'=>0])->asArray()->all();
        
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
    public function actionAdd()
    {
        $this->isPost();
        $name = $this->getParam('name',true);
        $pid  = $this->getParam('pid',false);
        $type  = $this->getParam('type',false);

        $projectId = $this->getParam('projectId',false);
        $createUid = $this->getParam('userId',true);
        $remark = $this->getParam('remark',true);

        $level = 1;

        //模型带上层级
        if (!empty($pid) && $type == 0) {
            $level = AModel::find()->select('level')
                ->where(['id'=>$pid,'type'=>0,'status'=>0])->scalar();
            $level = intval($level) + 1;
        }

        $Obj = new AModel();
        $Obj->name = $name;
        $Obj->create_time = time();
        $Obj->project_id = empty($projectId) ? 0 : $projectId;
        $Obj->create_uid = $createUid;
        $Obj->type = empty($type) ? '0' : (string)$type;
        $Obj->pid = empty($pid) ? 0 : $pid;
        $Obj->level = $level;
        $Obj->remark = $remark;

        if ($Obj->insert()) {
            if ($type == 0) {
                $msg = '创建模板:'.$name;
                helps::writeLog(Constants::OPERATION_MODEL,$msg,$createUid);
            } else {
                $msg = '创建目录:'.$name;
                helps::writeLog(Constants::OPERATION_CATE,$msg,$createUid);
            }

            $this->Success(['id'=>$Obj->attributes['id']]);
        }
        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }

    /**
     * 编辑
     */
    public function actionEdit()
    {
        $this->isPost();
        $id   = $this->getParam('id',true);
        $name = $this->getParam('name',true);
        $uid  = $this->getParam('userId',true);

        $Obj = AModel::findOne($id);

        if (!$Obj) {
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }
        $oldName = $Obj->name;
        $Obj->name = $name;
        $Obj->update_time = time();
        if ($Obj->save(false)) {
            if ($Obj->type == 0) {
                $msg = '编辑模板:'.$oldName.'改成'.$name;
                helps::writeLog(Constants::OPERATION_MODEL,$msg,$uid);
            } else {
                $msg = '编辑目录:'.$oldName.'改成'.$name;
                helps::writeLog(Constants::OPERATION_CATE,$msg,$uid);
            }
            $this->Success();
        }

        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }


    /**
     * 删除
     */
    public function actionDel()
    {
        $this->isPost();
        $id  = $this->getParam('id',true);
        $uid = $this->getParam('userId',true);
        $Obj = AModel::findOne($id);

        if (!$Obj) {
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }

        $Obj->status = -1;
        if ($Obj->save(false)) {

            if ($Obj->type == 0) {
                $msg = '删除模板:'.$Obj->name;
                helps::writeLog(Constants::OPERATION_MODEL,$msg,$uid);
            } else {
                $msg = '删除目录:'.$Obj->name;
                helps::writeLog(Constants::OPERATION_CATE,$msg,$uid);
            }
            $this->Success();
        }

        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }


    /**
     * 模块备注添加
     */
    public function actionAddRemark(){

        $this->isPost();
        $modelId = $this->getParam('modelId',true);
        $remark  = $this->getParam('remark',true);
        $uid     = $this->getParam('userId',true);

        $model = AModel::findOne(['id'=>$modelId,'status'=>0]);

        if (!$model) {
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }

        $model->remark      = $remark;
        $model->update_time = time();

        if ($model->save(false)) {

            $msg = '模块:'.$model->name.'添加备注:'.$remark;
            helps::writeLog(Constants::OPERATION_MODEL,$msg,$uid);
            $this->Success();
        }

        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }
}
