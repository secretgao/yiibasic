<?php
/**
 * Created by PhpStorm.
 * User: gaoxy
 * Date: 2018/7/24
 * Time: 18:20
 */
?>
<html>
<body>
<form action="/file/uploads" method="post" enctype="multipart/form-data">
    <input type="file" name="file" />
    <input type="hidden" name="ext" value=".png" />
    <input type="submit" value="上传文件" />
</form>
</body>
</html>

