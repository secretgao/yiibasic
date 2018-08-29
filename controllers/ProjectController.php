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
        $postionId= $this->getParam('postionId',false,null);
        //查询该用户创建的项目
        $createProejct = AProject::find()
            ->where(['create_uid'=>$uid, 'year'=>$time])
            ->andWhere(['!=','status',4])
            ->andFilterWhere(['position_id'=>$postionId])
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
                $item['start_time'] = date('Y-m-d H:i:s',$item['start_time']);
                $item['allow_add'] = $item['allow_add'] == 1 ?  true : false;
                $item['status'] = intval($item['status']);
                $item['members'] = intval($item['members']);
                $item['describe'] = $item['description'];
                $item['used_time']  = $usedTime;
                $projectAllStep = helps::allStep($item['id']);
                $projectCreateMkdir = helps::getProjectCateLog($item['id']);

                $remark1 = [];
                if ($projectAllStep) {
                    $jihe = [];
                    foreach ($projectAllStep as $key =>$value) {
                        if ($value['level'] == 1 && !empty($value['describe'])) {
                            if (!in_array($value['id'], $jihe)) {
                                $remark1[] = $value['describe'];
                                $jihe[] = $value['id'];
                            }

                        }
                        unset($projectAllStep[$key]);
                    }

                }
                $remark2 = [];
                if ($projectCreateMkdir) {
                    foreach ($projectCreateMkdir as $key =>$value) {
                        if (!empty($value['remark'])) {
                            $remark2[]=$value['remark'];
                        }
                        unset($projectCreateMkdir[$key]);
                    }
                }

                $item['remark'] = array_merge($remark1,$remark2);
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
                           // 'status'=>0,
                            'catalog_id'=>0
                        ])->andWhere(['<>','status',3])
                        ->asArray()->all();
                } else {
                    $file = AFile::find()->select($fileColumns)
                        ->where([
                            'project_id'=>$projectId,
                           // 'status'=>0,
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
                  //  'status'=>0,
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
            $msg = '编辑项目:'.$project->name;
            if ($selectUserIds){
                $member = explode(',',$selectUserIds);
                $msg.='添加人员：';
                foreach ($member as $uid) {
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
        AProjectExt::deleteAll(['project_id'=>$projectId,'is_manage'=>1]);

        $set = new AProjectExt();
        $set->project_id = $projectId;
        $set->uid = $userId;
        $set->is_manage = '1';

        if ($set->save(false)) {
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
        $columns = 'id,type,uid,name,catalog_id,create_time,size,status as auditState,path';
        $fileData = AFile::find()->select($columns)
            ->where(['project_id'=>$projectId,'status'=>0])->asArray()->all();

        if (empty($fileData)){
            $this->Success(['data'=>[]]);
        }

        $data = [];
        foreach ($fileData as &$item){
            $item['creater'] = AUser::getName($item['uid']);
            $item['time'] =date('Y-m-d H:i:s',$item['create_time']);
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

}
