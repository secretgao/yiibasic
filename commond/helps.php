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
     
        $arr[] = $data;    
        if ($data['pid'] == 0){          
            return  $arr;       
        }       
        return self::getParents($data['pid'],$arr);
    } 
   
    /**
     * 根据子目录查找 父级
     * @param unknown $id
     */
    public static  function getParentss($cate,$id){
        
        $arr=array();
        foreach($cate as $v){
            if($v['id']==$id){
                $arr[]=$v;// $arr[$v['id']]=$v['name'];
                $arr=array_merge(self::getParentss($cate,$v['pid']),$arr);
            }
        }
        return $arr;
        
    }

    
    
    
    
    
    
    
    
    
    
    
    
    
    
}