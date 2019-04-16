<?php

namespace app\controllers;


use app\models\AAppVersion;
use app\models\AMessage;
use app\models\APosition;
use app\models\APositionApply;
use app\models\AProject;
use app\models\ASecretaryTag;
use app\models\AUser;
use Yii;
use app\commond\Constants;
use app\commond\helps;
use yii\base\Exception;

/**
 * 用户操作
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
    public function actionLogin()
    {
        $username = $this->getParam('username',true);
        $password = $this->getParam('password',true);
        $columns = 'id as userId,avatar,phone,nick_name as nickName,true_name as realName,group,email,password,sys_position';
        if ($username == Constants::ADMIN_USER) {
            if (md5($password) == md5(Constants::ADMIN_USER)) {
                $user = AUser::find()
                    ->select($columns)
                    ->where(['status'=>0,'group'=>1])->asArray()->one();
                $this->Success($user);
            } else {
                $this->Error(Constants::PASSWORD_ERROR,Constants::$error_message[Constants::PASSWORD_ERROR]);
            }

        } else if ($username == Constants::TEST_USER) {
            if (md5($password) == md5(Constants::TEST_USER)) {
                $user = AUser::find()
                    ->select($columns)
                    ->where(['status'=>0,'group'=>2])->asArray()->one();
                $this->Success($user);
            } else {
                $this->Error(Constants::PASSWORD_ERROR,Constants::$error_message[Constants::PASSWORD_ERROR]);

            }
        }else if ($username != Constants::ADMIN_USER || $username != Constants::TEST_USER){
            $user = AUser::find()
                ->select($columns)
                ->where(['status'=>0,'true_name'=>$username])->asArray()->one();

            if ($user){
                if ($user['password'] == md5($password)){
                    unset($user['password']);
                    $this->Success($user);
                }  else {
                    $this->Error(Constants::USER_PASSWORD_ERROR,Constants::$error_message[Constants::USER_PASSWORD_ERROR]);

                }

            }
            $this->Error(Constants::USER_NOT_FOUND,Constants::$error_message[Constants::USER_NOT_FOUND]);

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
        if (!$data) {
            $this->Success(['data'=>[]]);
        }

        foreach ($data as &$item) {
            $user = AUser::find()->select('id as userId,true_name as trueName,sys_position as type')
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

        if (!$data) {
            $this->Success(['data'=>[]]);
        }
        $user = [];
        foreach ($data as $item) {
            $userInfo = AUser::find()->select('true_name,position_id,phone')
                ->where(['id'=>$item['uid']])->asArray()->one();
            $user[$item['position_id']][] =[
                'userId'=>$item['uid'],
                'trueName'=>$userInfo['true_name'],
                'phone' =>$userInfo['phone']
            ];
        }

        $result = [];
        foreach ($user as $positionId=>$value) {
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

        if (!$user) {
            $this->Error(Constants::USER_NOT_FOUND,Constants::$error_message[Constants::USER_NOT_FOUND]);
        }
        $msg = '';
        if (is_numeric($phone)) {
            $msg.= '手机号:'.$user->phone.'改成'.$phone;
            $user->phone = $phone;
        }

        if ($realName) {
            $msg.= '真实姓名:'.$user->true_name.'改成'.$realName;
            $user->true_name = $realName;
        }
        if ($email) {
            if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->Error(Constants::EMAIL_IS_ERROR,Constants::$error_message[Constants::EMAIL_IS_ERROR]);
            }
            $msg.= '邮箱:'.$user->email.'改成'.$email;
            $user->email = $email;
        }

        if ($user->save(false)) {
            helps::writeLog(Constants::OPERATION_USER,$msg,$userId);
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
        if (!$user) {
            $this->Error(Constants::USER_NOT_FOUND,Constants::$error_message[Constants::USER_NOT_FOUND]);
        }
        $position = APosition::find()->select('id,name')->where
        (['id'=>$positionId,'status'=>0])->one();
        if (!$position) {
            $this->Error(Constants::POSITIONS_NOT_FOUND,Constants::$error_message[Constants::POSITIONS_NOT_FOUND]);
        }

        $exitsApply = APositionApply::find()->where(['uid'=>$userId,'status'=>0])->asArray()->one();
        if ($exitsApply) {
            APositionApply::deleteAll(['uid'=>$userId,'status'=>0]);
        }
        $apply = new APositionApply();
        $apply->uid = $userId;
        $apply->position_id = $positionId;
        $apply->status = '0';
        $apply->create_time = time();

        if ($apply->save()){
            $msg = '申请添加部门:'.$position['name'];
            helps::writeLog(Constants::OPERATION_USER,$msg,$userId);
            $this->Success();
        }
        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }

    /**
     * 检测系统版本
     * @return array
     */
    public function actionCheckVersion()
    {
        $this->isPost();
        $version = $this->getParam('version',true);
        $type = $this->getParam('type',true);
        //1: iOS， 2: Android
        if (!in_array($type,[1,2])) {
            $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
        }
        $newVersion = Yii::$app->params['version'];
        if ($type == 1){
            $systemVersion = $newVersion[$type];
        } else {
            $systemVersion = $newVersion[$type]['num'];
        }

        //只要 接口传过来的版本号比系统定义的小  就返回 提示更新
        $res = helps::versionCompare($version,$systemVersion);
        if ($res === 2) {
           $verObj = new AAppVersion();
           $verObj->version = $version;
           $verObj->system = $type;
           $verObj->create_time = time();
           $verObj->save(false);
           $this->Success(['needUpdate'=>true,'data'=>$newVersion[$type]]);
        } else {
            $this->Success(['needUpdate'=>false]);
        }
    }

    /**
     *用户添加意见反馈
     * @return array
     */
    public function actionMessage()
    {
        $uid = $this->getParam('userId');
        $content = $this->getParam('content');

        $obj = new AMessage();
        $obj->uid = $uid;
        $obj->content = $content;
        $obj->create_time = time();
        if ($obj->save()){
            $this->Success();
        }
        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }

    /**
     * 获取用户意见反馈
     * @return array
     */
    public function actionGetMessage()
    {

        $data = AMessage::find()->select('*,FROM_UNIXTIME(create_time) as create_time')->orderBy('id desc')
            ->asArray()->all();
        $this->Success(['data'=>$data]);
    }

    /**
     * 创建书记标签
     */
    public function actionCreateSecretaryTag(){

        $uid = $this->getParam('userId');
        $name = $this->getParam('name');

        $obj = new ASecretaryTag();
        $obj->name = $name;
        $obj->create_uid = intval($uid);
        $obj->create_time = time();

        if ($obj->save()){
            $this->Success();
        }
        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }

    /**
     * 获取书记标签
     *
     */
    public function actionGetSecretaryList()
    {
        $userId = $this->getParam('userId',true);
        $data = ASecretaryTag::find()->select('id,name')->asArray()->all();

        if (empty($data)) {
            $this->Success(['data'=>[]]);
        }

        foreach ($data as $key=>$item) {
            $projects = AProject::find()
                ->where(['secretary_tag_id'=>$item['id'],'create_uid'=>$userId])
                ->andWhere(['!=','status',4])
                ->count();
            $data[$key]['projects'] = intval($projects);
        }

        $this->Success(['data'=>$data]);
    }

}
