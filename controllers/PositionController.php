<?php

namespace app\controllers;

use app\commond\Constants;
use app\models\APosition;
use app\models\APositionApply;
use app\models\AUser;
use Yii;


class PositionController extends BasicController
{
        
    
    public function init(){
       parent::init();
    }

    /**
     * http://www.api.com/position/index
     * 获取所有职位
     */
    public function actionIndex(){

        $uid = $this->getParam('userId',true);

        $user = AUser::find()->where(['id'=>$uid,'status'=>0])->asArray()->one();

        if (!$user){
            $this->Error(Constants::USER_NOT_FOUND,Constants::$error_message[Constants::USER_NOT_FOUND]);
        }

        $userPosition = [];


        if (!empty($user['position_id'])){
            $userPosition[0] = APosition::find()->select('id,name')
                ->where(['id'=>$user['position_id'],'status'=>0])->asArray()->one();

        }
       // echo '<pre>';print_r($userPosition);
        $parent = APosition::getAll();
        if (! $parent) {
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }
        if (empty($userPosition[0])){
            $userPosition[0]['id'] = '-1';
            $userPosition[0]['name'] = '';
        }
        $result = array_merge($userPosition,$parent);
//echo '<pre>';print_r($userPosition);

      //  echo '<pre>';print_r($result);exit();
       /* foreach ($parent as &$item){
            $item['children'] = empty(APosition::getChildren($item['id'])) ? [] : APosition::getChildren($item['id']);
        }*/

        $this->Success(['data'=>$result]);

    }

    /**
     *  部门添加
     * http://www.api.com/position/add
     */
    public function actionAdd(){
       // $this->isPost();
        $positionName = $this->getParam('name',true);
        $pid          = $this->getParam('pid',false);

        $positionObj = new APosition();
        $positionObj->name = $positionName;
        if (!empty($pid)){
            $positionObj->pid = $pid;

        }
        $positionObj->create_time = time();

        if ($positionObj->insert()) {
            $this->Success(
                [
                    'data'=>[
                        'positionId'=> (string)$positionObj->attributes['id'],
                        'positionName'=>$positionObj->attributes['name']
                        ]
                ]
            );
        }

        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }

    /**
     * 部门编辑
     */
    public function actionEdit(){
        $this->isPost();
        $id          = $this->getParam('id',true);
        $positionName = $this->getParam('name',true);

        $positionObj = APosition::findOne($id);

        if (!$positionObj){
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);

        }

        $positionObj->name = $positionName;
        if ($positionObj->save(false)) {
            $this->Success();
        }

        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }


    /**
     * 部门删除
     */
    public function actionDel(){
        $this->isPost();
        $id          = $this->getParam('id',true);

        $positionObj = APosition::findOne($id);
        if (!$positionObj){
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }

        $positionObj->status =-1;
        if ($positionObj->save(false)) {
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
        if (!$user || !$position){
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }

        if ($isAdd == 'true'){
            $user->position_id = $id;
        } else {
            $user->position_id = 0;
        }

        if ($user->save(false)){
            $this->Success();
        }
        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }

    /**
     *
     */

    public function actionApply()
    {

        $applyId = $this->getParam('applyId',true);
        $type = $this->getParam('type',true);

        $apply = APositionApply::findOne(['id'=>$applyId,'status'=>0]);

        if ( !$apply ){
            $this->Error(Constants::APPLY_NOT_FOUND,Constants::$error_message[Constants::APPLY_NOT_FOUND]);
        }
        $user = AUser::findOne(['id'=>$apply['uid']]);
        $position = APosition::findOne(['id'=>$apply['position_id']]);
        if (!$user || !$position){
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }

        if ($type == 1){
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
            if ( !$apply->save(false)){
                $this->Error(Constants::RET_ERROR,$apply->getErrors());
            }
            $this->Success();
        }


    }
}
