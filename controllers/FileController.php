<?php

namespace app\controllers;

use app\commond\helps;
use app\components\Aliyunoss;

use app\models\AFile;
use app\models\AProject;
use app\models\AProjectExt;
use Yii;
use app\commond\Constants;
use app\commond\fileupload;
/**
 * 文件操作
 * @author Administrator
 *
 */

class FileController extends BasicController
{

    public function init()
    {
       parent::init();
    }

    /**
     * 上传
     * /usr/local/var/www/basic/README.md
     */
    /*
    public function actionUpload(){
//`type` tinyint(3) DEFAULT NULL COMMENT '文件类型 1图片 2视频 3附件 4 笔记'
        $uid = $this->getParam('userId',true);
        $type = $this->getParam('type',true);
        $filePath = $this->getParam('filePath',true);
        $catalogId = $this->getParam('catalogId',true);
        $projectId = $this->getParam('projectId',true);
        $ext = $this->getParam('ext',true);
        $fileName = $this->getParam('fileName',true);
        $mainPath  = md5($uid);
        $typePath = '/'.md5($uid.$type);
        $fileNameExist = AFile::find()->select('id')->where(['name'=>$fileName,'status'=>0])->scalar();

        if ($fileNameExist){
          $this->Error(Constants::FILES_ALREADY_EXIST,Constants::$error_message[Constants::FILES_ALREADY_EXIST]);
        }

        $uploadRes = \YII::$app->Aliyunoss->upload($fileName.$ext,$filePath);

        if ($uploadRes['info'] && $uploadRes['info']['http_code'] == 200) {
            $file = new AFile();
            $file->uid = $uid;
            $file->type = $type;
            $file->name = $fileName;
            $file->ext = $ext;
            $file->path = $filePath;
            $file->create_time = time();
            $file->project_id = $projectId;
            $file->catalog_id = $catalogId;

            if ($file->insert()) {
                $this->Success();
            }
        }

        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }

    
    public function actionFileList(){
        $uid = $this->getParam('userId',true);

        $columns = '*';
        $file = AFile::find()->select($columns)->where(['uid'=>$uid])->asArray()->all();

        if (!$file){
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }
        $this->Success(['data'=>$file]);

    }

    public function actionDelFile()
    {
        $uid = $this->getParam('userId',true);
        $fileId = $this->getParam('fileId',true);

        $file = AFile::findOne(['id'=>$fileId,'uid'=>$uid,'status'=>0]);

        if (!$file){
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }
        $delRes = \YII::$app->Aliyunoss->delete($file->name.$file->ext);

        $file->status = 1;
        if ($file->save(false)){
            $this->Success();
        }
        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);

    }
*/

    public function actionTest()
    {
        return $this->render('test');
    }

    public function actionUploads()
    {
        $this->isPost();
        $userId = $this->getParam('userId',true);
        $projectId = $this->getParam('projectId',true);
        $catalogId = $this->getParam('catalogId',true);
        $type = $this->getParam('type',true);
        $exifDate = $this->getParam('exif_date',true);
        $exifLatitude = $this->getParam('exif_latitude',true);
        $exifLongitude = $this->getParam('exif_longitude',true);
        $gpsLatitude = $this->getParam('gps_latitude',true);
        $gpsLongitude = $this->getParam('gps_longitude',true);
        $comments = $this->getParam('comments',true);
        $fileUpload = new fileupload();
        $fileInfo = $fileUpload->getFileInfo($userId);
       
        if (isset($fileInfo['status'])) {
            $file = new AFile();
            $file->uid = $userId;
            $file->type = $type;
            $file->name = $fileInfo['fileInfo']['name'];
            $file->ext = $fileInfo['fileInfo']['ext'];
            $file->create_time = time();
            $file->path = $fileInfo['fileInfo']['path'];
            $file->project_id = $projectId;
            $file->catalog_id = $catalogId;
            $file->size = (string)$fileInfo['fileInfo']['size'];
            $file->exif_date = $exifDate;
            $file->exif_latitude = $exifLatitude;
            $file->exif_longitude = $exifLongitude;
            $file->gps_latitude = $gpsLatitude;
            $file->gps_longitude = $gpsLongitude;
            $file->remark = $comments;
            if ($file->save()) {
                $msg = '上传文件:'.$fileInfo['fileInfo']['name'];
                helps::writeLog(Constants::OPERATION_FILE,$msg,$userId);
                $this->Success(array_merge($fileInfo,array('project_id'=>$projectId),array('catalog_id'=>$catalogId)));
            } else {
                $this->Error(Constants::RET_ERROR,$file->getErrors());//Constants::$error_message[Constants::RET_ERROR]
            }
        }
        $this->Error($fileInfo['errorId'],$fileInfo['errorMsg']);
    }



    /**
     * 用户上传过的列表
     * @return array
     */
    public function actionUserFileList()
    {
        $this->isPost();
        $uid = $this->getParam('userId',true);

        $file = AFile::find()->select('id,type,name,create_time,size,project_id as projectId')->where(['uid'=>$uid,'status'=>0])
            ->asArray()->all();

        if (!$file){
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }
        foreach ($file as $key=>$item){
            if (empty($item['projectId'])){
                $file[$key]['projectId'] = '0';
            }
            $project = AProject::find()->select('name')->where(['id'=>$item['projectId']])->scalar();
            $file[$key]['projectName'] = empty($project) ? '' : $project;

        }

        $this->Success(['data'=>$file]);
    }


    /**
     * 用户上传过的列表
     * @return array
     */
    public function actionFileList()
    {

        $uid = $this->getParam('userId',true);

        $column = 'id,type,name,create_time,size,project_id as projectId';
        $file = AFile::find()->select($column)
            ->where(['uid'=>$uid,'status'=>0])
            ->asArray()->all();

        if (!$file) {
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }
        foreach ($file as $key=>$item) {
            if (empty($item['projectId'])){
                $file[$key]['projectId'] = '0';
            }
            $project = AProject::find()->select('name')->where(['id'=>$item['projectId']])->scalar();
            $file[$key]['projectName'] = empty($project) ? '' : $project;
            $file[$key]['size'] = intval($item['size']);

        }
        $this->Success(['data'=>$file]);
    }

    /**
     * 文件下载
     */

    public function actionDownload()
    {

        ob_clean();
        $fileId = $this->getParam('fileId',true);
        $userId = $this->getParam('userId',true);
        $file = AFile::find()->select('*')
            ->where(['id'=>$fileId,'status'=>0,'uid'=>$userId])
            ->asArray()->one();

        if (!$file) {
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }
        $msg = '下载文件:'.$file['name'];
        helps::writeLog(Constants::OPERATION_FILE,$msg,$userId);
        //用以解决中文不能显示出来的问题
        $path = iconv("utf-8","gb2312",$file['path']);
        $file_path = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.$path;

        //首先要判断给定的文件存在与否
        if(!file_exists($file_path)) {
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }

        // 使用basename函数可以获得文件的名称而不是路径信息，保护了服务器的目录安全性
        header("content-disposition:attachment;filename=".basename($file_path));
        header("content-length:".filesize($file_path));
        readfile($file_path);
        exit();

    }


    /**
     * 删除文件
     */

    public function actionDel()
    {
        $fileId = $this->getParam('fileId',true);
        $userId = $this->getParam('userId',true);
        $file = AFile::find()->where(['id'=>$fileId,'uid'=>$userId])
            ->andWhere(['<>','status',2])
            ->one();
        if (!$file) {
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }

        $file->status = '2';
        if ($file->save(false)) {
            $this->Success();
        } else {
            $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
        }
    }

    /**
     * 项目负责人审核文件
     * @return array
     */
    public function actionCheckFile()
    {
        $projectId = $this->getParam('projectId',true);
        $fileId    = $this->getParam('fileId',true);
        $userId    = $this->getParam('manageId',true);
        $status    = $this->getParam('state',true); //审核状态 2拒绝 1通过

        if (!in_array($status,[1,2])){
            $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
        }
        $project = AProject::find()
            ->where(['id'=>$projectId])
            ->andwhere(['<>','status',4])
            ->exists();
        if (!$project) {
            $this->Error(Constants::PROJECT_NOT_FOUND,Constants::$error_message[Constants::PROJECT_NOT_FOUND]);
        }

        $projectExt = AProjectExt::find()
            ->where(['project_id'=>$projectId,'uid'=>$userId,'is_manage'=>1])
            ->exists();
        if (!$projectExt) {
            $this->Error(Constants::PROJECT_MANAGE_EXITS,Constants::$error_message[Constants::PROJECT_MANAGE_EXITS]);
        }

        $file = AFile::find()->where(['id'=>$fileId,'project_id'=>$projectId,'status'=>0])->one();

        if (!$file) {
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }

        $file->status= $status;
        if ($file->save(false)) {
            $this->Success();
        } else {
            $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
        }
    }

}
