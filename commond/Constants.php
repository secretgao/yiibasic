<?php
namespace app\commond;
/**
 * 常量
 */

final class Constants
{

    const RET_SUCCESS               = 0;
    const RET_ERROR                 = 1000;
    const GLOBAL_INVALID_PARAM      = 1001;


    const DATA_NOT_FOUND             = 1004;
    const FILES_ALREADY_EXIST       = 1005;
    const MEMBER_NO_EXITS           = 1006;


    const REQUSET_NO_POST           = 2000;
    const REQUSET_NO_GET            = 2001;


    //-----------相关内容---------
    public static $error_message = [
        self::REQUSET_NO_GET => '请用get方式请求',
        self::REQUSET_NO_POST => '请用post方式请求',
        self::RET_ERROR => '操作失败',
        self::RET_SUCCESS => '操作成功',
        self::DATA_NOT_FOUND =>'数据不存在',
        self::FILES_ALREADY_EXIST =>'文件已经存在',
        self::MEMBER_NO_EXITS =>'要删除的人员不在此项目里',
    ];

}
