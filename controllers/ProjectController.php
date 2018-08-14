<?php

namespace app\controllers;

use app\commond\Constants;
use app\models\AFile;
use app\models\APosition;
use app\models\AProjectExt;
use app\models\AUser;
use Yii;
use app\models\AModel;
use app\models\AProject;
use app\commond\helps;


class ProjectController extends BasicController
{
    public function init()
    {
       parent::init();
    }

    /**
     * http://www.api.com/position/index
     * 获取
     */
    public function actionGetlist()
    {

        $uid = $this->getParam('userId',true);
        $time = $this->getParam('time',true);
        //查询该用户创建的项目
        $createProejct = AProject::find()->where(['create_uid'=>$uid,
            'year'=>$time])
            ->andWhere(['!=','status',4])
            ->orderBy('sort ASC,id DESC')->asArray()->all();
        //判断该用户是否有部门
        $isPosition = AUser::getUserIsPosition($uid);
        //查询该用户的参与项目
        $joinProjectId = AProjectExt::find()->select('project_id')
        ->where(['uid'=>$uid])->asArray()->column();

        $joinProject = [];
        if ($joinProjectId) {
            $joinProject = AProject::find()
                ->where(['in','id',$joinProjectId])
                ->andWhere(['year'=>$time])
                ->andWhere(['!=','status',4])
                ->andWhere(['!=','create_uid',$uid])
                ->orderBy('sort ASC,id DESC')
                ->asArray()->all();
        }
        $data = array_merge($createProejct,$joinProject);
        if ($data) {
            $nowTime = time();
            foreach ($data as &$item){
                $usedTime = '';
                if ($nowTime > $item['start_time']) {
                    $usedTime = helps::timediff($nowTime,$item['start_time']);
                }
                $item['start_time'] = date('Y-m-d H:i:s',$item['start_time']);
                $item['allow_add'] = $item['allow_add'] == 1 ?  true : false;
                $item['status'] = intval($item['status']);
                $item['members'] = intval($item['members']);
                $item['describe'] = $item['description'];
                $item['used_time']  = $usedTime;
            }
        }
        $this->Success(['data'=>$data,'isCertified'=>$isPosition]);
    }


    /**
     * 创建项目
     */
    
    public function actionCreate()
    {
          $name         = $this->getParam('name',true);
          $startTime    = $this->getParam('start_time',true); 
          $allowAdd     = $this->getParam('allow_add',true);
          $description  = $this->getParam('describe',false);
          $selectModuleIds  = $this->getParam('selectModuleIds',true);
          $selectUserIds  = $this->getParam('selectUserIds',true);

          $member = (explode(',',$selectUserIds));

          $uid          = $this->getParam('userId',true);
          $transaction= Yii::$app->db->beginTransaction();
          $projectObj = new AProject();

          try {
              $projectObj->name = $name;
              $projectObj->start_time = intval(strtotime($startTime));
              $projectObj->description = $description;
              $projectObj->allow_add = $allowAdd == 'true' ? '1' : '0';
              $projectObj->members = count($member);
              $projectObj->create_time = time();
              $projectObj->status = '0';
              $projectObj->year = date('Y',time());
              $projectObj->create_uid = $uid;
              $projectObj->model_id = $selectModuleIds;
              $projectObj->join_uid = $selectUserIds;
              if (!$projectObj->insert()) {
                  $this->Error(Constants::RET_ERROR,$projectObj->getErrors());
              }
              $projectObjId = $projectObj->getAttribute('id');
              foreach ($member as $joinUid) {
                  $projectExtObj = new AProjectExt();
                  $projectExtObj->project_id = $projectObjId;
                  $projectExtObj->uid = $joinUid;
                  if (!$projectExtObj->insert()) {
                      $this->Error(Constants::RET_ERROR,$projectExtObj->getErrors());
                  }
              }
              $transaction->commit();

              $result = [
                  'projectId'=>(string) $projectObjId,
              ];

              $this->Success($result);

          } catch (\Exception $e) {
              //如果操作失败, 数据回滚
              $transaction->rollback();
              $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
          }
    }
    


    /**
     * 设置排序
     */
    public function actionSettingSort()
    {
        
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
    public function actionGetPositionNumber()
    {
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
     * 获取项目目录
     */
    public function actionGetProjectCatalog()
    {
        $userId = $this->getParam('userId',true);
        $projectId = $this->getParam('projectId',true);
        $parentId = $this->getParam('parentId',true);
        
        $project = AProject::find()->select('model_id')
        ->where(['id'=>$projectId])
        ->andWhere(['<>','status',4])
            ->asArray()->one();
        
        if (!$project) {
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }

        //模版id 切割成数组
        $modelIdArr = explode(',', $project['model_id']);
        $result = [];
        //说明是顶级返回所有子集
        if ($project['model_id'] == $parentId  || in_array($parentId,$modelIdArr)){
            $result = AModel::find()->select('id,name,pid,level')
                ->where(['pid'=>$parentId,'status'=>0,'project_id'=>0])->asArray()->all();

        } else {
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
           //  echo '<pre>';print_r($modelIdArr);exit();
            $data = helps::getson($temp,0,1);  //附上层级
           // echo '<pre>';print_r($data);exit();
            if ($data) {
                foreach ($data as $value) {
                    if ($value['pid'] == $parentId){
                        $result[] = $value;
                    }
                }
            }
            //特殊情况 要把所有层级找出来然后筛选
            if (empty($result)){
                $allstep = $this->getAllStep($projectId);
                foreach ($allstep as $item){
                    if ($parentId == $item['pid']){
                        $result[] = $item;
                    }
                }
            }
        }

        //根据最后返回信息 遍历 是否存在文件
//echo '<pre>';print_r($result);exit();
        $fileId = [];
        if ($result){

            foreach ($result as $k=>$cata){
                $result[$k]['type'] = '0';

                if ($parentId == 0){
                    $file = AFile::find()->select('id,name,path,type')
                        ->where([
                         //   'uid'=>$userId,
                            'project_id'=>$projectId,
                            'status'=>0,
                            'catalog_id'=>0
                        ])
                        ->asArray()->all();
                } else {
                    $file = AFile::find()->select('id,name,path,type')
                        ->where([
                           // 'uid'=>$userId,
                            'project_id'=>$projectId,
                            'status'=>0,
                            'catalog_id'=>$cata['pid']
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
        } else {
            //项目目录
            $cata = AModel::find()->select('id,name,type')->where(['project_id'=>$projectId,'pid'=>$parentId,'type'=>1])->asArray()->all();
            if ($cata) {
                foreach ($cata as &$value){
                    $value['type'] = '0';
                }
                $result = array_merge($result,$cata);
            }
            $file = AFile::find()->select('id,name,path,type')
                ->where([
                  //  'uid'=>$userId,
                    'project_id'=>$projectId,
                    'status'=>0,
                    'catalog_id'=>$parentId
                ])
                ->asArray()->all();
            if ($file){
                foreach ($file as $item){
                    if (!in_array($item['id'],$fileId)){
                        $fileId[] = $item['id'];
                        array_push($result,$item);
                    }
                }
            }
        }

        //首先显示目录 然后显示文件
        $data = [];
        if ($result) {
            $files = $chapter = [];
            foreach ($result as $item){
                if ($item['type'] == 0) {
                    $chapter[] = $item;
                } else {
                    $files[]=$item;
                }
            }
            $data = array_merge($chapter,$files);

        }



//echo '<pre>';print_r($result);exit;
        $this->Success(['data'=>$data]);
       
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
        // `status` '项目状态   0 未开始  1 进行中  2 已结束  3 暂停 4删除',
        $project = AProject::findOne(['id'=>$projectId,'create_uid'=>$userId]);

        if (!$project){
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }
        $transaction= Yii::$app->db->beginTransaction();
        try {
            if ($selectUserIds){

                $joinUid = $project->join_uid;
                $joinUid .= ','.$selectUserIds;
                $members = count(explode(',',$joinUid));
                $project->join_uid = $joinUid;
                $project->members = $members;
                $member = explode(',',$selectUserIds);
                foreach ($member as $uid) {
                    $projectExt = new AProjectExt();
                    $projectExt->project_id = $projectId;
                    $projectExt->uid = $uid;
                    if (!$projectExt->insert()){
                        $this->Error(Constants::RET_ERROR,$projectExt->getErrors());
                    }
                }
            }
            if ($projectStatus){
                if ($projectStatus == 1){
                    $project->start_time = time();
                }

                $project->status = $projectStatus;
            }


            if ( !$project->save(false)){
                $this->Error(Constants::RET_ERROR,$project->getErrors());

            }
            $transaction->commit();
            $this->Success();
        } catch (\Exception $e) {
            //如果操作失败, 数据回滚
            $transaction->rollback();
            $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
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

        $user = $result =[];

        if ($userArr) {
            foreach ($userArr as $uid) {
                $userInfo = AUser::find()->select('true_name,position_id')
                    ->where(['id'=>$uid,'status'=>0])->asArray()->one();
                if ($userInfo) {
                    $user[$userInfo['position_id']][] =[
                        'userId'=>$uid,
                        'trueName'=>$userInfo['true_name'],
                    ];
                }
            }

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

        $this->Success(['projectStatus'=>intval($project['status']),'data'=>$result,'create_uid'=>$createUid]);
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

        $transaction= Yii::$app->db->beginTransaction();
        try {
             AProjectExt::deleteAll(['project_id'=>$projectId]);
             $project->join_uid = $joinUid;
             $project->members = $num;

             foreach ($memberArr as $uid) {
                 $projectExt = new AProjectExt();
                 $projectExt->project_id = $projectId;
                 $projectExt->uid = $uid;
                 if (!$projectExt->insert()) {
                     $this->Error(Constants::RET_ERROR,$projectExt->getErrors());
                 }
             }
            if (!$project->save(false)){
                $this->Error(Constants::RET_ERROR,$project->getErrors());
            }
            $transaction->commit();
            $this->Success();
        }
        catch (\Exception $e) {
                //如果操作失败, 数据回滚
            $transaction->rollback();
            $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
        }
    }


    /**
     *删除项目
     */
    public function actionDel()
    {
        $this->isPost();
        $projectId = $this->getParam('projectId',true);
        $userId    = $this->getParam('userId',true);

        $project = AProject::findOne(['id'=>$projectId,'create_uid'=>$userId]);

        if (!$project) {
            $this->Error(Constants::PROJECT_NOT_FOUND,Constants::$error_message[Constants::PROJECT_NOT_FOUND]);
        }

        if ($project->status == 4){
            $this->Error(Constants::PROJECT_ALREADY_DEL,Constants::$error_message[Constants::PROJECT_ALREADY_DEL]);
        }

        $project->status = '4';
        if ($project->save(false)){
            $this->Success();
        }
        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }

    /**
     * 根据项目返回所有层级
     * @param $projectId
     * @return array
     *
     */

    private function getAllStep($projectId)
    {

        //$projectId = 111;
        $project = AProject::find()->select('model_id')
            ->where(['id'=>$projectId])->asArray()->one();
        $modelIdArr = explode(',', $project['model_id']);
        $top = $step = [];
        //说明是顶级返回所有子集
        if (count($modelIdArr) == 1) {
            $top = helps::getParents($project['model_id']);
        }

        $toparr = [];
        if ($top) {
            foreach ($top as $item){
                if ($item['pid'] == 0){
                    $toparr[] = $item;
                }
            }

            $all = helps::recursion($toparr);
            $step  = helps::getson($all,0,1);
        }

        return $step;
    }
}
