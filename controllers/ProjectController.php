<?php

namespace app\controllers;

use app\commond\Constants;
use app\models\AFile;
use app\models\APersonalLog;
use app\models\APosition;
use app\models\AProjectExt;
use app\models\AProjectModel;
use app\models\AUser;
use Yii;
use app\models\AModel;
use app\models\AProject;
use app\commond\helps;
use yii\db\Query;


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
        $time = substr($this->getParam('time',true),0,4);
        $postionId = $this->getParam('positionId',false,null);
        $modelId  = $this->getParam('modelId',false,0);
        $projectId = null;
        if ($modelId) {
            $projectId = AProject::accordingToModelIdGetProjectId($modelId);
        }

        //查询该用户创建的项目
        $createProejct = AProject::find()
            ->where(['create_uid'=>$uid, 'year'=>$time])
            ->andWhere(['!=','status',4])
            ->andFilterWhere(['position_id'=>$postionId])
            ->andFilterWhere(['in','id',$projectId])
            ->orderBy('sort ASC,id DESC')->asArray()->all();
        //判断该用户是否有部门
        $isPosition = AUser::getUserIsPosition($uid);
        //查询该用户的参与项目
        $joinProjectId = AProjectExt::find()
            ->select('project_id')
            ->where(['uid'=>$uid])
            ->asArray()->column();

        $joinProject = [];
        if ($joinProjectId) {
            $joinProject = AProject::find()
                ->where(['in','id',$joinProjectId])
                ->andWhere(['year'=>$time])
                ->andWhere(['!=','status',4])
                ->andWhere(['!=','create_uid',$uid])
                ->andFilterWhere(['position_id'=>$postionId])
                ->andFilterWhere(['in','id',$projectId])
                ->orderBy('sort ASC,id DESC')
                ->asArray()->all();
        }
        $data = array_merge($createProejct,$joinProject);
        if ($data) {
            $nowTime = time();
            foreach ($data as &$item) {
                $usedTime = '';
                if ($nowTime > $item['start_time']) {
                    $usedTime = helps::timediff($nowTime,$item['start_time']);
                }
                $manage_uid = AProjectExt::find()->select('uid')
                    ->where(['project_id'=>$item['id'],'is_manage'=>1])->asArray()->scalar();


                //项目所选模板数量
                $catalog_id_arr = helps::getProjectModelBottomNum($item['id']);
                $file_agree_num = 0;
                $finish_progress = 0;
                $model_num = count($catalog_id_arr);
                if ($model_num) {
                    //项目通过文件数量
                    $file_agree_num = (int)helps::getProjectAgreeFileNum
                    ($item['id'],$catalog_id_arr);
                    //项目进度
                    if ($file_agree_num > 0) {
                        $finish_progress = intval($file_agree_num) / intval($model_num) * 100;
                    }
                }

                $item['start_time'] = date('Y-m-d H:i:s',$item['start_time']);
                $item['allow_add'] = $item['allow_add'] == 1 ?  true : false;
                $item['status'] = intval($item['status']);
                $item['members'] = intval($item['members']);
                $item['describe'] = $item['description'];
                $item['used_time']  = $usedTime;
                $item['manage_uid']  = $manage_uid ? $manage_uid : 0;
                $item['model_num'] = $model_num;
                $item['file_agree_num'] = $file_agree_num;
                $item['finish_progress'] = $finish_progress;
                $projectAllStep = helps::getProjectModelAndCateLog($item['id']);
                $remark = [];
                if ($projectAllStep) {
                    $jihe = [];
                    foreach ($projectAllStep as $key =>$value) {
                        if ($value['level'] == 1 && !empty($value['describe'])) {
                            if (!in_array($value['id'], $jihe)) {
                                $remark[] = $value['describe'];
                                $jihe[] = $value['id'];
                            }
                        }
                        unset($projectAllStep[$key]);
                    }
                }

                $item['remark'] = $remark;
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
          $finishTime  = $this->getParam('finish_time',true);
          $positionId  = $this->getParam('position_id',true);

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
              $projectObj->finish_time = intval(strtotime($finishTime));
              $projectObj->position_id = $positionId;
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

              //添加项目模版
              helps::CreateProjectModel($selectModuleIds,$projectObjId);

              $transaction->commit();

              $result = [
                  'projectId'=>(string) $projectObjId,
              ];
              $msg = '创建项目:'.$name;
              helps::writeLog(Constants::OPERATION_PROJECT,$msg,$uid);

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
            $msg = '排序项目:';
            foreach ($idArr as $key=>$projectId){
                $data = AProject::findOne($projectId);
                if ($data){
                    $data->sort = $key+1;
                    $data->save(false);
                    $msg .= $data->name.',';
                }
            }
            $trans->commit();

            helps::writeLog(Constants::OPERATION_PROJECT,$msg,$uid);
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
   /*
    public function actionGetProjectCatalog()
    {
        $userId = $this->getParam('userId',true);
        $projectId = $this->getParam('projectId',true);
        $parentId = $this->getParam('parentId',true);
        
        $project = AProject::find()
            ->select('model_id')->where(['id'=>$projectId])
            ->andWhere(['<>','status',4])->asArray()->one();
        
        if (!$project) {
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }
        $fileColumns = 'id,name,path,type,uid,create_time,size,status';
        //模版id 切割成数组
        $modelIdArr = explode(',', $project['model_id']);
        $result = [];
        //说明是顶级返回所有子集
        if ($project['model_id'] == $parentId  || in_array($parentId,$modelIdArr)){
            $result = AModel::find()->select('id,name,pid,level,remark as describe')
                ->where(['pid'=>$parentId,'status'=>0,'project_id'=>0])->asArray()->all();

        } else {
            $modelArr =  $temp =  [];  //去除重复目录用
            foreach ($modelIdArr as $id) {
                $catalog = helps::getParents($id);
                if (!$catalog) {
                    continue;
                }
                foreach ($catalog as $item) {
                    //去除重复
                    if (!in_array($item['id'], $modelArr)) {
                        $temp[] = $item;
                        $modelArr[]= $item['id'];
                    }
                }
            }

            $data = helps::getson($temp,0,1);  //附上层级

            if ($data) {
                foreach ($data as $value) {
                    if ($value['pid'] == $parentId){
                        $result[] = $value;
                    }
                }
            }
            //特殊情况 要把所有层级找出来然后筛选
            if (empty($result)) {
                $allstep = helps::allStep($projectId);
                foreach ($allstep as $item) {
                    if ($parentId == $item['pid']) {
                        $result[] = $item;
                    }
                }
            }
        }

        //根据最后返回信息 遍历 是否存在文件
//echo '<pre>';print_r($result);exit();
        $fileId = [];
        if ($result) {
            foreach ($result as $k=>$cata) {
                $result[$k]['type'] = '0';
                $son  = AModel::find()->select('remark')->where(['pid'=>$cata['id'],'status'=>0])->andWhere(['<>','remark',''])
                    ->asArray()->column();
                $result[$k]['remark'] = $son;
                if ($parentId == 0) {
                    $file = AFile::find()->select($fileColumns)
                        ->where([
                            'project_id'=>$projectId,
                            'catalog_id'=>0
                        ])->andWhere(['<>','status',3])
                        ->asArray()->all();
                } else {
                    $file = AFile::find()->select($fileColumns)
                        ->where([
                            'project_id'=>$projectId,
                            'catalog_id'=>$cata['pid']
                        ])->andWhere(['<>','status',3])
                        ->asArray()->all();
                }

                if ($file) {
                    foreach ($file as $item) {
                        if (!in_array($item['id'],$fileId)) {
                            $fileId[] = $item['id'];
                            $item['path'] = trim($item['path'],'.');
                            $item['creater'] = AUser::getName($item['uid']);
                            $item['time'] = date('Y-m-d',$item['create_time']);
                            array_push($result,$item);
                        }
                    }
                }
            }

            //项目目录
            $cata = AModel::find()->select('id,name,type,remark as describe')->where(['project_id'=>$projectId,'pid'=>$parentId,'type'=>1])->asArray()->all();
            if ($cata) {

                foreach ($cata as &$value) {
                    $value['type'] = '0';
                    $son  = AModel::find()->select('remark')->where(['pid'=>$value['id'],'status'=>0])->andWhere(['<>','remark',''])
                        ->asArray()->column();
                    $value['remark'] = $son;
                }
                $result = array_merge($result,$cata);
            }

        } else {
            //项目目录
            $cata = AModel::find()->select('id,name,type,remark as describe')->where(['project_id'=>$projectId,'pid'=>$parentId,'type'=>1])->asArray()->all();
            if ($cata) {
                foreach ($cata as &$value) {
                    $value['type'] = '0';
                    $son  = AModel::find()->select('remark')->where(['pid'=>$value['id'],'status'=>0])->andWhere(['<>','remark',''])
                        ->asArray()->scalar();
                    $value['remark'] = $son;
                }
                $result = array_merge($result,$cata);
            }
            $file = AFile::find()->select($fileColumns)
                ->where([
                    'project_id'=>$projectId,
                    'catalog_id'=>$parentId
                ])->andWhere(['<>','status',3])
                ->asArray()->all();
            if ($file) {
                foreach ($file as &$item) {
                    if (!in_array($item['id'],$fileId)) {
                        $fileId[] = $item['id'];
                        $item['path'] = trim($item['path'],'.');
                        $item['creater'] = AUser::getName($item['uid']);
                        $item['time'] = date('Y-m-d',$item['create_time']);
                        array_push($result,$item);
                    }
                }
            }
        }

        //首先显示目录 然后显示文件
        $data = [];
        if ($result) {
            $files = $chapter = [];
            foreach ($result as $item) {
                if ($item['type'] == 0) {
                    $chapter[] = $item;
                } else {
                    $files[]=$item;
                }
            }
            $data = array_merge($chapter,$files);
        }

        $this->Success(['data'=>$data]);
       
    }
*/
    /**
     * 获取项目目录
     */
    public function actionGetProjectCatalog()
    {
        $userId = $this->getParam('userId',true);
        $projectId = $this->getParam('projectId',true);
        $parentId = $this->getParam('parentId',true);

        //获取项目创建人员
        $project = AProject::find()->select('create_uid,model_id')
            ->where(['id'=>$projectId])
            ->andWhere(['<>','status',4])
            ->asArray()->one();

        if (!$project) {
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }
        //获取项目参与人员
        $member = AProjectExt::find()->select('uid')->where(['project_id'=>$projectId])->asArray()->column();
        $allMember = array_merge($member,$project);
        //判断uid 在不在 创建人和参与人的集合里
        if (!in_array($userId,$allMember)){
           $this->Error(Constants::MEMBER_NO_EXITS,Constants::$error_message[Constants::MEMBER_NO_EXITS]);
        }

        $fileColumns = 'id,name,path,type,uid,create_time,size,status,small_path,compress_path';
        $modelColumns = 'pm.model_id as id,pm.model_pid as pid,am.name,am.remark as describe,pm.level,am.type';
        $result = (new Query())
            ->select($modelColumns)
            ->from('a_project_model as pm')
            ->leftJoin('a_model as am','pm.model_id = am.id')
            ->where(['pm.model_pid'=>$parentId,'pm.project_id'=>$projectId])
            ->all();
        //根据最后返回信息 遍历 是否存在文件
//echo '<pre>';print_r($result);exit();
        $fileId = [];
        if (empty($result)) {
            // result 为空 可能是最底层
            $file = AFile::find()->select($fileColumns)
                ->where([
                    'project_id'=>$projectId,
                    'catalog_id'=>$parentId
                ])->andWhere(['<>','status',3])
                ->asArray()->all();
            if ($file) {
                foreach ($file as &$item) {
                    $fileId[] = $item['id'];
                    $item['path'] = trim($item['path'],'.');
                    $item['small_path'] = trim($item['small_path'],'.');
                    $item['creater'] = AUser::getName($item['uid']);
                    $item['time'] = date('Y-m-d',$item['create_time']);

                }
                $this->Success(['data'=>$file]);
            }
            $this->Success(['data'=>[]]);
        }

        foreach ($result as $k=>$cata) {
            $result[$k]['type'] = '0';
            $son  = AModel::find()->select('remark')->where(['pid'=>$cata['id'],'status'=>0])->andWhere(['<>','remark',''])
                ->asArray()->column();
            $result[$k]['remark'] = $son;
            if ($parentId == 0) {
                $file = AFile::find()->select($fileColumns)
                    ->where([
                        'project_id'=>$projectId,
                        'catalog_id'=>0
                    ])->andWhere(['<>','status',3])
                    ->asArray()->all();
            } else {
                $file = AFile::find()->select($fileColumns)
                    ->where([
                        'project_id'=>$projectId,
                        'catalog_id'=>$cata['pid']
                    ])->andWhere(['<>','status',3])
                    ->asArray()->all();
            }

            if ($file) {
                foreach ($file as $item) {
                    if (!in_array($item['id'],$fileId)) {
                        $fileId[] = $item['id'];
                        $item['path'] = trim($item['path'],'.');
                        $item['small_path'] = trim($item['small_path'],'.');
                        $item['compress_path'] = trim($item['compress_path'],'.');
                        $item['creater'] = AUser::getName($item['uid']);
                        $item['time'] = date('Y-m-d',$item['create_time']);
                        array_push($result,$item);
                    }
                }
            }
        }

        //首先显示目录 然后显示文件
        $data = [];
        if ($result) {
            $files = $chapter = [];
            foreach ($result as $item) {
                if ($item['type'] == 0) {
                    $chapter[] = $item;
                } else {
                    $files[]=$item;
                }
            }
            $data = array_merge($chapter,$files);
        }

        $this->Success(['data'=>$data]);

    }
    /**
     * 设置项目状态和编辑人员
     *  `status` '项目状态   0 未开始  1 进行中  2 已结束  3 暂停 4删除',
     */
    public function actionSetStatusUser()
    {
        $userId = $this->getParam('userId',true);
        $projectId = $this->getParam('projectId',true);
        $projectStatus = $this->getParam('status',false);
        $selectUserIds  = $this->getParam('selectUserIds',false);

        $project = AProject::findOne(['id'=>$projectId,'create_uid'=>$userId]);

        if (!$project){
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }
        $transaction= Yii::$app->db->beginTransaction();
        try {
            $msg = '编辑项目:'.$project->name;
            if ($selectUserIds){
                $member = explode(',',$selectUserIds);
                $msg.='添加人员：';

                foreach ($member as $uid) {
                    $exists = AProjectExt::find()
                        ->where(['project_id'=>$projectId,'uid'=>$uid])
                        ->exists();
                    //不用重复添加
                    if ($exists) {
                        continue;
                    }
                    $projectExt = new AProjectExt();
                    $projectExt->project_id = $projectId;
                    $projectExt->uid = $uid;
                    $msg.= AUser::getName($uid);
                    if (!$projectExt->insert()){
                        $this->Error(Constants::RET_ERROR,$projectExt->getErrors());
                    }
                }

                $count = AProjectExt::find()->where(['project_id'=>$projectId])->count();
                $project->members = $count;
            }
            if ($projectStatus){
                $msg.='设置项目状态：';
                if ($projectStatus == 1) {
                    $project->start_time = time();
                }

                $project->status = $projectStatus;
                $msg .=Constants::$projectStatus[$projectStatus];
            }

            if ( !$project->save(false)){
                $this->Error(Constants::RET_ERROR,$project->getErrors());

            }
            $transaction->commit();

            helps::writeLog(Constants::OPERATION_PROJECT,$msg,$userId);

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
     * status  0 未开始  1 进行中  2 已结束  3 暂停'
     */
    public function actionGetProjectStatusUser()
    {
        $createUid = $this->getParam('userId',true);
        $projectId = $this->getParam('projectId',true);

        $project = AProject::find()
            ->select('status')
            ->where(['id'=>$projectId,'create_uid'=>$createUid])
            ->andWhere(['<>','status',4])
            ->asArray()->one();
        if (!$project){
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }

        $userArr = AProjectExt::find()
            ->select('uid,is_manage')
            ->where(['project_id'=>$projectId])
            ->all();
        if (empty($userArr)) {
            $this->Success(['projectStatus'=>intval($project['status']),'data'=>[]]);
        }

        $user = $result =[];

        if ($userArr) {
            foreach ($userArr as $item) {
                $isManager = false;
                $userInfo = AUser::find()->select('true_name,position_id')
                    ->where(['id'=>$item['uid'],'status'=>0])->asArray()->one();
                if ($userInfo) {

                    if ($item['is_manage'] == 1) {
                        $isManager = true;
                    }
                    $user[$userInfo['position_id']][] =[
                        'userId'=>$item['uid'],
                        'trueName'=>$userInfo['true_name'],
                        'isManager'=>$isManager,
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
        $userId = $this->getParam('userId',true);
        $projectId = $this->getParam('projectId',true);
        $memberId = $this->getParam('memberId',true);

        $project = AProject::find()
            ->where(['id'=>$projectId,'create_uid'=>$userId])
            ->andWhere(['<>','status',4])
            ->one();
        if (!$project){
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }

        $memberArr = AProjectExt::find()
            ->select('uid')
            ->where(['project_id'=>$projectId,'is_manage'=>0])
            ->column();

        if (!in_array($memberId,$memberArr)){
            $this->Error(Constants::MEMBER_NO_EXITS,Constants::$error_message[Constants::MEMBER_NO_EXITS]);
        }
        $msg = '删除项目参与人员:';
        $num = 0; //从新计算参与人数
        foreach ($memberArr as $key=>$mid){
            if ($mid == $memberId){
                unset($memberArr[$key]);
                $msg.= AUser::getName($memberId).',';
            } else {
                $num++;
            }
        }

        $transaction= Yii::$app->db->beginTransaction();
        try {
             AProjectExt::deleteAll(['project_id'=>$projectId,'is_manage'=>0]);

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
            helps::writeLog(Constants::OPERATION_PROJECT,$msg,$userId);

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

        $project = AProject::find()
            ->where(['id'=>$projectId,'create_uid'=>$userId])
            ->one();

        if (!$project) {
            $this->Error(Constants::PROJECT_NOT_FOUND,Constants::$error_message[Constants::PROJECT_NOT_FOUND]);
        }

        if ($project->status == 4){
            $this->Error(Constants::PROJECT_ALREADY_DEL,Constants::$error_message[Constants::PROJECT_ALREADY_DEL]);
        }

        $project->status = '4';
        if ($project->save(false)){
            $msg = '删除项目:'.$project->name;
            helps::writeLog(Constants::OPERATION_PROJECT,$msg,$userId);
            $this->Success();
        }
        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }


    /**
     * 设置项目负责人
     * @return array
     */
    public function actionSetManage()
    {
        $projectId = $this->getParam('projectId',true);
        $userId    = $this->getParam('userId',true);

        $project = AProject::find()
            ->where(['id'=>$projectId])
            ->andwhere(['<>','status',4])
            ->exists();
        if (!$project) {
            $this->Error(Constants::PROJECT_NOT_FOUND,Constants::$error_message[Constants::PROJECT_NOT_FOUND]);
        }
        //删除之前的项目负责人
        AProjectExt::updateAll(['is_manage'=>0],['project_id'=>$projectId,'is_manage'=>1]);

        $obj = AProjectExt::findOne(['project_id'=>$projectId,'uid'=>$userId]);

        if (!$obj) {
            $this->Error(Constants::MEMBER_NO_EXITS,Constants::$error_message[Constants::MEMBER_NO_EXITS]);
        }

        $obj->is_manage = '1';
        if ($obj->save(false)) {
            $this->Success();
        }

        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);

    }


    /**
     * 项目责任人获取项目所有待审核文件列表
     * @return array
     */
    public function actionGetAuditList()
    {
        $projectId = $this->getParam('projectId',true);
        $userId    = $this->getParam('userId',true);

        $project = AProject::find()->select('status')
            ->where(['id'=>$projectId])
            ->asArray()->one();
        if ($project['status'] == 4) {
            $this->Error(Constants::PROJECT_NOT_FOUND,Constants::$error_message[Constants::PROJECT_NOT_FOUND]);
        }

        $projectInfo = AProjectExt::find()
            ->where(['project_id'=>$projectId,'uid'=>$userId,'is_manage'=>1])
            ->exists();
        if (!$projectInfo) {
            $this->Error(Constants::PROJECT_MANAGE_EXITS,Constants::$error_message[Constants::PROJECT_MANAGE_EXITS]);
        }
        $columns = 'id,type,uid,name,catalog_id,create_time,size,status as auditState,compress_path,path,small_path';
        $fileData = AFile::find()->select($columns)
            ->where(['project_id'=>$projectId,'status'=>0])->asArray()->all();

        if (empty($fileData)){
            $this->Success(['data'=>[]]);
        }

        $data = [];
        foreach ($fileData as &$item){
            $item['creater'] = AUser::getName($item['uid']);
            $item['time'] =date('Y-m-d H:i:s',$item['create_time']);
            $item['path'] = trim($item['path'],'.');
            $item['small_path'] = trim($item['small_path'],'.');
            $item['compress_path'] = trim($item['compress_path'],'.');
            //按照目录分组
            if (array_key_exists($item['catalog_id'],$data)){
                $data[$item['catalog_id']][] = $item;
            } else {
                $data[$item['catalog_id']][] = $item;
            }
        }

        $result = [];
        foreach ($data as $key=>$value) {
            if ($key == 0) {
                $foler = $key;
            } else {
                //查出所属目录的所有直属上级
                $title = helps::getParents($key);
                $tmp = [];
                foreach ($title as $v){
                    $tmp[$v['id']]=$v['name'];
                }
                sort($tmp);
                //把数组上级 分隔成字符串
                $foler = implode('/',$tmp);
            }

            $result[]=[
                'foler'=>$foler,
                'files'=>$value,
            ];
        }
        $this->Success(['data'=>$result]);

    }


    /**
     * 获取个人项目文件列表
     * @return array
     */
    public function actionGetShelfUploadList()
    {
        $projectId = $this->getParam('projectId',true);
        $userId    = $this->getParam('userId',true);

        $project = AProject::find()->select('status')
            ->where(['id'=>$projectId])
            ->asArray()->one();
        if ($project['status'] == 4) {
            $this->Error(Constants::PROJECT_NOT_FOUND,Constants::$error_message[Constants::PROJECT_NOT_FOUND]);
        }

        $projectInfo = AProjectExt::find()
            ->where(['project_id'=>$projectId,'uid'=>$userId])
            ->exists();
        if (!$projectInfo) {
            $this->Error(Constants::MEMBER_NO_EXITS,Constants::$error_message[Constants::MEMBER_NO_EXITS]);
        }
        $columns = 'id,type,uid,name,catalog_id,create_time,size,status as auditState,compress_path,path,small_path';
        $fileData = AFile::find()->select($columns)
            ->where(['project_id'=>$projectId,'uid'=>$userId])->asArray()->all();

        if (empty($fileData)){
            $this->Success(['data'=>[]]);
        }

        $data = [];
        foreach ($fileData as &$item){
            $item['creater'] = AUser::getName($item['uid']);
            $item['time'] =date('Y-m-d H:i:s',$item['create_time']);
            $item['path'] = trim($item['path'],'.');
            $item['small_path'] = trim($item['small_path'],'.');
            $item['compress_path'] = trim($item['compress_path'],'.');
            //按照目录分组
            if (array_key_exists($item['catalog_id'],$data)){
                $data[$item['catalog_id']][] = $item;
            } else {
                $data[$item['catalog_id']][] = $item;
            }
        }

        $result = [];
        foreach ($data as $key=>$value) {
            if ($key == 0) {
                $foler = $key;
            } else {
                //查出所属目录的所有直属上级
                $title = helps::getParents($key);
                $tmp = [];
                foreach ($title as $v){
                    $tmp[$v['id']]=$v['name'];
                }
                sort($tmp);
                //把数组上级 分隔成字符串
                $foler = implode('/',$tmp);
            }

            $result[]=[
                'foler'=>$foler,
                'files'=>$value,
            ];
        }
        $this->Success(['data'=>$result]);
    }

    public function actionAs()
    {

      $commond = "./usr/local/ffmpeg -i /tmp/_VID_20180730_221548.mp4 -y -f mjpeg -ss 3 -t 0.001 -s 320*240 /tmp/test.jpg";
       $res = shell_exec($commond);

       echo '<pre>';print_r($res);
        exit();
      //  var_dump($fil);
        //获取所有模板和目录
       // $allStep = helps::allStep(171);

      //  $catalog_id_arr = helps::getProjectModelBottomNum(183);
      //  echo '<pre>';print_r($catalog_id_arr);

        //项目通过文件数量
     //   $file_agree_num = helps::getProjectAgreeFileNum(183,$catalog_id_arr);
      //  echo '<pre>';print_r($file_agree_num);
            //项目进度
      //  $r = helps::getChildren(17230,[]);
       // $model = AModel::find()->where(['id'=>17230])->asArray()->all();
       // $res = helps::recursion($model);
        //$s = helps::CreateProjectModel(17230,171);
       // exit();
        //echo '<pre>';print_r($res);exit();
       // $a = helps::getProjectModelBottomNum(170);
       // var_dump($a);
    }

}
