<?php

namespace app\controllers;

use app\commond\Constants;
use app\models\AFile;
use app\models\APosition;
use app\models\AUser;
use Yii;
use app\models\AModel;
use app\models\AProject;
use app\commond\helps;


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

       // exit();
        $data = AProject::find()->where(['create_uid'=>$uid,'year'=>$time])->asArray()->all(); 
        
        if (empty($data)){
            $this->Success(['data'=>[]]);
        }
        
        foreach ($data as &$item){
            $usedTime = '';
            if (time() > $item['start_time']) {
                $usedTime = helps::timediff(time(),$item['start_time']);
            }
            $item['start_time'] = date('Y-m-d H:i:s',$item['start_time']);
            $item['allow_add'] = $item['allow_add'] == 1 ?  true : false;
            $item['status'] = intval($item['status']);
            $item['members'] = intval($item['members']);
            $item['describe'] = $item['description'];
            $item['used_time']  = $usedTime;
        }

        $this->Success(['data'=>$data]);
    
    }


    public function actionGetModels(){
        //$id = 21;
       // $arr = [18,21];
        $arr = [21,26];
       // $res = helps::getParents($id); 
       // $new = helps::getson($res,0,1);
        
       // echo '<pre>';print_r($new);
        ///$result = helps::make_tree($new);
       // echo '<pre>';print_r($result);
        $test= [];
        $idArr = [];
        foreach ($arr as $id){
            $a = helps::getParents($id);
            foreach ($a as $item){
                //去除重复
                if (!in_array($item['id'], $idArr)) {
                    $test[] = $item;
                    $idArr[]= $item['id'];
                }
                
            }
        }
        
       
        $new = helps::getson($test,0,1);
       // echo '<pre>';print_r($new);
        $result = helps::make_tree($new);
       //  echo '<pre>';print_r($result);
         $this->Success($result);
        exit();
    }
    
    /**
     * 创建项目
     */
    
    public function actionCreate(){

          $name         = $this->getParam('name',true);
          $startTime    = $this->getParam('start_time',true); 
          $allowAdd     = $this->getParam('allow_add',true);
          $description  = $this->getParam('describe',false);
          $selectModuleIds  = $this->getParam('selectModuleIds',true);
          $selectUserIds  = $this->getParam('selectUserIds',true);


          $members = count(explode(',',$selectUserIds));


          $uid          = $this->getParam('userId',true);
          $projectObj = new AProject();
          
          $projectObj->name = $name;
          $projectObj->start_time = intval(strtotime($startTime));
          $projectObj->description = $description;
          $projectObj->allow_add = $allowAdd == 'true' ? '1' : '0';
          $projectObj->members = $members;
          $projectObj->create_time = time();
          $projectObj->status = '0';
          $projectObj->year = date('Y',time());
          $projectObj->create_uid = $uid;
          $projectObj->model_id = $selectModuleIds;
          $projectObj->join_uid = $selectUserIds;
          if ($projectObj->insert()){
              $projectObjId = $projectObj->getAttribute('id');
                       
              $result = [
                  'projectId'=>(string) $projectObjId,
                 //
              ];
              
              $this->Success($result);
          }
       

          $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }
    


    /**
     * 设置排序
     */
    public function actionSettingSort(){
        
        $this->ispost();
        $uid = $this->getParam('userId',true);
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
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
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
    
  
    /**
     * 获取项目详情
     */
    public function actionGetProjectDetail(){
        
        $userId = $this->getParam('userId',true);
        $projectId = $this->getParam('projectId',true);
        
        $columns = '*';
        $project = AProject::find()->select($columns)
        ->where(['id'=>$projectId,'create_uid'=>$userId])->asArray()->one();
        
        if (!$project){
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }
        
       // echo '<pre>';print_r($project);
      
    //    $project['cata_log'] = helps::accordingCatalogToAllHierarchy($project['model_id']);
        
        $this->Success(['data'=>$project]);
    }


    /**
     * 获取项目目录
     */
    public function actionGetProjectCatalog(){
        $userId = $this->getParam('userId',true);
        $projectId = $this->getParam('projectId',true);
        $parentId = $this->getParam('parentId',true);
        
        $project = AProject::find()->select('model_id')
        ->where(['id'=>$projectId,'create_uid'=>$userId])->asArray()->one();
        
        if (!$project){
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }

        //模版id 切割成数组
        $modelIdArr = explode(',', $project['model_id']);

        $modelArr =  $temp =  [];  //去除重复目录用
        foreach ($modelIdArr as $id){
            $catalog = helps::getParents($id);
            if (!$catalog){
                continue;
            }
            foreach ($catalog as $item){
                //去除重复
                if (!in_array($item['id'], $modelArr)) {
                    $temp[] = $item;
                    $modelArr[]= $item['id'];
                }
            }
        }

      //  echo '<pre>';print_r($catalogArr);exit();
        $data = helps::getson($temp,0,1);  //附上层级

       // echo '<pre>';print_r($data);exit();
        $result = [];
        if ($data) {
            foreach ($data as $value) {
                if ($value['pid'] == $parentId){
                    $result[] = $value;
                }
            }
        }
        $parent = AModel::find()->where(['id'=>$parentId,'status'=>0])->asArray()->one();

        //说明是顶级返回子集
        if ($parent && $parent['pid'] == 0){
            $result = AModel::find()->select('id,name,pid')
                ->where(['pid'=>$parentId,'status'=>0,'project_id'=>0])->asArray()->all();

            foreach ($result as &$value){
                $value['level'] = 2;
            }
        }

        //根据最后返回信息 遍历 是否存在文件
//echo '<pre>';print_r($result);
        $fileId = [];
        foreach ($result as $k=>$cata){
            $result[$k]['type'] = '0';
            $file = AFile::find()->select('id,name,path,type')
                ->where([
                    'uid'=>$userId,
                    'project_id'=>$projectId,
                    'status'=>0,
                    'catalog_id'=>$cata['id']
                ])
                ->asArray()->all();
            if ($parentId == 0){
                $file = AFile::find()->select('id,name,path,type')
                    ->where([
                        'uid'=>$userId,
                        'project_id'=>$projectId,
                        'status'=>0,
                        'catalog_id'=>0
                    ])
                    ->asArray()->all();
            }

            if ($file){
                foreach ($file as $item){
                    if (!in_array($item['id'],$fileId)){
                        $fileId[] = $item['id'];
                        array_push($result,$item);
                    }
                }
            }
        }


        //项目目录
        $cata = AModel::find()->select('id,name,type')->where(['project_id'=>$projectId,'pid'=>$parentId,'type'=>1])->asArray()->all();
        if ($cata) {
            foreach ($cata as &$value){
                $value['type'] = '0';
            }

            $result = array_merge($result,$cata);
        }

        $this->Success(['data'=>$result]);
       
    }

    /**
     * 设置项目状态和编辑人员
     */
    public function actionSetStatusUser()
    {
        $userId = $this->getParam('userId',true);
        $projectId = $this->getParam('projectId',true);
        $projectStatus = $this->getParam('status',false);
        $selectUserIds  = $this->getParam('selectUserIds',false);
// `status` tinyint(3) DEFAULT '0' COMMENT '项目状态   0 未开始  1 进行中  2 已结束  3 暂停',
        $project = AProject::findOne(['id'=>$projectId,'create_uid'=>$userId]);

        if (!$project){
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }
        if ($selectUserIds){
            $members = count(explode(',',$selectUserIds));
            $project->join_uid = $selectUserIds;
            $project->members = $members;
        }
        if ($projectStatus){
            $project->status = $projectStatus;
        }


        if ($project->save(false)){
            $this->Success();
        }
        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);

    }

    /**
     * 获取项目状态和参与人员用户
     */
    public function actionGetProjectStatusUser()
    {
        $createUid = $this->getParam('userId',true);
        $projectId = $this->getParam('projectId',true);

// `status` tinyint(3) DEFAULT '0' COMMENT '项目状态   0 未开始  1 进行中  2 已结束  3 暂停',
        $project = AProject::find()->select('status,join_uid')
            ->where(['id'=>$projectId,'create_uid'=>$createUid])
            ->asArray()->one();
        if (!$project){
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }

        if (empty($project['join_uid'])){
            $this->Success(['projectStatus'=>intval($project['status']),'data'=>[]]);
        }
        $userArr = explode(',',$project['join_uid']);

        $user = [];
        if ($userArr) {
            foreach ($userArr as $uid){

                $userInfo = AUser::find()->select('true_name,position_id')
                    ->where(['id'=>$uid])->asArray()->one();
                $user[$userInfo['position_id']][] =[
                    'userId'=>$uid,
                    'trueName'=>$userInfo['true_name'],
                ];
            }
            $result = [];
            foreach ($user as $positionId=>$value){
                $position = APosition::find()->select('name')
                    ->where(['id'=>$positionId])->asArray()->scalar();
                $result[]=[
                    'positionId'=>(string)$positionId,
                    'positionName'=>$position,
                    'positionUser'=>$user[$positionId]
                ];
            }
        }

        $this->Success(['projectStatus'=>intval($project['status']),'data'=>$result]);
    }



    /**
     * 删除项目参与人员
     */
    public function actionDelProjectMember()
    {
        $uid = $this->getParam('userId',true);
        $projectId = $this->getParam('projectId',true);
        $memberId = $this->getParam('memberId',true);

        $project = AProject::find()
            ->where(['id'=>$projectId,'create_uid'=>$uid])
            ->one();
        if (!$project){
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }

        $memberArr = explode(',',$project['join_uid']);

        if (!in_array($memberId,$memberArr)){
            $this->Error(Constants::MEMBER_NO_EXITS,Constants::$error_message[Constants::MEMBER_NO_EXITS]);
        }

        $num = 0; //从新计算参与人数
        foreach ($memberArr as $key=>$mid){
            if ($mid == $memberId){
                unset($memberArr[$key]);
            } else {
                $num++;
            }
        }

        $joinUid = implode(',',$memberArr);

        $project->join_uid = $joinUid;
        $project->members = $num;

        if ($project->save(false)){
            $this->Success();
        }
        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }
}
