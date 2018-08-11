<?php
namespace app\commond;
/**
 * 常量
 */

final class Constants
{

    const ADMIN_USER                = 'admin';
    const TEST_USER                 = 'test';
    const PASSWORD_ERROR            = 999;

    const RET_SUCCESS               = 0;
    const RET_ERROR                 = 1000;
    const GLOBAL_INVALID_PARAM      = 1001;


    const DATA_NOT_FOUND            = 1004;
    const FILES_ALREADY_EXIST       = 1005;
    const MEMBER_NO_EXITS           = 1006;
    const USER_NOT_FOUND            = 1007;
    const POSITIONS_NOT_FOUND       = 1008;
    const APPLY_NOT_FOUND           = 1009;
    const USER_IS_EXITS             = 1010;
    const EMAIL_IS_ERROR            = 1011;
    const PROJECT_NOT_FOUND         = 1012;
    const PROJECT_ALREADY_DEL       = 1013;

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
        self::USER_NOT_FOUND =>'用户不存在',
        self::POSITIONS_NOT_FOUND =>'部门不存在',
        self::PASSWORD_ERROR  => '密码错误',
        self::APPLY_NOT_FOUND => '申请不存在',
        self::USER_IS_EXITS  =>'用户已经注册过',
        self::EMAIL_IS_ERROR=>'邮箱不合法',
        self::PROJECT_NOT_FOUND =>'项目不存在',
        self::PROJECT_ALREADY_DEL =>'项目已经删除'
    ];


    const OPERATION_MODEL   = 1;
    const OPERATION_CATE    = 2;
    const OPERATION_FILE    = 3;
    const OPERATION_PROJECT   = 4;
    const OPERATION_USER   = 5;

    public static $operationType = [

        self::OPERATION_MODEL =>'模板日志',
        self::OPERATION_CATE  =>'目录日志',
        self::OPERATION_USER  =>'用户日志',




    ];

}
