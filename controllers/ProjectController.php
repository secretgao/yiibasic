<?php

namespace app\controllers;

use app\commond\Constants;
use app\models\AFile;
use app\models\APersonalLog;
use app\models\APosition;
use app\models\AProjectExt;
use app\models\AProjectFollow;
use app\models\AProjectModel;
use app\models\ASecretaryTag;
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
        $mobile = $this->getParam('mobile',false,0);
        $time = date('Y');
        if  (isset($mobile) && !empty($mobile)){
            $uid = AUser::find()->select('id')->where(['phone'=>$mobile,'status'=>0])->asArray()->scalar();
        } else {
            $uid = $this->getParam('userId',true);
            $time = substr($this->getParam('time',true),0,4);
        }


        $postionId = $this->getParam('positionId',false,null);
        $secretarytagId = $this->getParam('secretarytagId',false,null);
        $modelId  = $this->getParam('modelId',false,null);
        $page  = $this->getParam('pageNum',false,null);
        $size  = $this->getParam('rp',false,null);
        $projectId = null;
        if ($modelId) {
            $projectId = AProjectModel::accordingToModelIdGetProjectId($modelId);
        }

        //小程序访问
        if (!is_numeric($uid) && is_string($uid)){
            $uid = AUser::find()->select('id')->where(['weixin_id'=>$uid,'status'=>0])->asArray()->scalar();
        }


        //查询该用户创建的项目
        $createProject = AProject::find()
            ->where(['create_uid'=>$uid])
            ->andWhere(['!=','status',4])
            ->andFilterWhere(['position_id'=>$postionId])
            ->andFilterWhere(['secretary_tag_id'=>$secretarytagId])
            ->andFilterWhere(['id'=>$projectId])
            ->andFilterWhere(['year'=>$time])
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

                ->andWhere(['!=','status',4])
                ->andWhere(['!=','create_uid',$uid])
                ->andFilterWhere(['position_id'=>$postionId])
                ->andFilterWhere(['id'=>$projectId])
                ->andFilterWhere(['secretary_tag_id'=>$secretarytagId])
                ->andFilterWhere(['year'=>$time])
                ->orderBy('sort ASC,id DESC')
                ->asArray()->all();
        }

        $data = array_merge($createProject,$joinProject);
        $projects = 0;
        $low = $middle = $high = 0;
        $newData = [];
        if ($data) {
            $projects = count($data);
            $nowTime = time();


            foreach ($data as $key=>&$item) {
                //项目所选模板数量
                $finish_progress = 0;
                $item['file_agree_num'] = intval($item['file_agree_num']);
                $item['model_num'] = intval($item['model_num']);
                //项目进度
                if ($item['file_agree_num'] > 0) {
                    $finish_progress = intval($item['file_agree_num']) /
                        intval($item['model_num']) * 100;
                }

                if ($finish_progress >= 0 && $finish_progress<20){
                    $low++;
                } else if($finish_progress >= 20 && $finish_progress<80){
                    $middle++;
                } else if ($finish_progress >= 80 ){
                    $high++;
                }
                if (!empty($page) && !empty($size)){

                    if ( (($page-1)*$size) <= $key && $key <= ($size*$page-1) ){
                        $usedTime = '';
                        if ($nowTime > $item['start_time']) {
                            $usedTime = helps::timediff($nowTime,$item['start_time']);
                        }
                        $manage_uid = AProjectExt::find()->select('uid')
                            ->where(['project_id'=>$item['id'],'is_manage'=>1])->asArray()->scalar();
                        $item['start_time'] = date('Y-m-d H:i:s',$item['start_time']);
                        $item['allow_add'] = $item['allow_add'] == 1 ?  true : false;
                        $item['status'] = intval($item['status']);
                        $item['members'] = intval($item['members']);
                        $item['describe'] = $item['description'];
                        $item['used_time']  = $usedTime;
                        $item['manage_uid']  = $manage_uid ? $manage_uid : 0;
                        $item['finish_progress'] = $finish_progress;
                        $item['money'] = empty($item['money']) ? 0 : $item['money'];
                        $item['concern_num'] = AProjectFollow::getFollowNum
                        ($item['id']);
                        $item['concern_state'] = AProjectFollow::getFollowNum
                        ($item['id'],$uid);
                        $newData[] = $item;
                        $projectAllStep = helps::getProjectModelAndCateLog($item['id']);
                        $remark = [];
                        if ($projectAllStep) {
                            $jihe = [];
                            foreach ($projectAllStep as $k =>$value) {
                                if ($value['level'] == 1 && !empty($value['describe'])) {
                                    if (!in_array($value['id'], $jihe)) {
                                        $remark[] = $value['describe'];
                                        $jihe[] = $value['id'];
                                    }
                                }
                                unset($projectAllStep[$k]);
                            }
                        }
                        $item['remark'] = $remark;
                    }
                } else {
                    $usedTime = '';
                    if ($nowTime > $item['start_time']) {
                        $usedTime = helps::timediff($nowTime,$item['start_time']);
                    }
                    $manage_uid = AProjectExt::find()->select('uid')
                        ->where(['project_id'=>$item['id'],'is_manage'=>1])->asArray()->scalar();

                    $data[$key]['start_time'] = date('Y-m-d H:i:s',$item['start_time']);
                    $data[$key]['allow_add'] = $item['allow_add'] == 1 ?  true : false;
                    $data[$key]['status'] = intval($item['status']);
                    $data[$key]['members'] = intval($item['members']);
                    $data[$key]['describe'] = $item['description'];
                    $data[$key]['used_time']  = $usedTime;
                    $data[$key]['manage_uid']  = $manage_uid ? $manage_uid : 0;
                    $data[$key]['finish_progress'] = $finish_progress;
                    $data[$key]['money'] = empty($item['money']) ? 0 : $item['money'];
                    $data[$key]['concern_num'] = AProjectFollow::getFollowNum
                    ($item['id']);
                    $data[$key]['concern_state'] = AProjectFollow::getFollowNum
                    ($item['id'],$uid);
                    $projectAllStep = helps::getProjectModelAndCateLog($item['id']);
                    $remark = [];
                    if ($projectAllStep) {
                        $jihe = [];
                        foreach ($projectAllStep as $k =>$value) {
                            if ($value['level'] == 1 && !empty($value['describe'])) {
                                if (!in_array($value['id'], $jihe)) {
                                    $remark[] = $value['describe'];
                                    $jihe[] = $value['id'];
                                }
                            }
                            unset($projectAllStep[$k]);
                        }
                    }
                    $item['remark'] = $remark;
                }
            }
        }
        $maxPage = 0;
        if (!empty($page) && !empty($size)){
            $data  = $newData;
            $maxPage = ceil($projects/$size);
        }
        $this->Success(['data'=>$data,
            'isCertified'=>$isPosition,
            'totalSize'=>$projects,
            'pageNum'=>intval($page),
            'rp'=>intval($size),
            'maxPage'=>$maxPage,
            'completion_low'=>$low,
            'completion_middle'=>$middle,
            'completion_high'=>$high,
        ]);
    }


    public function actionSearch()
    {
        $mobile = $this->getParam('mobile',false,0);

        if  (isset($mobile) && !empty($mobile)){
            $uid = AUser::find()->select('id')->where(['phone'=>$mobile,'status'=>0])->asArray()->scalar();
        } else {
            $uid = $this->getParam('userId',true);
           
        }

        $time = substr($this->getParam('time',true),0,4);
        $page  = $this->getParam('pageNum',true,null);
        $size  = $this->getParam('rp',true,null);
        $keyword = $this->getParam('keywords',true);

        //查询该用户创建的项目
        $createProejct = AProject::find()
            ->where(['create_uid'=>$uid])
            ->andWhere(['!=','status',4])
            ->andWhere(['like','name',$keyword])
            ->andFilterWhere(['year'=>$time])
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
                ->andWhere(['!=','status',4])
                ->andWhere(['!=','create_uid',$uid])
                ->andWhere(['like','name',$keyword])
                ->andFilterWhere(['year'=>$time])
                ->orderBy('sort ASC,id DESC')
                ->asArray()->all();
        }
        $data = array_merge($createProejct,$joinProject);
        $projects = 0;
        $newData = [];
        if ($data) {
            $projects = count($data);
            $nowTime = time();
            foreach ($data as $key=>&$item) {

                if ( (($page-1)*$size) <= $key && $key <= ($size*$page-1) ){
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
                    $item['money'] = empty($item['money']) ? 0 : $item['money'];
                    $item['concern_num'] = AProjectFollow::getFollowNum
                    ($item['id']);
                    $item['concern_state'] = AProjectFollow::getFollowNum
                    ($item['id'],$uid);
                    $newData[] = $item;
                    $projectAllStep = helps::getProjectModelAndCateLog($item['id']);
                    $remark = [];
                    if ($projectAllStep) {
                        $jihe = [];
                        foreach ($projectAllStep as $k =>$value) {
                            if ($value['level'] == 1 && !empty($value['describe'])) {
                                if (!in_array($value['id'], $jihe)) {
                                    $remark[] = $value['describe'];
                                    $jihe[] = $value['id'];
                                }
                            }
                            unset($projectAllStep[$k]);
                        }
                    }
                    $item['remark'] = $remark;
                }
            }
        }


        $this->Success(['data'=>$newData,
            'isCertified'=>$isPosition,
            'totalSize'=>$projects,
            'pageNum'=>intval($page),
            'rp'=>intval($size),
            'maxPage'=>$projects ==0 ? 0: ceil($projects/$size)
        ]);
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
          $financial_number = $this->getParam('financial_number',false);
          $money = $this->getParam('money',false);

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

              if ($financial_number){
                  $projectObj->financial_number = $financial_number;
              }
              if ($money){
                  $projectObj->money = $money;
              }
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

              //更新项目表模板数量
              helps::UpdateProjectModelNum($projectObjId);
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
        $modelColumns = 'pm.id as project_model_id,pm.model_id as id,pm.model_pid as pid,
        am.name,am.remark as describe,pm.level,am.type, pm.is_file as hasFile,pm.is_master_look';
        $cateLog = $result1 =  array();
        if ($parentId == 0 ) {
            $parentId = AProjectModel::find()->select('model_id')
                ->where(['project_id'=>$projectId,'model_pid'=>0,'status'=>0,'type'=>0])
                ->scalar();
            $cateLog =  (new Query())
                ->select($modelColumns)
                ->from('a_project_model as pm')
                ->leftJoin('a_model as am','pm.model_id = am.id')
                ->where(['pm.model_pid'=>0,'pm.project_id'=>$projectId,'pm.status'=>0,'pm.type'=>1])
                ->all();
        } else {
            $cateLog =  (new Query())
                ->select($modelColumns)
                ->from('a_project_model as pm')
                ->leftJoin('a_model as am','pm.model_id = am.id')
                ->where(['pm.model_pid'=>$parentId,'pm.project_id'=>$projectId,'pm.status'=>0,'pm.type'=>1])
                ->all();
        }
      
        $fileColumns = 'id,name,path,type,uid,create_time,size,status,small_path,compress_path';
      //  $modelColumns = 'pm.model_id as id,pm.model_pid as pid,am.name,am.remark as describe,pm.level,am.type, pm.is_file as hasFile';
        $result1 = (new Query())
            ->select($modelColumns)
            ->from('a_project_model as pm')
            ->leftJoin('a_model as am','pm.model_id = am.id')
            ->where(['pm.model_pid'=>$parentId,'pm.project_id'=>$projectId,'pm.status'=>0,'pm.type'=>0])
            ->all();

         $result = array_merge($result1,$cateLog);
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
            $result[$k]['hasFile'] = $cata['hasFile'] == 1 ? true : false;
            $result[$k]['isLook'] = $cata['is_master_look'] == 1 ? true : false;
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
          //  echo '<pre>';print_r($result);
            $files = $chapter = [];
            foreach ($result as &$item) {
                if ($item['type'] == 0) {
                   // $item['hasFile'] = helps::getHasFile($projectId,$item['id']);
                    $chapter[] = $item;

                } else {
                    $files[]=$item;
                }
            }
//            exit();
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
            ->select('status,secretary_tag_id as tag_id')
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

        $secretary = '';
        if (!empty($project['tag_id'])) {
            $tag = ASecretaryTag::find()->select('name')
                ->where(['id'=>$project['tag_id']])->asArray()->scalar();
            $secretary = isset($tag) ? $tag : '';
        }
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
        $ret = [
            'projectStatus'=>intval($project['status']),
            'data'=>$result,
            'create_uid'=>$createUid,
            'secretary'=>$secretary
        ];
        $this->Success($ret);
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
            AProjectModel::deleteAll(['project_id'=>$projectId]);
            AProjectExt::deleteAll(['project_id'=>$projectId]);
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



    /**
     * 设置项目 书记标签
     * @return mixed
     */

    public function actionSetSecretaryTag()
    {

        $projectId = $this->getParam('projectId',true);
        $userId    = $this->getParam('userId',true);
        $SecretaryTagId = $this->getParam('secretarytagId',true);

        $project = AProject::find()
            ->where(['id'=>$projectId,'create_uid'=>$userId])
            ->one();
        if ($project['status'] == 4) {
            $this->Error(Constants::PROJECT_NOT_FOUND,Constants::$error_message[Constants::PROJECT_NOT_FOUND]);
        }

        $project->secretary_tag_id = intval($SecretaryTagId);

        if ($project->save(false)) {
            $this->Success();
        }
        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);

    }

    /***
     *设置项目财政编号
     *
     * @return array
     */
    public function actionSetProjectFinancial()
    {
        $projectId = $this->getParam('projectId',true);
        $userId    = $this->getParam('userId',true);
        $number = $this->getParam('number',true);

        $existsFinancial = AProject::find()->where(['financial_number'=>$number])
        ->exists();
        if ($existsFinancial) {
            $this->Error(Constants::PROJECT_FINANCIAL_EXITS,Constants::$error_message[Constants::PROJECT_FINANCIAL_EXITS]);

        }

        $project = AProject::find()
            ->where(['id'=>$projectId,'create_uid'=>$userId])
            ->one();
        if ($project['status'] == 4) {
            $this->Error(Constants::PROJECT_NOT_FOUND,Constants::$error_message[Constants::PROJECT_NOT_FOUND]);
        }

        $project->financial_number = $number;

        if ($project->save(false)) {
            $this->Success();
        }
        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);

    }


    /**
     * 设置项目关注
     * @return array
     */
    public function actionSettingConcernState()
    {

        $projectId = $this->getParam('projectId',true);
        $userId    = $this->getParam('userId',true);
        $state    = $this->getParam('state');


        $where = ['project_id'=>$projectId,'uid'=>$userId];
        $exists = AProjectFollow::findOne($where);
        if ($exists){
            if ($exists->state == $state){
                $this->Error(Constants::PROJECT_EXISTS_FOLLOW,Constants::$error_message[Constants::PROJECT_EXISTS_FOLLOW]);
            }
            $exists->state = $state;
            $exists->update_time = time();
            if($exists->save(false)){
                $this->Success();
            } else {
                $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);

            }

        } else {

           $insert = new AProjectFollow();
           $insert->project_id = $projectId;
           $insert->uid = $userId;
           $insert->create_time = time();
           if($insert->save(false)){
               $this->Success();
           } else {
               $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
           }
        }

    }


    /***
     * 设置项目模版是否有文件
     * @return array
     */
    public function actionSettingProjectModelFile()
    {

        $Id    = $this->getParam('project_model_id',true);
        $hasFile = $this->getParam('is_look',true);
        $exits = AProjectModel::findOne($Id);
        if (!$exits) {
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }

        $exits->is_master_look = $hasFile;

        if ($exits->save(false)){
            $this->Success();
        } else {
            $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
        }
    }

}
