<?php
namespace app\commond;

use app\models\AModel;

class helps {
    
   static function make_tree($arr){
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
    
    static function getson($arr,$pid=0,$level){
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
    public static  function getParents($id,$arr = []){           
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
   

    
    /**
     * 根据多个底层目录id 返回整个目录结构
     */
    public static function accordingCatalogToAllHierarchy($selectModuleIds){
        
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
    public static function replace_specialChar($strParam){
        
        $regex = "/\/|\～|\，|\。|\！|\？|\“|\”|\【|\】|\『|\』|\：|\；|\《|\》|\’|\‘|\ |\·|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\.|\/|\;|\'|\`|\-|\=|\\\|\|/";
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
    
    
    
    
    
    
}