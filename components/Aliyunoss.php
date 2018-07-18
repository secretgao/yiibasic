<?php
namespace app\components;

require_once __DIR__ . './../vendor/oss-sdk-php/autoload.php';
use Yii;
use yii\base\Component;
use OSS\OssClient;

class Aliyunoss extends Component
{
    public static $oss;
    
    public function __construct()
    {
        parent::__construct();
        $accessKeyId = Yii::$app->params['oss']['accessKeyId'];                 //获取阿里云oss的accessKeyId
        $accessKeySecret = Yii::$app->params['oss']['accessKeySecret'];         //获取阿里云oss的accessKeySecret
        $endpoint = Yii::$app->params['oss']['endPoint'];                       //获取阿里云oss的endPoint
        self::$oss = new OssClient($accessKeyId, $accessKeySecret, $endpoint);  //实例化OssClient对象
    }
    
    /**
     * 使用阿里云oss上传文件
     * @param $object   保存到阿里云oss的文件名
     * @param $filepath 文件在本地的绝对路径
     * @return bool     上传是否成功
     */
    public function upload($object, $filepath)
    {
        $res = false;
        $bucket = Yii::$app->params['oss']['bucket'];               //获取阿里云oss的bucket
        $res = self::$oss->uploadFile($bucket, $object, $filepath);
       /* if () {  //调用uploadFile方法把服务器文件上传到阿里云oss
            $res = true;
        }
        */
        return $res;
    }
    
    /**
     * 删除指定文件
     * @param $object 被删除的文件名
     * @return bool   删除是否成功
     */
    public function delete($object)
    {
        $res = false;
        $bucket = Yii::$app->params['oss']['bucket'];    //获取阿里云oss的bucket
        if (self::$oss->deleteObject($bucket, $object)){ //调用deleteObject方法把服务器文件上传到阿里云oss
            $res = true;
        }
        
        return $res;
    }
    
    /**
     * 上传大文件
     * @param unknown $object
     * @param unknown $file
     * @param unknown $options
     * @return NULL|boolean
     */
    
    public function multiuploadFile($object, $file, $options = null){
        $res = false;
        $bucket = Yii::$app->params['oss']['bucket'];    //获取阿里云oss的bucket
        $r = self::$oss->multiuploadFile($bucket, $object, $file, $options = null);
        
        return $r;
        if (self::$oss->multiuploadFile($bucket, $object, $file, $options = null)){ 
            $res = true;
        }
        
        return $res;
    }
    
    /**
     * 创建目录
     * @param unknown $object
     * @param unknown $options
     * @return NULL
     */
    public  function  createObjectDir( $object, $options = NULL){
        $bucket = Yii::$app->params['oss']['bucket'];    //获取阿里云oss的bucket
        $r = self::$oss->createObjectDir($bucket, $object,  $options = null);        
        return $r;     
    }
    /**
     * 获取bucket下的object列表
     *
     * @param string $bucket
     * @param array $options
     * 其中options中的参数如下
     * $options = array(
     *      'max-keys'  => max-keys用于限定此次返回object的最大数，如果不设定，默认为100，max-keys取值不能大于1000。
     *      'prefix'    => 限定返回的object key必须以prefix作为前缀。注意使用prefix查询时，返回的key中仍会包含prefix。
     *      'delimiter' => 是一个用于对Object名字进行分组的字符。所有名字包含指定的前缀且第一次出现delimiter字符之间的object作为一组元素
     *      'marker'    => 用户设定结果从marker之后按字母排序的第一个开始返回。
     *)
     * 其中 prefix，marker用来实现分页显示效果，参数的长度必须小于256字节。
     * @throws OssException
     * @return ObjectListInfo
     */
    public function listObjects( $options = null){
        $bucket = Yii::$app->params['oss']['bucket'];    //获取阿里云oss的bucket
        $r = self::$oss->listObjects($bucket, $options = null);
        return $r;
    }

  
}
?>