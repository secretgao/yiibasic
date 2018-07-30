<?php

namespace app\controllers;

use app\commond\Constants;
use app\models\AUser;
use Yii;


class RegisterController extends BasicController
{
        
    
    public function init(){
       parent::init();
    }

    /**
     * http://www.api.com/register/sign-up
     * 注册用户
     */
    public function actionSignUp(){

        $this->isPost();
        $nickName = $this->getParam('nick_name',true);
        $id       = $this->getParam('id',true);
        $img      = $this->getParam('url',true);

        $idExits = AUser::find()->where(['status'=>0,'weixin_id'=>$id])->exists();
        if ($idExits){
            $this->Error(Constants::USER_IS_EXITS,Constants::$error_message[Constants::USER_IS_EXITS]);
        }

        $group = AUser::find()->where(['status'=>0,'group'=>1])->count();

        $userObj = new AUser();
        $userObj->nick_name = $nickName;
        $userObj->avatar   = $img;
        $userObj->create_time = time();
        $userObj->weixin_id = $id;
        $userObj->group = $group == 0 ? '1' : '2';

        if ( $userObj->insert() ) {
            $result = [
                'userId'=> (string) $userObj->getAttribute('id'),
                'avatar'=>$userObj->getAttribute('avatar'),
                'phone' =>'',
                'nickName'=>$userObj->getAttribute('nick_name'),
                'realName'=> '',
                'group' => $userObj->getAttribute('group'),
            ];
            $this->Success($result);
        }
        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);

    }
}
