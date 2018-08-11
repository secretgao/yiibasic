<?php
namespace app\commond;

use app\models\AFile;
use app\models\ALog;
use app\models\AModel;
use app\models\AProject;
use app\models\AUser;
class helps {
    
   static function make_tree($arr)
   {
        $refer = array();
        $tree = array();
        foreach($arr as $k => $v){
            $refer[$v['id']] = & $arr[$k]; //创建主键的数组引用
        }
        foreach($arr as $k => $v){
            $pid = $v['pid'];  //获取当前分类的父级id
            if($pid == 0){
                $tree[] = & $arr[$k];  //顶级栏目
            }else{
                if(isset($refer[$pid])){
                    $refer[$pid]['nodeList'][] = & $arr[$k]; //如果存在父级栏目，则添加进父级栏目的子栏目数组中
                }
            }
        }
        return $tree;
   }
    
   static function getson($arr,$pid=0,$level)
   {
        static $res;//静态变量 只会被初始化一次
        foreach($arr as $k=>$v){
            $ctid = intval($v['pid']);
            $cid = intval($v['id']);
            if($ctid === $pid){
                $tmp = $v;
                $tmp['level'] = $level;
                $res[] = $tmp;
                self::getson($arr,$cid,$level+1);
            }
        }
        return $res;
   }
    
    /**
     * 根据子目录查找 父级
     * @param unknown $id
     */
    public static  function getParents($id,$arr = [])
    {
        if (empty($id)){
            return $arr;
        }
 
        $data = AModel::find()->select('id,name,pid')
        ->where(['id'=>$id,'status'=>0])->asArray()->one();

        if (!$data) {
            return false;
        }
        $arr[] = $data;    
        if ($data['pid'] == 0){          
            return  $arr;       
        }       
        return self::getParents($data['pid'],$arr);
    }


    /**根据顶级id 获取所有子集
     * @param $id
     */
    public static function getChildren($id,$result = []){

        if (empty($id)){
            return $result;
        }

        $result = AModel::find()->select('id,name,pid')
            ->where(['pid'=>$id,'status'=>0])->asArray()->one();

        if (empty($data)) {
            return  $result;
            //return false;
        }
        //$arr[] = $data;
        return self::getChildren($data['pid'],$result);
    }


    public static function recursion($res)
    {
        $output = array();
        foreach ($res as $k => $v)
        {
            $tmpRes = AModel::find()->select('id,name,pid')
                ->where(['pid'=>$v['id'],'project_id'=>0,'status'=>0])
                ->asArray()->all();
            $output []= $v;
            if (!empty($tmpRes))
            {
                $output = array_merge($output, self::recursion($tmpRes));
            }
        }
        return $output;
    }
    
    /**
     * 根据多个底层目录id 返回整个目录结构
     */
    public static function accordingCatalogToAllHierarchy($selectModuleIds)
    {
        
        $result = $temp = [];
        if (empty($selectModuleIds)){
            return  $result;
        }
        //目录id 切割成数组
        $catalogIdArr = explode(',', $selectModuleIds);
        
        $catalogArr = [];  //去除重复目录用
        foreach ($catalogIdArr as $id){
            $catalog = self::getParents($id);
            foreach ($catalog as $item){
                //去除重复
                if (!in_array($item['id'], $catalogArr)) {
                    $temp[] = $item;
                    $catalogArr[]= $item['id'];
                }
            }
        }
        
        $level = self::getson($temp,0,1);  //附上层级
        $result = self::make_tree($level);
        return  $result;
    }
    
    
    
    /**
     * @param $strParam
     * @return mixed
     * 完美过滤特殊字符串
     */
    public static function replace_specialChar($strParam)
    {
        $regex = "/\/|\～|\，|\。|\！|\？|\“|\”|\【|\】|\『|\』|\：|\；|\《|\》|\’|\‘|\ |\·|\~|\!|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\/|\;|\'|\`|\-|\=|\\\|\|/";
        return preg_replace($regex,"",$strParam);
    }
    
    /**
     * 计算2个时间差 
     * @param unknown $begin_time
     * @param unknown $end_time
     * @return string
     */
    
    public static function  timediff( $begin_time, $end_time )
    {
        if ( $begin_time < $end_time ) {
            $starttime = $begin_time;
            $endtime = $end_time;
        } else {
            $starttime = $end_time;
            $endtime = $begin_time;
        }
        $timediff = $endtime - $starttime;
        $days = intval( $timediff / 86400 );
        $remain = $timediff % 86400;
        $hours = intval( $remain / 3600 );
        $remain = $remain % 3600;
        $mins = intval( $remain / 60 );
        $secs = $remain % 60;
        
        $str = '';
        if ($days){
            $str.= $days.'天';
        }
        if ($hours){
            $str.= $hours.'小时';
        }
        if ($mins){
            $str.= $mins.'分';
        }
        if ($secs){
            $str.= $secs.'秒';
        }
        
        // $res = array( "day" => $days, "hour" => $hours, "min" => $mins, "sec" => $secs );
        return $str;
    }


    /**
     * 记录日志
     * @param $message
     */
    public static function writeLog($type,$message,$uid){

        $msg = '操作人:';
        $msg.= AUser::getName($uid);

        $controllerName =  \Yii::$app->controller->id;
        $actionName =  \Yii::$app->controller->action->id;
        $log = new ALog();
        $log->type = $type;
        $log->c_name = $controllerName;
        $log->a_name = $actionName;
        $log->create_time = time();
        $log->operation = $msg.$message;
        $log->insert();
    }

    /**
     * 根据控制器和方法 获取操作类型
     * @param $cName
     * @param $aName
     */

    public static function operationType($cName,$aName){



    }


    /**获取所有模版
     * @param $projectId
     * @return array
     */

    public static function allStep($projectId){
        $project = AProject::find()->select('model_id')
            ->where(['id'=>$projectId])->asArray()->one();
        $modelIdArr = explode(',', $project['model_id']);
        $top = $step = [];
        //说明是顶级返回所有子集
        if (count($modelIdArr) == 1) {
            $top = self::getParents($project['model_id']);
        }

        $toparr = [];
        if ($top) {
            foreach ($top as $item){
                if ($item['pid'] == 0){
                    $toparr[] = $item;
                }
            }

            $all = self::recursion($toparr);
            $step  = self::getson($all,0,1);
        }

        return $step;
    }


    /**获取项目中的所有文件
     * @param $projectId
     */

    public static function getProjectAllFile($projectId){

        $result = [];

        if (empty($projectId)) {
            return $result;
        }

        $file = AFile::find()->select('catalog_id as cid,path,name')
            ->where(['status'=>0,'project_id'=>$projectId])
            ->asArray()->all();

        if (empty($file)) {
            return $result;
        }

        return $file;
    }

    /**创建目录
     * DIRECTORY_SEPARATOR
     * @param $path   路径
     * @param $param  所有模板加目录
     * @param $allfiles 项目下的所有文件
     * @param $pid    上层id
     */
    public static function createDirectory($path,$param,$allFiles,$pid){

        foreach ($param as $key=>$item) {
            if ($item['pid'] == $pid) {
               // $item['name'] = iconv("UTF-8", "GBK", $item['name']);
               //汉字转码 防止乱码
                $newPath = $path.DIRECTORY_SEPARATOR.$item['name'];
               // echo $newPath.PHP_EOL;
                if (!is_dir($newPath)) {
                    mkdir($newPath,0777,true);
                }

                if (!empty($allFiles)) {
                    //循环项目文件 ，放到指定目录下
                    foreach ($allFiles as $k=>$value){
                        if ($pid == $value['cid']) {
                            self::copyToDir($value['path'],$newPath,$value['name']);
                            unset($allFiles[$k]);
                        }
                    }
                }

                $id = $item['id'];
                unset($param[$key]);
                self::createDirectory($newPath,$param,$allFiles,$id);
            }
        }
    }

    /**复制文件到指定目录
     * @param $sourcefile
     * @param $dir
     * @param $filename
     * @return bool
     */
    public static function copyToDir($sourcefile, $dir,$filename){
        if( ! file_exists($sourcefile)) {
            return false;
        }
        return copy($sourcefile, $dir .DIRECTORY_SEPARATOR. $filename);
    }
}