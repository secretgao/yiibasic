<?php

namespace app\controllers;

use app\commond\Constants;
use app\models\ALog;
use app\models\AUser;
use Yii;


class IndexController extends BasicController
{
        
    
    public function init(){
       parent::init();
    }
    
    public function actionIndex(){
        phpinfo();
            echo 'aaaa';exit();
    }


    public function actionGetLogType()
    {
        $logType = Constants::$operationType;
        $this->Success(['data'=>$logType]);
    }

    public function actionGetLog()
    {
        $userId = $this->getParam('userId',false);
        $type   = $this->getParam('type',false);

        $data = ALog::find()
            ->select('create_time,operation,type,uid')
            ->andFilterWhere(['uid'=>$userId])
            ->andFilterWhere(['type'=>$type])
            ->asArray()->all();


        foreach ($data as &$item) {
            $item['create_time'] = date('Y-m-d H:i:s',$item['create_time']);
            $item['username'] = AUser::getName($item['uid']);
            $item['type'] = isset(Constants::$operationType[$item['type']]) ?
                Constants::$operationType[$item['type']] : '无';
        }
        $this->Success(['data'=>$data]);
    }
}
