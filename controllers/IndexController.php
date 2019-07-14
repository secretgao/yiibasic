<?php

namespace app\controllers;

use app\commond\Constants;
use app\commond\helps;
use app\commond\Imgcompress;
use app\models\AFile;
use app\models\ALog;
use app\models\AProject;
use app\models\AUser;
use phpDocumentor\Reflection\Project;
use Yii;
use yii\db\Query;


class IndexController extends BasicController
{
        
    
    public function init(){
       parent::init();
    }
    
    public function actionIndex(){
        phpinfo();
            echo 'aaaa';exit();
    }


    public function actionGetLogType()
    {
        $logType = Constants::$operationType;
        $this->Success(['data'=>$logType]);
    }

    public function actionGetLog()
    {
        $userId = $this->getParam('userId',false);
        $type   = $this->getParam('type',false);

        $data = ALog::find()
            ->select('create_time,operation,type,uid')
            ->andFilterWhere(['uid'=>$userId])
            ->andFilterWhere(['type'=>$type])
            ->asArray()->all();


        foreach ($data as &$item) {
            $item['create_time'] = date('Y-m-d H:i:s',$item['create_time']);
            $item['username'] = AUser::getName($item['uid']);
            $item['type'] = isset(Constants::$operationType[$item['type']]) ?
                Constants::$operationType[$item['type']] : '无';
        }
        $this->Success(['data'=>$data]);
    }

    /**
     * 隐私政策
     * @return string
     */
    public function actionPrivacyPolicy()
    {
        $this->layout=false;
        return $this->render('privacy-policy');
    }



    /**
     * 修复数据
     */
    public function actionHasFile()
    {

        $pages = $this->getParam('p');
        $pageSize = 3;
        $page = $pageSize * ($pages - 1);
        $data = (new Query())->select('project_id,catalog_id')
            ->from('a_file')->where(['status' => 1])
            ->offset($page)->limit($pageSize)->all();
        if (empty($data)) {
            $this->Success(['data' => 'empty']);
        }
        foreach ($data as $item) {
            $re = helps::uploadFileUpdateProjectModel($item['project_id'], $item['catalog_id']);
        }
        //  echo '<pre>';print_r($data);
    }


    /**
     * 修复数据
     */
    public function actionProject()
    {

        $pages = $this->getParam('p');
        $pageSize = 20;
        $page = $pageSize * ($pages - 1);
        $data = (new Query())->select('*')
            ->from('a_project')//->where(['status' => 1])
            ->offset($page)->limit($pageSize)->all();
        if (empty($data)) {
            $this->Success(['data' => 'empty']);
        }
        foreach ($data as $item) {
            //$re = helps::uploadFileUpdateProjectModel($item['project_id'],
              //  $item['catalog_id']);

            //更新项目表模板数量
            helps::UpdateProjectModelNum($item['id']);

            $file_agree_num = 0;

            //项目所选模板数量
            $catalog_id_arr = helps::getProjectModelBottomNum($item['id']);
            $file_agree_num = (int)helps::getProjectAgreeFileNum
            ($item['id'],$catalog_id_arr);
            $finish_progress = 0;
            $model_num = count($catalog_id_arr);


            $num = helps::getProjectModelBottomNum($item['id']);
            $project = AProject::findOne($item['id']);
            $project->model_num = count($num);
            $project->file_agree_num = $file_agree_num;
            $project->save(false);


            $item['file_agree_num'] = intval($item['file_agree_num']);
            $item['model_num'] = intval($item['model_num']);

        }
        //  echo '<pre>';print_r($data);
    }
    /**
     * http://www.bjwxapp.cn
     * tnes  项目 征文启事
     * @return array
     *
     */
    public function actionMessage()
    {
        $this->layout=false;
        return $this->render('message');

    }


    public function actionFileGenerateSmall()
    {
        ini_set('memory_limit','1024M');
        $pages = $this->getParam('p');
        $pageSize =3;
        $page = $pageSize * ($pages - 1);
       // echo '<pre>';
        ini_set("gd.jpeg_ignore_warning", 1);
        $file = AFile::find()
            ->select('id,uid,path,compress_path,ext')
            ->where(['type'=>1,'compress_path'=>''])
            ->offset($page)->limit($pageSize)
            ->asArray()->all();
        var_dump($file);
//exit();
        if (empty($file)) {
            $this->Success(['data' => '0']);
        }


//echo '<hr>';
        foreach ($file as $item){

            if ($item['ext'] == 'bmp' ){
                continue;
            }
            //var_dump($item);exit();
            $fileUploadDir = './uploads'.DIRECTORY_SEPARATOR.$item['uid'].DIRECTORY_SEPARATOR.date('Y').DIRECTORY_SEPARATOR.date('m').DIRECTORY_SEPARATOR.date('d').DIRECTORY_SEPARATOR.date('H');
            if (!file_exists($fileUploadDir)){
                mkdir($fileUploadDir,0777,true);
            }
            $small_img = $fileUploadDir.DIRECTORY_SEPARATOR.date('YmdHis').$item['uid'].'.'.$item['ext'];
            helps::img_create_small($item['path'],150,120, $small_img);

            //生成压缩图
            $compress_img =  $fileUploadDir.DIRECTORY_SEPARATOR.date('YmdHis').'ys'.$item['uid'].'.'.$item['ext'];
            $source = iconv("UTF-8", "GBK", $item['path']);
            $dst_img = $compress_img;//压缩后图片的名称
            $percent = 1;  #原图压缩，不缩放，但体积大大降低
            (new Imgcompress($source,$percent))->compressImg($dst_img);
            $update = AFile::findOne($item['id']);
            $update->small_path = $small_img;
            $update->compress_path = $compress_img;
            $update->save(false);
       //     var_dump($small_img);
        //    var_dump($compress_img);

        }

        $this->Success(['data' =>1]);
       // return parent::actions(); // TODO: Change the autogenerated stub
       // $small_img = $fileInfo['fileInfo']['uploadDir'].DIRECTORY_SEPARATOR.date('YmdHis').$userId.'.'.$fileInfo['fileInfo']['ext'];
        //helps::img_create_small($fileInfo['fileInfo']['path'],150,120, $small_img);

    }


    //项目决策资料、项目管理资料、项目绩效资料数据修复
    public function actionProjectFile(){

        set_time_limit(0);

//        $update_data=Helps::projectFile(1677);
//        var_dump($update_data);exit;
//        AProject::updateAll($update_data, ['id'=> 1677]);

        $pages = $this->getParam('p',false,1);
        $pageSize = 20;
        $page = $pageSize * ($pages - 1);
        $data = (new Query())->select('id')
            ->from('a_project')
            ->offset($page)->limit($pageSize)->all();


        if (empty($data)) {
            $this->Success(['data' => 'empty']);
        }
        foreach ($data as $item) {

            $update_data=Helps::projectFile($item['id']);

            AProject::updateAll($update_data, ['id'=> $item['id']]);

        }

        $this->Success(['data' => 'sucess']);
    }


    public function actionUpdateProjectFileById()
    {
        $projectId = $this->getParam('id',true);
        $update_data=Helps::projectFile($projectId);
        AProject::updateAll($update_data, ['id'=> $projectId]);
        $this->Success(['data' => 'sucess']);

    }
}
