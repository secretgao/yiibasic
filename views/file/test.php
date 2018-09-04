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
    <input type="hidden" name="userId" value="11" />
    <input type="hidden" name="projectId" value="33" />
    <input type="hidden" name="catalogId" value="11" />
    <input type="hidden" name="exif_date" value="11" />
    <input type="hidden" name="exif_latitude" value="11" />
    <input type="hidden" name="exif_longitude" value="11" />
    <input type="hidden" name="gps_latitude" value="11" />
    <input type="hidden" name="gps_longitude" value="11" />
    <input type="hidden" name="type" value="1" />

    <input type="submit" value="上传文件" />
</form>
</body>
</html>

