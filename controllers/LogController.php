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




}
