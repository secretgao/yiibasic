<?php

namespace app\controllers;

use app\commond\Constants;
use app\commond\helps;
use app\models\AProject;
use app\models\AProjectModel;
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
        if (!empty($pid)) {
            $level = AModel::find()->select('level')
                ->where(['id'=>$pid,'type'=>0,'status'=>0])->scalar();
            $level = intval($level) + 1;
        }
        $transaction= Yii::$app->db->beginTransaction();
        try {
            //创建模板
            if ($type == 0) {
                $Obj = new AModel();
                $Obj->name = $name;
                $Obj->create_time = time();
                $Obj->project_id =  0 ;
                $Obj->create_uid = $createUid;
                $Obj->type = '0' ;
                $Obj->pid = empty($pid) ? 0 : $pid;
                $Obj->level = $level;
                $Obj->remark = $remark;
                if (!$Obj->save(false)) {
                    $this->Error(Constants::RET_ERROR,$Obj->getErrors());
                }
            } else {
            //创建目录
                //创建项目所属目录不是模板
                $Obj = new AModel();
                $Obj->name = $name;
                $Obj->create_time = time();
                $Obj->project_id = $projectId;
                $Obj->create_uid = $createUid;
                $Obj->type = '1';
                $Obj->pid = empty($pid) ? 0 : $pid;
                $Obj->level = $level;
                $Obj->remark = $remark;
                if (!$Obj->save(false)) {
                    $this->Error(Constants::RET_ERROR,$Obj->getErrors());
                }
                $projectModel = new AProjectModel();
                $projectModel->project_id = $projectId;
                $projectModel->model_id = $Obj->attributes['id'];
                $projectModel->model_pid = $Obj->pid;
                $projectModel->create_time = time();
                $projectModel->level = $level;
                $projectModel->type = '1';
                if (!$projectModel->save(false)) {
                    $this->Error(Constants::RET_ERROR,$projectModel->getErrors());
                }
            }

            $transaction->commit();
            if ($type == 0) {
                $msg = '创建模板:'.$name;
                helps::writeLog(Constants::OPERATION_MODEL,$msg,$createUid);
            } else {
                $msg = '创建目录:'.$name;
                helps::writeLog(Constants::OPERATION_CATE,$msg,$createUid);
            }
            $this->Success(['id'=>$Obj->attributes['id']]);
        } catch (\Exception $e) {
            //如果操作失败, 数据回滚
            $transaction->rollback();
            $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
        }

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

    /**
     * 获取一级分类
     * @return array
     */
    public function actionFirstIndex()
    {
        // header("Content-type: text/html; charset=utf-8");
         //获取一级分类
         $data = AModel::getFirstModels();
         //查询所属一级分类的项目
         foreach ($data as $key=>$item) {

          $projectId = AProjectModel::accordingToModelIdGetProjectId($item['id']);
          $data[$key]['projects'] = count($projectId);
         }
         //echo '<pre>';print_r($data);exit();
         $this->Success(['data'=>$data]);

    }


    /**
     * 删除项目目录或者模板
     */
    public function actionDelProjectModel()
    {
        $this->isPost();
        $id  = $this->getParam('id',true);
        $projectId  = $this->getParam('projectId',true);

        $project = AProject::find()
            ->where(['id'=>$projectId])
            ->andwhere(['<>','status',4])
            ->one();
        //判断项目 状态是否正常
        if (!$project) {
            $this->Error(Constants::PROJECT_NOT_FOUND,Constants::$error_message[Constants::PROJECT_NOT_FOUND]);
        }

        // 模板是否存在
        $Obj = AProjectModel::findOne(['project_id'=>$projectId,'model_id'=>$id,'status'=>0]);
        if (!$Obj) {
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }
        $type = $Obj->type;
        $directory = 0;
        if ($type == 0){
            // 判断是否有子模板
            $sonModel = AProjectModel::findOne(['project_id'=>$projectId,'model_pid'=>$id,'status'=>0]);
            if ($sonModel) {
                $this->Error(Constants::PROJECT_MODEL_SON,Constants::$error_message[Constants::PROJECT_MODEL_SON]);
            }
            $directory = AProjectModel::find()->where(['project_id'=>$projectId,'model_pid'=>$id,'status'=>0,'type'=>1])->count();
        } else {
            // 判断是否有子模板
            $sonModel = AProjectModel::findOne(['project_id'=>$projectId,'model_id'=>$id,'status'=>0,'type'=>1]);
            if ($sonModel) {
                $directory = AProjectModel::find()->where(['project_id'=>$projectId,'model_pid'=>$id,'status'=>0,'type'=>1])->count();
            } else {
                $directory = AProjectModel::find()->where(['project_id'=>$projectId,'model_id'=>$id,'status'=>0,'type'=>1])->count();
            }
        }
        $fileNum = $project->file_agree_num;
        $Obj->status = -1;
        if ($Obj->save(false)) {
            $project->file_agree_num = $fileNum + $directory;
            $project->save(false);
            if ($type == 0) { // 模板
                $res = AModel::find()->where(['pid'=>$id])->asArray()->all();
                helps::CreateProjectRecursion($res);
                $modelId = [];
                foreach ($res as $item){
                    $modelId[]= $item['id'];
                }
                AProjectModel::updateAll(['status'=>-1],['project_id'=>$projectId,'model_id'=>$modelId]);
            }
            $this->Success();
        }

        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }
}
