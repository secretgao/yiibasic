<?php

namespace app\controllers;


use app\models\APersonalLog;
use app\models\APosition;
use app\models\APositionApply;
use app\models\AUser;
use Yii;
use app\commond\Constants;
use app\commond\helps;
use yii\base\Exception;

/**
 * 个人工作日志操作
 * @author Administrator
 *
 */

class LogController extends BasicController
{

    public function init(){
       parent::init();
    }


    /**
     * 个人工作日志添加
     * @return array
     */
    public function actionWrite()
    {
        $userId = $this->getParam('userId',true);
        $projectId = $this->getParam('projectId',true);
        $logContent = $this->getParam('log_content',true);

        $perObj = new APersonalLog();
        $perObj->uid = $userId;
        $perObj->project_id = $projectId;
        $perObj->content = $logContent;
        $perObj->create_time = time();

        if ($perObj->save()) {
            $this->Success();
        }

        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);

    }

    /**
     * 设置个人日志状态
     *
     */
    public function actionEdit()
    {
        $userId = $this->getParam('userId',true);
        $projectId = $this->getParam('projectId',true);
        $logContent = $this->getParam('log_content',false);
        $log_id = $this->getParam('log_id',true);
        $state = $this->getParam('state',true);

        $perLog = APersonalLog::findOne(['id'=>$log_id,'uid'=>$userId,'project_id'=>$projectId]);

        if (empty($perLog)) {
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }

        $perLog->status = $state;

        if ($logContent == '') {
            $perLog->status = 2;
        } else {
            $perLog->content = $logContent;
        }

        $perLog->update_time = time();
        if ($perLog->save(false)){
            $this->Success();
        }
        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }


    /**
     * 获取个人日志
     * @return array
     */
    public function actionGetList()
    {
        $userId = $this->getParam('userId',true);
        $projectId = $this->getParam('projectId',true);

        $data = APersonalLog::find()
            ->select('id as log_id,content as log_content,status as state,create_time,uid')
            ->where(['uid'=>$userId,'project_id'=>$projectId])
            ->andWhere(['<>','status',2])
            ->asArray()->all();


        if (empty($data)){
            $this->Success(['data'=>[]]);
        }

        foreach ($data as &$value){
            $value['create_time'] = date('Y.m.d',$value['create_time']);
            $value['operator'] = AUser::getName($value['uid']);
            unset($value['uid']);
        }
        $this->Success(['data'=>$data]);
    }
}
