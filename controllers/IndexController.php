<?php

namespace app\controllers;

use app\commond\Constants;
use app\commond\helps;
use app\models\ALog;
use app\models\AUser;
use Yii;
use yii\db\Query;


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

    /**
     * 隐私政策
     * @return string
     */
    public function actionPrivacyPolicy()
    {
        $this->layout=false;
        return $this->render('privacy-policy');
    }


    /**
     * 修复数据
     */
    public function actionHasFile()
    {

        $pages = $this->getParam('p');
        $pageSize = 3;
        $page = $pageSize * ( $pages-1 );
        $data = (new Query())->select('project_id,catalog_id')
            ->from('a_file')->where(['status'=>1])
            ->offset($page)->limit($pageSize)->all();
        if (empty($data)){
            $this->Success(['data'=>'empty']);
        }
        foreach ($data as $item){
           $re = helps::uploadFileUpdateProjectModel($item['project_id'],$item['catalog_id']);
        }
      //  echo '<pre>';print_r($data);
    }
}
