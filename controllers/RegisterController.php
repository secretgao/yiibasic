<?php

namespace app\controllers;

use app\commond\Constants;
use app\models\AUser;
use Yii;


class RegisterController extends BasicController
{
    public function init()
    {
       parent::init();
    }

    /**
     * http://www.api.com/register/sign-up
     * 注册用户
     */
    public function actionSignUp()
    {
        $this->isPost();
        $nickName = $this->getParam('nick_name',true);
        $id       = $this->getParam('id',true);
        $img      = $this->getParam('url',true);

        $idExits = AUser::find()->where(['status'=>0,'weixin_id'=>$id])
                ->asArray()->one();

        if ($idExits) {
            $isCertified = true;
            if (empty($idExits['position_id'])) {
                $isCertified = false;
            }

            $result = [
                'userId'  => (string) $idExits['id'],
                'avatar'  => $idExits['avatar'],
                'phone'   => $idExits['phone'],
                'nickName'=> $idExits['nick_name'],
                'realName'=> $idExits['true_name'],
                'group'   => $idExits['group'],
                'isCertified' =>$isCertified,
            ];
            $this->Success($result);
        }

        $group = AUser::find()->where(['status'=>0,'group'=>1])->count();

        $userObj = new AUser();
        $userObj->nick_name = $nickName;
        $userObj->avatar   = $img;
        $userObj->create_time = time();
        $userObj->weixin_id = $id;
        $userObj->group = $group == 0 ? '1' : '2';

        if ( $userObj->save(false) ) {
            $result = [
                'userId'=> (string) $userObj->getAttribute('id'),
                'avatar'=>$userObj->getAttribute('avatar'),
                'phone' =>'',
                'nickName'=>$userObj->getAttribute('nick_name'),
                'realName'=> '',
                'group' => $userObj->getAttribute('group'),
                'isCertified'=>false
            ];
            $this->Success($result);
        }
        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }


    /**
     * 重置密码
     */
    public function actionRestPassword()
    {
        $Id   = $this->getParam('id',true);
        $oldPass = $this->getParam('old_pass',true);
        $newPass = $this->getParam('new_pass',true);

        $user = AUser::findOne(['id'=>$Id,'status'=>0]);

        if (!$user) {
            $this->Error(Constants::USER_NOT_FOUND,Constants::$error_message[Constants::USER_NOT_FOUND]);
        }

        if ($user->password != md5($oldPass)){
           $this->Error(Constants::USER_PASSWORD_ERROR,Constants::$error_message[Constants::USER_PASSWORD_ERROR]);
        }
        

        $user->password = md5($newPass);
        if ($user->save(false)){
            $this->Success();
        }
        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }
}

