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

    //接口错误返回
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
    const PROJECT_PACK_FAIL         = 1014;
    const NOT_SYS_POSITION          = 1015;
    const PROJECT_MANAGE_EXITS      = 1016;
    const PROJECT_FINANCIAL_EXITS   = 1017;
    const PROJECT_MODEL_SON         = 1018;

    const REQUSET_NO_POST           = 2000;
    const REQUSET_NO_GET            = 2001;

    const PROJECT_EXISTS_FOLLOW     = 2002;

    const USER_PASSWORD_ERROR       = 3000;
    const INPUT_PASSWORD_ATYPISM    = 3001;

    //-----------相关内容---------
    public static $error_message = [
        self::REQUSET_NO_GET => '请用get方式请求',
        self::REQUSET_NO_POST => '请用post方式请求',
        self::RET_ERROR => '操作失败',
        self::RET_SUCCESS => '操作成功',
        self::DATA_NOT_FOUND =>'数据不存在',
        self::FILES_ALREADY_EXIST =>'文件已经存在',
        self::MEMBER_NO_EXITS =>'人员不在此项目里',
        self::USER_NOT_FOUND =>'用户不存在',
        self::POSITIONS_NOT_FOUND =>'部门不存在',
        self::PASSWORD_ERROR  => '密码错误',
        self::APPLY_NOT_FOUND => '申请不存在',
        self::USER_IS_EXITS  =>'用户已经注册过',
        self::EMAIL_IS_ERROR=>'邮箱不合法',
        self::PROJECT_NOT_FOUND =>'项目不存在',
        self::PROJECT_ALREADY_DEL =>'项目已经删除',
        self::PROJECT_PACK_FAIL =>'项目压缩失败，可能是网络原因，请重试',
        self::NOT_SYS_POSITION =>'不是系统职位',
        self::PROJECT_MANAGE_EXITS =>'项目负责人不存在',
        self::PROJECT_FINANCIAL_EXITS=>'项目财政编号已经存在',
        self::PROJECT_MODEL_SON=>'请先删除该模板下的模板',
        self::PROJECT_EXISTS_FOLLOW=>'数据存在',
        self::USER_PASSWORD_ERROR=>'密码错误',
        self::INPUT_PASSWORD_ATYPISM=>'两次密码不一致',
    ];

    //操作日志相关
    const OPERATION_MODEL   = 1;
    const OPERATION_CATE    = 2;
    const OPERATION_FILE    = 3;
    const OPERATION_PROJECT   = 4;
    const OPERATION_USER   = 5;
    const OPERATION_POSITION   = 6;

    public static $operationType = [

        self::OPERATION_MODEL =>'模板日志',
        self::OPERATION_CATE  =>'目录日志',
        self::OPERATION_USER  =>'用户日志',
        self::OPERATION_FILE  =>'文件日志',
        self::OPERATION_POSITION  =>'部门日志',

    ];

    //项目相关
    // `status` '项目状态   0 未开始  1 进行中  2 已结束  3 暂停 4删除',
    const PROJECT_NONSTARTER  = 0;
    const PROJECT_START  = 1;
    const PROJECT_END    = 2;
    const PROJECT_STOP   = 3;
    const PROJECT_DELETE = 4;

    public static $projectStatus = [
        self::PROJECT_NONSTARTER =>'未开始',
        self::PROJECT_START =>'进行中',
        self::PROJECT_END =>'已结束',
        self::PROJECT_STOP =>'暂停',
        self::PROJECT_DELETE =>'删除',
    ];


    //职位 不是部门-----主管,专管员,部门领导，普通职员
    const MEMBER = 0;            //普通职员
    const DIRECTOR = 1;          //主管
    const ADMINISTRATION = 2;    //专管员
    const LEADER = 3;            //部门领导


    public static $position = [
        self::MEMBER => '普通职员',
        self::DIRECTOR => '主管',
        self::ADMINISTRATION => '专管员',
        self::LEADER => '部门领导',

    ];

}
