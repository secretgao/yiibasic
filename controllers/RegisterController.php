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
        $trueName = $this->getParam('true_name',true);
        $img      = $this->getParam('url',true);
        $position_id= $this->getParam('position_id',true);

        $userObj = new AUser();

        $userObj->nick_name = $nickName;
        $userObj->true_name = $trueName;
        $userObj->img       = $img;
        $userObj->create_time = time();
        $userObj->position_id = $position_id;

        if ( $userObj->insert() ) {
            $this->Success();
        }

        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);

    }
}
