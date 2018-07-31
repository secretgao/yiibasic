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
     * 登录
     */
    public function actionLogin(){

        $username = $this->getParam('username',true);
        $password = $this->getParam('password',true);

        if ($username == Constants::ADMIN_USER){

            if (md5($password) == md5(Constants::ADMIN_USER)){
                $user = AUser::find()
                    ->select('id as userId,avatar,phone,nick_name as nickName,true_name as  realName,group')
                    ->where(['status'=>0,'group'=>1])->asArray()->one();

                $this->Success($user);

            } else{
                $this->Error(Constants::PASSWORD_ERROR,Constants::$error_message[Constants::PASSWORD_ERROR]);
            }


        } else if ($username == Constants::TEST_USER){
            if (md5($password) == md5(Constants::TEST_USER)){
                $user = AUser::find()
                    ->select('id as userId,avatar,phone,nick_name as nickName,true_name as  realName,group')
                    ->where(['status'=>0,'group'=>2])->asArray()->one();
                $this->Success($user);
            } else{
                $this->Error(Constants::PASSWORD_ERROR,Constants::$error_message[Constants::PASSWORD_ERROR]);

            }
        } else {
            $this->Error(Constants::USER_NOT_FOUND,Constants::$error_message[Constants::USER_NOT_FOUND]);
        }

    }
    /**
     * 获取成员列表
     * @return array
     */

    public function actionIndex()
    {
        $data = APosition::find()->select('id as positionId,name as positionName')->where(['status'=>0])->asArray()->all();
        if (!$data){
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }

        foreach ($data as &$item){
            $user = AUser::find()->select('id as userId,true_name as trueName')
                ->where(['position_id'=>$item['positionId'],'status'=>0])->asArray()->all();
            $item['positionUser'] = $user;
        }
        $this->Success(['data'=>$data]);

    }


    /**获取申请部门成员列表接口
     * @return array
     */
    public function actionGetApplyList()
    {
        $data = APositionApply::find()->where(['status'=>0])
            ->orderBy('create_time DESC')->asArray()->all();

        if (!$data){
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }
        $user = [];
        foreach ($data as $item){
            $userInfo = AUser::find()->select('true_name,position_id,phone')
                ->where(['id'=>$item['uid']])->asArray()->one();
            $user[$item['position_id']][] =[
                'userId'=>$item['uid'],
                'trueName'=>$userInfo['true_name'],
                'phone' =>$userInfo['phone']
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

        $this->Success(['data'=>$result]);
    }

    /**用户修改个人资料接口
     * @return array
     */
    public function actionSetInfo()
    {
        $userId = $this->getParam('userId',true);
        $phone  = $this->getParam('phone',false);
        $realName = $this->getParam('realName',false);
        $email = $this->getParam('email',false);





        $user = AUser::findOne(['id'=>$userId]);

        if (!$user){
            $this->Error(Constants::USER_NOT_FOUND,Constants::$error_message[Constants::USER_NOT_FOUND]);
        }

        if (is_numeric($phone)){
            $user->phone = $phone;
        }

        if ($realName){
            $user->true_name = $realName;
        }
        if ($email){
            if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                $this->Error(Constants::EMAIL_IS_ERROR,Constants::$error_message[Constants::EMAIL_IS_ERROR]);
            }
            $user->email = $email;
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

        $exitsApply = APositionApply::find()->where(['uid'=>$userId,'status'=>0])->asArray()->one();
        if ($exitsApply){
            APositionApply::deleteAll(['uid'=>$userId,'status'=>0]);
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
