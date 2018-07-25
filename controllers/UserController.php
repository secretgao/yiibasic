<?php

namespace app\controllers;


use app\models\AUser;
use Yii;
use app\commond\Constants;

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

    }

}
