<?php

namespace app\controllers;


use app\models\APosition;
use app\models\APositionApply;
use app\models\AUser;
use Yii;
use app\commond\Constants;
use yii\base\Exception;

/**
 * 文件操作
 * @author Administrator
 *
 */

class UserController extends BasicController
{

    public function init(){
       parent::init();
    }

    /**
     * 获取成员列表
     * @return array
     */

    public function actionIndex()
    {

    }


    /**获取申请部门成员列表接口
     * @return array
     */
    public function actionGetApplyList()
    {

    }

    /**用户修改个人资料接口
     * @return array
     */
    public function actionSetInfo()
    {
        $userId = $this->getParam('userId',true);
        $phone  = $this->getParam('phone',false);
        $realName = $this->getParam('realName',false);

        $user = AUser::findOne(['id'=>$userId]);

        if (!$user){
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }

        if (is_numeric($phone)){
            $user->phone = $phone;
        }

        if ($realName){
            $user->true_name = $realName;
        }
        if ($user->save(false)){
            $this->Success();
        }
        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }

    /**申请添加部门
     * @return array
     */
    public function actionApplyDepartment()
    {
        $userId = $this->getParam('userId',true);
        $positionId = $this->getParam('positionId',true);


        $user = AUser::find()->select('id')->where(['id'=>$userId,'status'=>0])->scalar();


        if (!$user){
            $this->Error(Constants::USER_NOT_FOUND,Constants::$error_message[Constants::USER_NOT_FOUND]);
        }

        $position = APosition::find()->select('id')->where(['id'=>$positionId,'status'=>0])->scalar();

        if (!$position){
            $this->Error(Constants::POSITIONS_NOT_FOUND,Constants::$error_message[Constants::POSITIONS_NOT_FOUND]);
        }

        $apply = new APositionApply();
        $apply->uid = $userId;
        $apply->position_id = $positionId;
        $apply->status = '0';
        $apply->create_time = time();

        if ($apply->save()){
            $this->Success();
        }
        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }

}
