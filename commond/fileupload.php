<?php
/**
 * 文件上传类
 * User: renyuchao
 * Date: 2018/4/9
 */

namespace app\commond;
use app\models\AFile;

class fileupload
{
    const SUCCESS_CODE = 0;
    const OVERTOP_PHPINI_FILE_SIZE_CODE = 30001;
    const OVETOP_MAX_SIZE_CODE = 30002;
    const FILE_PARTIALLY_UPLOADED_CODE = 30003;
    const NO_FILE_UPLOAD_CODE = 30004;
    const FILE_SIZE_0_CODE = 30005;
    const FILE_UPLOAD_FAIL_CODE = 30006;

    const OVERTOP_PHPINI_FILE_SIZE_ERROR_MSG = '超出了php.ini中文件大小';
    const OVERTOP_MAX_SIZE_ERROR_MSG = '超出了MAX_FILE_SIZE的文件大小';
    const FILE_PARTIALLY_UPLOADED_ERROR_MSG = '文件被部分上传';
    const NO_FILE_UPLOAD_ERROR_MSG = '没有文件上传';
    const FILE_SIZE_0_ERROR_MSG = '文件大小为0';
    const FILE_UPLOAD_FAIL_ERROR_MSG = '上传失败';

    const NOT_INPUT_ERROR_CODE = 40001;
    const NOT_FOUND_FILE_ERROR_CODE = 40002;
    const NOT_INPUT_ERROR_MSG = '请输入提取码';
    const NOT_FOUND_FILE_ERROR_MSG = '未找到相关文件';

    const PLEASE_LOGIN_ERROR_CODE = 50001;
    const PLEASE_LOGIN_ERROR_MSG = '请登录';
    private $uploadDir = './uploads';//上传文件的存储目录
/*    const UPLOAD_AVATAR_DIR ='/uploadAvatar';//上传头像存储目录
    const UPLOAD_COVER_IMAGE_DIR ='/coverImages';//上传封面图存储目录
    const UPLOAD_BANNER_DIR ='/banner';//上传栏目banner存储目录
    const UPLOAD_PIC_DIR = '/uploadPics';//上传插图存储目录
*/
    /**
     * 获取上传文件类型
     * @param $filename
     * @return mixed
     */
    public static function getFileType($filename)
    {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        return $ext;
    }

    /**
     * 获取上传文件错误信息
     * @param $data
     * @return array
     */
    public function getFileErrorMsg($data)
    {
        $errorId = '';
        $errorMsg = '';
        switch($data['error']) {
            case 0:
                $errorId = self::SUCCESS_CODE;
                $errorMsg = '';
                break;
            case 1:
                $errorId = self::OVERTOP_PHPINI_FILE_SIZE_CODE;
                $errorMsg = self::OVERTOP_PHPINI_FILE_SIZE_ERROR_MSG;
                break;
            case 2:
                $errorId = self::OVETOP_MAX_SIZE_CODE;
                $errorMsg = self::OVERTOP_MAX_SIZE_ERROR_MSG;
                break;
            case 3:
                $errorId = self::FILE_PARTIALLY_UPLOADED_CODE;
                $errorMsg = self::FILE_PARTIALLY_UPLOADED_ERROR_MSG;
                break;
            case 4:
                $errorId = self::NO_FILE_UPLOAD_CODE;
                $errorMsg = self::NO_FILE_UPLOAD_ERROR_MSG;
                break;
            case 5:
                $errorId = self::FILE_SIZE_0_CODE;
                $errorMsg = self::FILE_SIZE_0_ERROR_MSG;
                break;
            default:
                $errorId = self::FILE_UPLOAD_FAIL_CODE;
                $errorMsg = self::FILE_UPLOAD_FAIL_ERROR_MSG;
                break;
        }
        return array('errorId'=>$errorId, 'errorMsg'=>$errorMsg);
    }


    /**
     * 处理上传文件并获取图片基本信息
     * @param int $userId
     * @param int $projectId 根据项目id 筛选 文件是否有重复
     * @return array
     */
    public function getFileInfo($userId = 0,$projectId = 0)
    {
        $fileInfoArr = array();//多维数组
        $fileNewArr = array();
        if (!empty($userId)) {

            $uploadSucc = 0;
            $uploadFail = 0;
            $chinese = array();
            //判断指定上传文件存储目录是否存在  /uploads/用户id/年/月/日/时
            $fileUploadDir = $this->uploadDir.DIRECTORY_SEPARATOR.$userId.DIRECTORY_SEPARATOR.date('Y').DIRECTORY_SEPARATOR.date('m').DIRECTORY_SEPARATOR.date('d').DIRECTORY_SEPARATOR.date('H');

            if (!empty($_FILES)) {
                foreach ($_FILES as $key => $val) {
                    preg_match_all("/[\x80-\xff]+/", $_FILES[$key]['name'], $chinese);//匹配上传文件名称是否包含中文字符
                    //echo '<br>chinese=';var_dump($chinese);
                    array_filter($chinese);//过滤空元素

                    $fileInfoArr[$key] = $val;
                    $fileErrorMsg = self::getFileErrorMsg($fileInfoArr[$key]);

                    if (!empty($fileErrorMsg) && empty($fileErrorMsg['errorMsg'])) {
                        $fileInfoArr[$key]['ok'] = true;
                    } else {
                        $fileInfoArr[$key]['ok'] = false;
                        $fileInfoArr[$key]['serverTime'] = time();
                        $fileInfoArr[$key]['errorId'] = $fileErrorMsg['errorId'];
                    }
                    $fileInfoArr[$key]['errorMsg'] = $fileErrorMsg['errorMsg'];

                    if (!empty($fileInfoArr[$key]) && empty($fileErrorMsg['errorMsg']) && ($fileErrorMsg['errorId'] == 0)) {
                        //判断上传的是图片 or 文本 or 文档
                        $typePos = mb_strpos($val['type'], 'image');
                        if (is_numeric($typePos)) {//图片
                            $imageInfo = self::getImageInfo($val['tmp_name']);
                            $fileInfoArr[$key] = array_merge($fileInfoArr[$key], $imageInfo);
                        }


                        if (!file_exists($fileUploadDir)){
                            mkdir($fileUploadDir,0777,true);
                        }

                        $name = $fileInfoArr[$key]['name'];
                        $fileInfoArr[$key]['true_name'] = $name;
                        //查询文件名是否存在 如果存在 重新定义文件名为xxx(1).ext
                        $fileRenameNum = AFile::find()->where(['true_name'=>$name,'project_id'=>$projectId])->count();
                        if ($fileRenameNum && $name) {
                            $ext = pathinfo($fileInfoArr[$key]['name'], PATHINFO_EXTENSION);
                            $start = strlen($ext) + 1;
                            $name =  substr($fileInfoArr[$key]['name'],0,-$start);
                            $fileInfoArr[$key]['name'] = $name.'('.$fileRenameNum.')'.'.'.$ext;
                        }

                        $uploadFileName = $fileUploadDir .DIRECTORY_SEPARATOR. "_{$fileInfoArr[$key]['name']}";//重新命名文件名称

                        $fileInfoArr[$key]['path'] = $uploadFileName;//文件存储路径
                        if (!empty($chinese)) {//包含有中文字符需要转码
                            $uploadFileName = iconv("UTF-8", "GBK", $uploadFileName);   //先转换名字为GBK编码
                        }

                        $returnRes = self::uploadFileToTargetDir($fileInfoArr[$key], $uploadFileName);
                        if ($returnRes) {
                            $uploadSucc++;
                            $fileInfoArr[$key]['isDone'] = 0;
                        } else {
                            $uploadFail++;
                            $fileInfoArr[$key]['isDone'] = 1;
                        }

                        if (!empty($fileInfoArr[$key]) && !empty($fileErrorMsg['errorMsg'])) {
                            $uploadFail++;
                        }
                    }
                }

                $fileNewArr['userId'] = $userId;
                $fileNewArr['name'] = !empty($fileInfoArr['file']['name']) ? $fileInfoArr['file']['name'] : '';
                $fileNewArr['true_name'] = !empty($fileInfoArr['file']['true_name']) ? $fileInfoArr['file']['true_name'] : '';
                $fileNewArr['path'] = !empty($fileInfoArr['file']['path']) ? $fileInfoArr['file']['path'] : '';
                $fileNewArr['type'] = !empty($fileInfoArr['file']['type']) ? $fileInfoArr['file']['type'] : '';
                $fileNewArr['size'] = !empty($fileInfoArr['file']['size']) ? $fileInfoArr['file']['size'] : '';
                $fileNewArr['ext']  =  pathinfo($fileInfoArr['file']['name'], PATHINFO_EXTENSION);
                $fileNewArr['uploadDir']  =  $fileUploadDir;

                return array('status'=>0, 'fileInfo'=>$fileNewArr, 'uploadSucc' => $uploadSucc, 'uploadFail' => $uploadFail);
            } else {
                return array('errorId'=>self::NO_FILE_UPLOAD_CODE, 'errorMsg'=>self::NO_FILE_UPLOAD_ERROR_MSG);
            }
        } else {
            return array('errorId'=>self::PLEASE_LOGIN_ERROR_CODE, 'errorMsg'=>self::PLEASE_LOGIN_ERROR_MSG);
        }
    }

    /**
     * 上传文件到指定目录
     * @param $data
     * @param $uploadFileName
     * @return bool
     */
    public function uploadFileToTargetDir($data, $uploadFileName)
    {
        if (is_uploaded_file($data['tmp_name'])) {
            move_uploaded_file($data['tmp_name'],   $uploadFileName);//将缓存文件移动到指定位置
            //unlink($fileInfoArr[$key]['tmp_name']);//删除临时文件
            return true;
        } else {
            return false;
        }
    }

    /**
     * 根据上传临时文件获取图片信息，图像详细信息如下：
     * 0 - 图像宽度的像素值
     * 1 - 图像高度的像素值
     * 2 - 图像的类型，返回为数字，1 = GIF，2 = JPG，3 = PNG，
     *                4 = SWF，5 = PSD，6 = BMP，
     *                7 = TIFF(intel byte order)，8 = TIFF(motorola byte order)，9 = JPC，
     *                10 = JP2，11 = JPX，12 = JB2，13 = SWC，14 = IFF，15 = WBMP，16 = XBM
     * 3 - 宽度和高度的字符串
     * bits - 图像的颜色的位数，二进制格式
     * channels - 图像的通道值，RGB图像默认是3
     * mime - 图像的MIME信息
     */
    public static function getImageInfo($FileTmpName)
    {
        $imageInfo = array();
        $list = getimagesize($FileTmpName);
        $imageInfo['width'] = !empty($list[0]) ? $list[0] : '';
        $imageInfo['height'] = !empty($list[1]) ? $list[1] : '';
        $imageInfo['type'] = !empty($list[2]) ? $list[2] : '';
        $imageInfo['attr'] = !empty($list[3]) ? $list[3] : '';
        $imageInfo['bits'] = !empty($list['bits']) ? $list['bits'] : '';
        $imageInfo['channels'] = !empty($list['channels']) ? $list['channels'] : '';
        $imageInfo['mime'] = !empty($list['mime']) ? $list['mime'] : '';
        return $imageInfo;
    }

    /**
     * 组装文件存储信息
     * @param $data
     * @return array
     */
    public static function packSycFile($data)
    {
        $packData = array();
        $packData['extraction_code'] = $data['codeId'];
        $packData['name'] = $data['name'];
        $packData['path'] = $data['path'];
        $packData['title'] = $data['title'];
        $packData['brief'] = $data['brief'];
        $packData['author'] = $data['author'];
        $packData['tag'] = $data['tag'];
        $packData['cover_image'] = $data['cover_image'];
        $packData['create_time'] = strtotime(date('Y-m-d H:i:s'));
        $packData['update_time'] = strtotime(date('Y-m-d H:i:s'));
        return $packData;
    }

    /**
     * 组装文件基本信息
     * @param $data
     * @return array
     */
    public static function packSycFileInfo($data)
    {
        $packData = array();
        $packData['name'] = $data['name'];
        $packData['title'] = $data['title'];
        $packData['type'] = $data['type'];
        $packData['size'] = $data['size'];
        $packData['source'] = $data['source'];
        $packData['is_done'] = $data['isDone'];
        $packData['bytes_processed'] = $data['size'];
        $packData['brief'] = $data['brief'];
        $packData['err_msg'] = $data['errorMsg'];
        $packData['code_id'] = $data['codeId'];
        $packData['tag'] = $data['tag'];
        $packData['author'] = $data['author'];
        $packData['create_time'] = strtotime(date('Y-m-d H:i:s'));
        $packData['update_time'] = strtotime(date('Y-m-d H:i:s'));
        return $packData;
    }

    /**
     * 根据文件路径下载文件
     * @param $file
     */
    public static function downloadFile($file)
    {
        /*
        $fileName = $file;

        echo '<br>' . __FUNCTION__ . ' fileName_begin=';var_dump(basename($fileName));

        echo '<br>' . __FUNCTION__ . ' fileName_begin=';var_dump($fileName);

        echo '<br>file=' . __FILE__ ;
        echo '<br>file_dir=' . dirname(dirname(__FILE__)) . '/uploadFile/4/20180505071020_FTP及MYSQL信息.txt';
        echo '<br>dir=' . __DIR__ ;
        echo '<br>' . __FUNCTION__ . ' filesize=';var_dump(filesize(dirname(dirname(__FILE__)) . '/uploadFile/4/20180505071020_FTP及MYSQL信息.txt'));

        $mime = 'application/force-download';
        header('Pragma: public'); // required
        header('Expires: 0'); // no cache
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private',false);
        header('Content-Type: '.$mime);
        header('Content-Disposition: attachment; filename="'.basename($fileName).'"');
        header('Content-Transfer-Encoding: binary');
        header('Connection: close');
        //readfile($fileName);
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, $fileName);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT,10);
        $img = curl_exec($ch);
        echo $img;
        */

        $fileName = $file;
        $filePath = WEB_DOMAIN . $fileName;
        $fileDirPath = dirname(dirname(__FILE__)) . $fileName;
        preg_match_all("/[\x80-\xff]+/", $fileDirPath, $chinese);//匹配上传文件名称是否包含中文字符
        preg_match_all("/[\x80-\xff]+/", $filePath, $chinese_);//匹配上传文件名称是否包含中文字符
        array_filter($chinese);//过滤空元素
        array_filter($chinese_);//过滤空元素
        if (!empty($chinese)){
            $fileDirPath = iconv("UTF-8", "GBK", $fileDirPath);   //先转换名字为GBK编码
        }
        if (!empty($chinese_)){
            $filePath = iconv("UTF-8", "GBK", $filePath);   //先转换名字为GBK编码
        }
        $mime = 'application/force-download';
        header('Pragma: public'); // required
        header('Expires: 0'); // no cache
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private',false);
        header('Content-Type: '.$mime);
        header('Content-Length: '. filesize($fileDirPath));
        header('Content-Disposition: attachment; filename="'.basename($fileName).'"');
        header('Content-Transfer-Encoding: binary');
        header('Connection: close');
        //readfile($fileName);
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, $filePath);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT,10);
        $img = curl_exec($ch);
        echo $img;

        exit;
    }

    /**
     * 根据提取码下载对应文件
     * @param $extractionCode
     * @return array
     */
    public static function downloadFileByCode($extractionCode)
    {
        $data = array();
        if (!empty($extractionCode))
        {
            $sycfileObj = new sycalc_file_core();
            $result = $sycfileObj->getFileInfoByExtractionCode($extractionCode);
            if (!empty($result) && !empty($result['path'])) {
                $filePath = $result['path'];
                self::downloadFile($filePath);
            } else {
                $data['ok'] = false;
                $data['serverTime'] = time();
                $data['errorId'] = self::NOT_FOUND_FILE_ERROR_CODE;
                $data['errorMsg'] = self::NOT_FOUND_FILE_ERROR_MSG;
            }
        } else {
            $data['ok'] = false;
            $data['serverTime'] = time();
            $data['errorId'] = self::NOT_INPUT_ERROR_CODE;
            $data['errorMsg'] = self::NOT_INPUT_ERROR_MSG;
        }
        return $data;
    }

    /**
     * 根据文件字节数换算KB,MB,GB,TB
     * @param $num
     * @return string
     */
    public function getFilesize($num)
    {
        $p = 0;
        $format = 'bytes';
        if($num > 0 && $num < 1024){
            $p = 0;
            return number_format($num).' '.$format;
        }
        if($num >= 1024 && $num < pow(1024, 2)){
            $p = 1;
            $format = 'KB';
        }
        if ($num >= pow(1024, 2) && $num < pow(1024, 3)) {
            $p = 2;
            $format = 'MB';
        }
        if ($num >= pow(1024, 3) && $num < pow(1024, 4)) {
            $p = 3;
            $format = 'GB';
        }
        if ($num >= pow(1024, 4) && $num < pow(1024, 5)) {
            $p = 3;
            $format = 'TB';
        }
        $num /= pow(1024, $p);
        return number_format($num, 3) . $format;
    }

    public function getFileErrorByOnlyReadStream($fileInfo)
    {
        $error = '';
        $errorId = '';
        $errorMsg = '';

        $maxFileSize = ini_get('upload_max_filesize');
        $curFileSize = !empty($fileInfo['Filedata']['size']) ? $this->getFilesize($fileInfo['Filedata']['size']) : 0;
        $maxFileSize = str_ireplace('M', '', $maxFileSize);
        $curFileSize = str_ireplace('MB', '', $curFileSize);
        $curFileSize = str_ireplace('M', '', $curFileSize);

        if (!empty($fileInfo['Filedata']['name']) && !empty($fileInfo['Filedata']['type'])) {
            if (intval($curFileSize) > intval($maxFileSize))
            {
                $error = 1;
                $errorId = self::OVERTOP_PHPINI_FILE_SIZE_CODE;
                $errorMsg = self::OVERTOP_PHPINI_FILE_SIZE_ERROR_MSG;
            } else if (intval($curFileSize) > 0 && intval($curFileSize) <= intval($maxFileSize)) {
                $error = 0;
                $errorId = 0;
                $errorMsg = '';
            } else if (intval($curFileSize) == 0){
                $error = 5;
                $errorId = self::FILE_SIZE_0_CODE;
                $errorMsg = self::FILE_SIZE_0_ERROR_MSG;
            } else {
                $error = 6;
                $errorId = self::FILE_UPLOAD_FAIL_CODE;
                $errorMsg = self::FILE_UPLOAD_FAIL_ERROR_MSG;
            }
        } else {
            $error = 4;
            $errorId = self::NO_FILE_UPLOAD_CODE;
            $errorMsg = self::NO_FILE_UPLOAD_ERROR_MSG;
        }
        return array('error'=>$error, 'errorId'=>$errorId, 'errorMsg'=>$errorMsg);
    }

}