<?php
/**
 * 处理上传文件
 * User: renyuchao
 * Date: 2018/4/9
 */

require_once("../public/pub.session.php");
require_once("../db/sycalc.user.core.php");
require_once("../db/sycalc.file.core.php");
require_once("../db/sycalc.fileinfo.core.php");
require_once("../functions/sycalc_fileupload.php");
header("Content-type: text/html; charset=utf-8"); 

$userId = '';
$userName = '';
$returnOut = !empty($_POST['returnOut']) ? $_POST['returnOut'] : '';
if (!empty($_SESSION['SYCALC_USER_CODE']) || !empty($_SESSION['SYCALC_USER_NAME'])) {
    $userId = intval($_SESSION['SYCALC_USER_CODE']);
    $userName = $_SESSION['SYCALC_USER_NAME'];
} else {
    $userId = !empty($_REQUEST['userId']) ? $_REQUEST['userId'] : '';
}

$fileUpload = new sycalc_fileupload();
$fileInfo = $fileUpload->getFileInfo($userId);
if ($returnOut == 'pc' && empty($fileInfo['errorMsg']) && $fileInfo['uploadFail'] == 0 && empty($fileInfo['fileInfo']['file']['errorMsg']))
{
	echo '<script type="text/javascript">alert("提交成功");window.location.href="/functions/sycalc_book_list.php";</script>';
} else if ($returnOut == 'pc' && (!empty($fileInfo['errorMsg']) || !empty($fileInfo['fileInfo']['file']['errorMsg']))){
	$errorMsg = !empty($fileInfo['errorMsg']) ? $fileInfo['errorMsg'] : (!empty($fileInfo['fileInfo']['file']['errorMsg']) ? $fileInfo['fileInfo']['file']['errorMsg']: '');
	echo '<script type="text/javascript">alert("'. $errorMsg .'");window.location.href="/upload_file.php";</script>';
} else {
	$returnData = array();
	$returnData['code'] = 0;
	$returnData['msg'] = 'succ';
	$returnData['data'] = $fileInfo;
	echo json_encode($returnData);
}
exit;