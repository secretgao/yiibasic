<?php

namespace app\controllers;

use app\commond\Constants;
use app\models\APosition;
use app\models\APositionApply;
use app\models\AProject;
use app\models\AUser;
use Yii;


class PositionController extends BasicController
{

    public function init()
    {
       parent::init();
    }

    /**
     * http://www.api.com/position/index
     * 获取所有职位
     */
    public function actionIndex()
    {

        $uid = $this->getParam('userId',true);
        $user = AUser::find()->where(['id'=>$uid,'status'=>0])->asArray()->one();

        if (!$user) {
            $this->Success(['data'=>[]]);
        }

        $userPosition[0] = [];

        if (!empty($user['position_id'])){
            $userPosition[0] = APosition::find()->select('id,name')
                ->where(['id'=>$user['position_id'],'status'=>0])->asArray()->one();

        }

        $parent = APosition::getAll();
        if (! $parent) {
            $this->Success(['data'=>[]]);
        }
        if (empty($userPosition[0])){
            $userPosition[0]['id'] = '-1';
            $userPosition[0]['name'] = '';
        }
        $result = array_merge($userPosition,$parent);
        $this->Success(['data'=>$result]);

    }

    /**
     *  部门添加
     * http://www.api.com/position/add
     */
    public function actionAdd()
    {
        $this->isPost();
        $positionName = $this->getParam('name',true);
        $pid          = $this->getParam('pid',false);
        $uid          = $this->getParam('userId',true);
        $positionObj = new APosition();
        $positionObj->name = $positionName;
        if (!empty($pid)) {
            $positionObj->pid = $pid;

        }
        $positionObj->create_time = time();

        if ($positionObj->insert()) {
            $msg = '创建部门:'.$positionName;
            helps::writeLog(Constants::OPERATION_POSITION,$msg,$uid);
            $this->Success([
                'data'=>[
                    'positionId'=> (string)$positionObj->attributes['id'],
                    'positionName'=>$positionObj->attributes['name']
                ]
            ]);
        }

        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }

    /**
     * 部门编辑
     */
    public function actionEdit()
    {
        $this->isPost();
        $id          = $this->getParam('id',true);
        $positionName = $this->getParam('name',true);
        $uid          = $this->getParam('userId',true);

        $positionObj = APosition::findOne($id);

        if (!$positionObj) {
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }

        $positionObj->name = $positionName;
        if ($positionObj->save(false)) {
            $msg = '编辑部门:'.$positionName;
            helps::writeLog(Constants::OPERATION_POSITION,$msg,$uid);
            $this->Success();
        }

        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }


    /**
     * 部门删除
     */
    public function actionDel()
    {
        $this->isPost();
        $id          = $this->getParam('id',true);
        $uid          = $this->getParam('userId',true);

        $positionObj = APosition::findOne($id);
        if (!$positionObj) {
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }

        $positionObj->status =-1;
        if ($positionObj->save(false)) {
            $msg = '删除部门:'.$positionObj->name;
            helps::writeLog(Constants::OPERATION_POSITION,$msg,$uid);
            $this->Success();
        }

        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }

    /**
     * 部门增减人员
     */
    public function actionManageUser()
    {
        $id = $this->getParam('id',true);
        $userId = $this->getParam('userId',true);
        $isAdd = $this->getParam('isAdd',true);

        $user = AUser::findOne(['id'=>$userId]);
        $position = APosition::findOne(['id'=>$id]);
        if (!$user || !$position) {
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }

        if ($isAdd == 'true') {
            $user->position_id = $id;
        } else {
            $user->position_id = 0;
        }

        if ($user->save(false)) {
            if ($isAdd == 'true') {
                $msg = '添加部门:'.$position->name;
                helps::writeLog(Constants::OPERATION_POSITION,$msg,$userId);
            } else {
                $msg = '移除部门:'.$position->name;
                helps::writeLog(Constants::OPERATION_POSITION,$msg,$userId);
            }
            $this->Success();
        }
        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }

    /**
     *部门申请 通过与拒绝
     */

    public function actionApply()
    {
        $userId = $this->getParam('userId',true);
        $type = $this->getParam('type',true);

        $apply = APositionApply::findOne(['uid'=>$userId,'status'=>0]);

        if (!$apply) {
            $this->Error(Constants::APPLY_NOT_FOUND,Constants::$error_message[Constants::APPLY_NOT_FOUND]);
        }
        $user = AUser::findOne(['id'=>$apply['uid']]);
        $position = APosition::findOne(['id'=>$apply['position_id']]);
        if (!$user || !$position) {
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }

        if ($type == 1) {
            $transaction= Yii::$app->db->beginTransaction();
            try {
                $apply->status = '1';
                $apply->update_time  = time();
                if ( !$apply->save(false)){
                    $this->Error(Constants::RET_ERROR,$apply->getErrors());
                }
                $user->position_id = $apply['position_id'];
                if (!$user->save(false)){
                    $this->Error(Constants::RET_ERROR,$apply->getErrors());
                }
                $transaction->commit();
                $this->Success();
            } catch (\Exception $e) {
                //如果操作失败, 数据回滚
                $transaction->rollback();
                $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
            }

        } else {
            $apply->status = '2';
            $apply->update_time  = time();
            if (!$apply->save(false)) {
                $this->Error(Constants::RET_ERROR,$apply->getErrors());
            }
            $this->Success();
        }
    }



    /**
     * 职位添加
     */

    public function actionAddPosition(){

        $this->isPost();
        $name = $this->getParam('name',true);
        $userId = $this->getParam('userId',true);

        $positionObj = new APosition();
        $positionObj->name = $name;
        $positionObj->pid  = -1;
        $positionObj->create_time = time();

        if ($positionObj->insert()) {

            $msg = '添加职位:'.$name;
            helps::writeLog(Constants::OPERATION_POSITION,$msg,$userId);
            $this->Success();
        }

        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);

    }

    /**
     * 获取系统职位。不是部门
     * @return array
     */

    public function actionGetSysPosition()
    {
        $this->Success(['data'=>Constants::$position]);
    }


    /**
     * 设置系统职位
     */
    public function actionSetPosition()
    {
       $userId = $this->getParam('userId',true);
       $positionId = $this->getParam('positionId',true);

       $sysPosition = array_flip(Constants::$position);
       if (!in_array($positionId,$sysPosition)) {
           $this->Error(Constants::NOT_SYS_POSITION,Constants::$error_message[Constants::NOT_SYS_POSITION]);
       }

       $user = AUser::findOne(['id'=>$userId,'status'=>0]);

       if (!$user) {
           $this->Error(Constants::USER_NOT_FOUND,Constants::$error_message[Constants::USER_NOT_FOUND]);
       }

       $user->sys_position = $positionId;

       if ($user->save(false)){
           $this->Success();
       }
        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);

    }

    /**
     * 获取部门项目列表
     * @return array
     */
    public function actionProjectIndex()
    {
        $userId = $this->getParam('userId',true);
        //获取所有部门
        $data = APosition::getAllPosition();

        //根据部门id 查询每个部门下的项目数量
        foreach ($data as $key=>$item) {
            $data[$key]['projects'] = 0;
            $projectNum = AProject::find()
                ->where(['position_id'=>$item['id'],'create_uid'=>$userId])
                ->andWhere(['<>','status',4])
                ->count();
            if ($projectNum) {
                $data[$key]['projects'] = intval($projectNum);
            }
        }
        $this->Success(['data'=>$data]);
       // echo '<pre>';print_r($data);exit();
        //return parent::actions(); // TODO: Change the autogenerated stub
    }
}
