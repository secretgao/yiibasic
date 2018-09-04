<?php

namespace app\controllers;

use Yii;

use yii\web\Controller;
use yii\web\Response;
use app\commond\Constants;
use app\commond\helps;

class BasicController extends Controller
{
    
    public static $request;
    
    public function init(){
        $this->enableCsrfValidation = false;
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
 
    /**
     * 获取页面传参
     * @param type $key
     * @param type $is_need
     * @param type $default_value
     * @return type
     */
    public function getParam($key, $is_need = true, $default_value = NULL)
    {
        $notCheck = ['file','url','log_content','email'];

        if (in_array($key,$notCheck)){
            $val = self::$request->get($key);
        } else {
            $val = self::replace_specialChar(self::$request->get($key));
        }
        if ($val === NULL || $val === '')
        {
            if (in_array($key,$notCheck)){
                $val = self::$request->post($key);
            } else {
                $val = self::replace_specialChar(self::$request->post($key));
            }

        }

        if ( ($is_need && $val === NULL) || ($is_need && $val === '') )
        {
            $this->Error(Constants::GLOBAL_INVALID_PARAM, 'required param: ' . $key);
        }
      
        return $val!== NULL ? $val : $default_value;
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
        echo(json_encode($_arr));exit();
        \Yii::$app->end();
    }


    /**
     *判断是否为POST 请求
     */
    public function isPost (){

        if ( !self::$request->isPost ) {
            $this->Error(Constants::REQUSET_NO_POST, Constants::$error_message[Constants::REQUSET_NO_POST]);
        }


    }

    /**
     * 判断是否为GET 请求
     */
    public function isGet() {

        if ( !self::$request->isGet ) {
            $this->Error(Constants::REQUSET_NO_GET, Constants::$error_message[Constants::REQUSET_NO_GET]);
        }
    }

    /**
     * @param $strParam
     * @return mixed
     * 完美过滤特殊字符串
     */
    public static function replace_specialChar($strParam){
     
        return helps::replace_specialChar($strParam);
      //  $regex = "/\/|\～|\，|\。|\！|\？|\“|\”|\【|\】|\『|\』|\：|\；|\《|\》|\’|\‘|\ |\·|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\.|\/|\;|\'|\`|\-|\=|\\\|\|/";
      //  return preg_replace($regex,"",$strParam);
    }

}
