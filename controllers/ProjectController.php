<?php

namespace app\controllers;

use app\commond\Constants;
use app\models\APosition;
use app\models\AUser;
use Yii;
use app\models\AModel;
use app\models\AProject;


class ProjectController extends BasicController
{
        
    
    public function init(){
       parent::init();
    }

    /**
     * http://www.api.com/position/index
     * 获取
     */
    public function actionGetlist(){

       
        $uid = $this->getParam('userId',true);
        
      
        $time = $this->getParam('time',true);
        
        $data = AProject::find()->where(['create_uid'=>$uid,'year'=>$time])->asArray()->all(); 
        
        if (empty($data)){
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
            
        }
        
        foreach ($data as &$item){
            $usedTime = '';
            if (time() > $item['start_time']) {
                $usedTime = self::timediff(time(),$item['start_time']);
            }
            $item['start_time'] = date('Y-m-d H:i:s',$item['start_time']);
            $item['allow_add'] = $item['allow_add'] == 1 ?  true : false;
            $item['status'] = intval($item['status']);
            $item['members'] = intval($item['members']);
            $item['used_time']  = $usedTime;
        }      
        $this->Success(['data'=>$data]);
    }


    /**
     * 创建项目
     */
    
    public function actionAdd(){
      
          $name         = $this->getParam('name',true);
          $startTime    = $this->getParam('start_time',true); 
          $allowAdd     = $this->getParam('allow_add',false,0);
          $description  = $this->getParam('description',false);
          $number       = $this->getParam('number',true);
          
          $projectObj = new AProject();
          
          $projectObj->name = $name;
          $projectObj->start_time = strtotime($startTime);
          $projectObj->description = $description;
          $projectObj->allow_add = $allowAdd;
          $projectObj->members = $number;
          $projectObj->create_time = time();
          $projectObj->status = 0;
          $projectObj->year = date('Y',time());
          if ($projectObj->insert()){
              $this->Success();
          }
          $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }
    
  



    /**
     * 设置排序
     */
    public function actionSettingSort(){
        
        $this->ispost();
        $uid = $this->getParam('uid',true);
        $ids = $this->getParam('ids',true);

        $idArr = explode(',', $ids);
        $trans = Yii::$app->db->beginTransaction();
        try {
            foreach ($idArr as $key=>$projectId){
                $data = AProject::findOne($projectId);
                if ($data){
                    $data->sort = $key+1;
                    $data->save(false);
                }
            }
            $trans->commit();
            $this->Success();
        } catch (\Exception $e){
            $trans->rollBack();
            $this->Error($e);
          
        }    
    }



    /**
     * 获取部门人数
     */
    public function actionGetPositionNumber(){
        
        $parent = APosition::getAll();
        if (! $parent) {
            $this->Error();
        }
        foreach ($parent as $k=>$item){
            $children = empty(APosition::getChildren($item['id'])) ? [] : APosition::getChildren($item['id']);
                  
             if ($children){
                foreach ($children as $key=>$value){
                    $children[$key]['number'] = AUser::find()->where(['position_id'=>$value['id']])->count();                    
                }
            } 
            $parent[$k]['children'] = $children;
        }
        
        $this->Success(['data'=>$parent]);
        
    }
    
    public static function  timediff( $begin_time, $end_time )
    {
        if ( $begin_time < $end_time ) {
            $starttime = $begin_time;
            $endtime = $end_time;
        } else {
            $starttime = $end_time;
            $endtime = $begin_time;
        }
        $timediff = $endtime - $starttime;
        $days = intval( $timediff / 86400 );
        $remain = $timediff % 86400;
        $hours = intval( $remain / 3600 );
        $remain = $remain % 3600;
        $mins = intval( $remain / 60 );
        $secs = $remain % 60;
        
        $str = '';
        if ($days){
            $str.= $days.'天';
        }
        if ($hours){
            $str.= $hours.'小时';
        }
        if ($mins){
            $str.= $mins.'分';
        }
        if ($secs){
            $str.= $secs.'秒';
        }
      
       // $res = array( "day" => $days, "hour" => $hours, "min" => $mins, "sec" => $secs );
        return $str;
    }
}
