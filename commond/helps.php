<?php
namespace app\commond;

use app\models\AFile;
use app\models\ALog;
use app\models\AModel;
use app\models\APersonalLog;
use app\models\AProject;
use app\models\AProjectModel;
use app\models\AUser;
use yii\db\Query;

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
                $tmp['type']  = 0;
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
 
        $data = AModel::find()->select('id,name,pid,remark as describe')
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

        $result = AModel::find()->select('id,name,pid,remark as describe')
            ->where(['pid'=>$id,'status'=>0])->asArray()->one();

        if (empty($data)) {
            return  $result;
        }

        return self::getChildren($data['pid'],$result);
    }


    public static function recursion($res)
    {
        $output = array();
        foreach ($res as $k => $v)
        {
            $tmpRes = AModel::find()->select('id,name,pid,remark as describe')
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
        $log->uid = $uid;

        $log->insert();
    }

    /**获取项目中的所有文件
     * @param $projectId
     */

    public static function getProjectAllFile($projectId,$status = 0){

        $result = [];
        if (empty($projectId)) {
            return $result;
        }

        $file = AFile::find()->select('id,catalog_id as pid,type,FROM_UNIXTIME(create_time) as uploadTime,path,name ')
            ->where(['status'=>$status,'project_id'=>$projectId])
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
            //    $item['name'] = iconv("UTF-8", "GBK", $item['name']);
               //汉字转码 防止乱码
             //   iconv("UTF-8", "GBK", $item['name']);   //汉字转码 防止乱码
                $newPath = $path.DIRECTORY_SEPARATOR.$item['name'];
               // echo '<pre>';print_r($newPath).PHP_EOL;
                if (!is_dir($newPath)) {
                    mkdir($newPath,0777,true);
                }

                if (!empty($allFiles)) {
                    //循环项目文件 ，放到指定目录下
                    foreach ($allFiles as $k=>$value){
                        if ($pid == $value['pid']) {
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


    /**
     * @param $source
     * @param $destination
     * @param $child
     * @return int
     *    //用法：
    // xCopy("feiy","feiy2",1):拷贝feiy下的文件到 feiy2,包括子目录
    // xCopy("feiy","feiy2",0):拷贝feiy下的文件到 feiy2,不包括子目录
    //参数说明：
    // $source:源目录名
    // $destination:目的目录名
    // $child:复制时，是不是包含的子目录
     */
    public  static  function xCopy($source, $destination, $child = 1){

        if (!is_dir($source)) {
             echo("Error:the $source is not a direction!");
             return 0;
        }

        if (!is_dir($destination)) {
           mkdir($destination,0777);
        }

        $handle = dir($source);
        while ($entry=$handle->read()) {
             if (($entry!=".")&&($entry!="..")) {
                 if (is_dir($source."/".$entry)) {
                 if ($child)
                     self::xCopy($source."/".$entry,$destination."/".$entry,$child);
                 } else{
                     copy($source."/".$entry,$destination."/".$entry);
                 }
             }
        }

     return 1;
    }

    /**
    *    版本号比较
    *    @param $version1 版本A 如:5.3.2
    *    @param $version2 版本B 如:5.3.0
    *    @return int -1版本A小于版本B , 0版本A等于版本B, 1版本A大于版本B
    *
    *    版本号格式注意：
    *        1.要求只包含:点和大于等于0小于等于2147483646的整数 的组合
    *        2.boole型 true置1，false置0
    *        3.不设位默认补0计算，如：版本号5等于版号5.0.0
    *        4.不包括数字 或 负数 的版本号 ,统一按0处理
    *
    *    @example:
    *       if (versionCompare('5.2.2','5.3.0')<0) {
    *            echo '版本1小于版本2';
    *       }
    */
    public  static  function versionCompare($versionA,$versionB) {
        if ($versionA>2147483646 || $versionB>2147483646) {
            throw new Exception('版本号,位数太大暂不支持!','101');
        }
        $dm = '.';
        $verListA = explode($dm, (string)$versionA);
        $verListB = explode($dm, (string)$versionB);

        $len = max(count($verListA),count($verListB));
        $i = -1;
        while ($i++<$len) {
            $verListA[$i] = intval(@$verListA[$i]);
            if ($verListA[$i] <0 ) {
                $verListA[$i] = 0;
            }
            $verListB[$i] = intval(@$verListB[$i]);
            if ($verListB[$i] <0 ) {
                $verListB[$i] = 0;
            }

            if ($verListA[$i]>$verListB[$i]) {
                return 1;
            } else if ($verListA[$i]<$verListB[$i]) {
                return 2;
            } else if ($i==($len-1)) {
                return 0;
            }
        }

    }



    /**
     * 获取项目所选模板
     */
    public static function CreateProjectRecursion($res)
    {
        $output = array();
        foreach ($res as $k => $v)
        {
            $tmpRes = AModel::find()->select('id,name,pid,level')
                ->where(['pid'=>$v['id'],'project_id'=>0,'status'=>0])
                ->asArray()->all();
            $output []= $v;
            if (!empty($tmpRes))
            {
                $output = array_merge($output, self::CreateProjectRecursion($tmpRes));
            }
        }
        return $output;
    }


    /**
     * 获取项目最底层模版所属文件已通过的数量
     * @param $projectId
     * @param $catalog_id_arr
     * @return int|string
     */
    public static  function getProjectAgreeFileNum($projectId,$catalog_id_arr){
        $total = 0;
        if (empty($projectId) || empty($catalog_id_arr)) {
            return $total;
        }

        $all = AFile::find()
            ->select('id')
            ->where(['project_id'=>$projectId,'status'=>1])
            ->andWhere(['catalog_id'=>$catalog_id_arr])
            ->groupBy('catalog_id')->count();
        return $all;
    }


    /**
     * 创建项目时把所选模板重新添加到项目扩展表里
     *
     */
    public static function CreateProjectModel($modelId,$projectId){

        if (empty($modelId)) {
            return false;
        }
        $modelArr = explode(',',$modelId);
        $model = AModel::find()->select('id,name,pid,level')
            ->where(['id'=>$modelArr,'status'=>0,'pid'=>0])->asArray()->all();

        $insertData = self::CreateProjectRecursion($model);

        foreach ($insertData as $item) {
            $exits = AProjectModel::find()
                ->where([
                    'project_id'=>$projectId,
                    'model_id'=>$item['id'],
                    'model_pid'=>$item['pid'],
                    'level'=>$item['level']
                ])->exists();
            if ($exits){
                continue;
            }
            $projectModel = new AProjectModel();
            $projectModel->project_id = $projectId;
            $projectModel->model_id = $item['id'];
            $projectModel->model_pid = $item['pid'];
            $projectModel->level  = $item['level'];
            $projectModel->create_time = time();
            if (!$projectModel->save()) {
                throwException($projectModel->getErrors());
            }
        }

        return true;
    }


    /**
     * 根据项目id获取项目最底层模版数量
     * @param $projectId
     */
    public static function getProjectModelBottomNum($projectId){

        $total = 0;
        if (empty($projectId)) {
            return $total;
        }

        //获取这个项目的所有模版
        $all = AProjectModel::find()
            ->select('model_id,model_pid,level')
            ->where(['project_id'=>$projectId,'type'=>0,'status'=>0])
            ->asArray()->all();

        $result = [];
        foreach ($all as $item){
            //查询这个模版下是否还有子集
            $exists = AProjectModel::find()->select('id')
                ->where(['project_id'=>$projectId,'type'=>0,'status'=>0,'model_pid'=>$item['model_id']])
                ->exists();
            //如果没有证明这个模板就是底层
            if (!$exists){
                $result[]=$item['model_id'];
            }
        }

        return $result;

    }

    /**
     * 获取项目所有的模板和目录
     */
    public static function getProjectModelAndCateLog($projectId){

        if (empty($projectId)) {
            return false;
        }

        $modelColumns = 'pm.model_id as id,pm.model_pid as pid,am.name,am.remark as describe,pm.level,am.type';
        $result = (new Query())
            ->select($modelColumns)
            ->from('a_project_model as pm')
            ->leftJoin('a_model as am','pm.model_id = am.id')
            ->where(['pm.project_id'=>$projectId])
            ->all();
        return $result;
    }

    /**
     * 生成个人工作日志
     * @param $path
     * @param $projectId
     */
    public static function createUserDiary($path,$projectId){
        $path = $path.DIRECTORY_SEPARATOR.'个人工作日志'.DIRECTORY_SEPARATOR;
        //创建项目个人工作日志目录
        if (!is_dir($path)) {
            mkdir($path,0777,true);
        }

        $log = APersonalLog::find()
            ->select('uid,content')
            ->where(['project_id'=>$projectId])
            ->andWhere(['<>','status',3])
            ->asArray()->all();

        foreach ($log as &$item) {
            $item['user'] = AUser::getName($item['uid']);
            $filename = $path.$item['user'].'.txt';
            file_put_contents($filename, $item['content'].PHP_EOL, FILE_APPEND);
        }
        return true;
    }


    /**
     *生成图片缩略图
     *
     */
    public  static function img_create_small($big_img, $width, $height, $small_img) {//原始大图地址，缩略图宽度，高度，缩略图地址
        $imgage = getimagesize($big_img); //得到原始大图片
        switch ($imgage[2]) { // 图像类型判断
            case 1:
                $im = imagecreatefromgif($big_img);
                break;
            case 2:
                $im = imagecreatefromjpeg($big_img);
                break;
            case 3:
                $im = imagecreatefrompng($big_img);
                break;
        }
        $src_W = $imgage[0]; //获取大图片宽度
        $src_H = $imgage[1]; //获取大图片高度
        $tn = imagecreatetruecolor($width, $height); //创建缩略图
        imagecopyresampled($tn, $im, 0, 0, 0, 0, $width, $height, $src_W, $src_H); //复制图像并改变大小
        imagejpeg($tn, $small_img); //输出图像
    }


    /**
     * 根据子目录查找 父级id
     * @param unknown $id
     */
    public static  function getParentsId($projectId,$id,$arr = [])
    {
        if (empty($id)){
            return $arr;
        }
        $data = AProjectModel::find()->select('id,model_pid')
            ->where(['project_id'=>$projectId,'model_id'=>$id,'status'=>0])->asArray()->one();

        if (!$data) {
            return false;
        }
        $arr[] = $data;
        if ($data['model_pid'] == 0){
            return  $arr;
        }
        return self::getParentsId($projectId,$data['model_pid'],$arr);
    }



    public static function uploadFileUpdateProjectModel($projectId,$id){

        if (!$projectId || !$id){
            return false;
        }
        $arr = [];
        $parentsId = helps::getParentsId($projectId,$id,$arr);
       // var_dump($parentsId);
        if (empty($parentsId)){
            return false;
        }
        $projectModelId = [];
        foreach ($parentsId as $item){
            $projectModelId[]=$item['id'];
        }
      //  echo '<pre>';print_r($projectModelId);
        AProjectModel::updateAll(['is_file'=>1],['id'=>$projectModelId]);
        return true;

    }


    /**
     * 更新项目模板数量
     * @param $projectId
     */
    public static function UpdateProjectModelNum($projectId){

        if (empty($projectId)){
            return false;
        }

        $num = self::getProjectModelBottomNum($projectId);
        $project = AProject::findOne($projectId);
        $project->model_num = count($num);
        $project->save(false);
        return true;
    }


    /**
     * @param $res
     * @return array
     */
    public static function recursionIsLook($res,$projectId)
    {
        $output = array();
        foreach ($res as $k => $v)
        {
            $tmpRes = AProjectModel::find()->select('id,model_id,model_pid')
                ->where(['model_id'=>$v['model_pid'],'project_id'=>$projectId,'status'=>0])
                ->asArray()->all();
            $output []= $v;
            if (!empty($tmpRes))
            {
                $output = array_merge($output, self::recursionIsLook($tmpRes,$projectId));
            }
        }
        return $output;
    }

}