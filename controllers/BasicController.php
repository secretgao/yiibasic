<?php

namespace app\controllers;

use Yii;

use yii\web\Controller;
use yii\web\Response;
use app\commond\Constants;

class BasicController extends Controller
{
    
    public static $request;
    
    public function init(){
        parent::init();
        //   self::$request = \Yii::$app->request;
        
    }
    
    public function beforeAction($action)
    {
        self::$request = \Yii::$app->request;
        //code here
        $result = parent::beforeAction($action);
        return $result;
    }
    public function afterAction($action, $result)
    {
        //code here
        $result = parent::afterAction($action, $result);
        return $result;
    }
    
    public function actionCheckLogin()
    {
        
    }
    
    
    /**
     * 获取页面传参
     * @param type $key
     * @param type $is_need
     * @param type $default_value
     * @return type
     */
    public function getParam($key, $is_need = true, $default_value = NULL)
    {
        
        $val = self::$request->get($key);
        if ($val === NULL)
        {
            $val = self::$request->post($key);
        }
        if ($is_need && $val === NULL)
        {
            $this->Error(Constants::GLOBAL_INVALID_PARAM, 'required param: ' . $key);
        }
        return $val!==NULL ? $val : $default_value;
    }
    
    
    /**
     * 成功返回
     * @param array $_data
     */
    public function Success($_data = false)
    {
        $_msg = [
            'ok' => true,
            'serverTime' => time(),
        ];
        if (is_array($_data))
        {
            $_msg += $_data;
        }
        $this->Json($_msg);
    }
    /**
     * 错误返回
     * @param integer $_errID
     */
    public function Error($_errID = '10000', $ext_msg = null)
    {
        $_msg = [
            'ok' => false,
            'serverTime' => time(),
            'errorId' => $_errID,
            'errorMsg' => $ext_msg,
        ];
        $this->Json($_msg);
    }
    /**
     * JSON输出并结束
     * @param array $_arr
     */
    public function Json($_arr)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        header('Content-Type:application/json; charset=utf-8;');
        header('Access-Control-Allow-Origin:*');
        echo(json_encode($_arr));
        \Yii::$app->end();
    }
  

  

}
