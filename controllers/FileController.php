<?php

namespace app\controllers;

use app\commond\Constants;
use app\models\APosition;
use app\models\AUser;
use Yii;
use app\models\AModel;

/**
 * 文件操作
 * @author Administrator
 *
 */

class FileController extends BasicController
{
        
    
    public function init(){
       parent::init();
    }

    /**
     * http://www.api.com/position/index
     * 获取
     */
    public function actionIndex(){

        $id   = $this->getParam('id',true);
        $Obj = AModel::findOne($id);

        if (!$Obj){
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);          
        }
        
        
        $this->Success(['data'=>$Obj->content]);

    }


    /**
     * 创建项目
     */
    
    public function actionAdd(){
        
        
        
        
    }
    
    /**
     * 编辑
     */
    public function actionEdit(){
        $this->isPost();
        $id      = $this->getParam('id',true);
        $content = $this->getParam('data',true);

        $Obj = AModel::findOne($id);

        if (!$Obj){
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);

        }

        $Obj->content = $content;
        $Obj->update_time = time();
        if ($Obj->save(false)) {
            $this->Success();
        }

        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }


    /**
     * 删除
     */
    public function actionDel(){
        $this->isPost();
        $id          = $this->getParam('id',true);
        $Obj = AModel::findOne($id);

        if (!$Obj){
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }

        $Obj->status =-1;
        if ($Obj->save(false)) {
            $this->Success();
        }

        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }




    /**
     * 多维数组转成二位数组
     */
    public function arrayToTweArr($data){

        echo '<pre>';print_r($data);

    }
    
    /**
     * 获取部门人数
     */
    public function actionGetPositionNumber(){
        
        $parent = APosition::getAll();
        if (! $parent) {
            $this->Error();
        }
        foreach ($parent as $k=>$item){
            $children = empty(APosition::getChildren($item['id'])) ? [] : APosition::getChildren($item['id']);
                  
             if ($children){
                foreach ($children as $key=>$value){
                    $children[$key]['number'] = AUser::find()->where(['position_id'=>$value['id']])->count();                    
                }
            } 
            $parent[$k]['children'] = $children;
        }
        
        $this->Success(['data'=>$parent]);
        
    }
}
