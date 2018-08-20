<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "a_user".
 *
 * @property int $id 主键
 * @property string $nick_name 昵称
 * @property string $true_name 真实姓名
 * @property string $email 邮箱
 * @property string $avatar 头像
 * @property int $status 状态:0 正常  -1 删除
 * @property int $create_time 创建时间
 * @property int $position_id 职位id
 * @property int $sex 状态:0 男  1女
 * @property string $phone 手机号
 * @property int $group 是否是超级管理员
 * @property string $weixin_id 微信id
 * @property int $sys_position 系统职位
 */
class AUser extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'a_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time', 'position_id'], 'integer'],
            [['create_time', 'position_id', 'sys_position'], 'integer'],
            [['nick_name'], 'string', 'max' => 50],
            [['true_name', 'phone'], 'string', 'max' => 20],
            [['email'], 'string', 'max' => 60],

            'phone' => 'Phone',
            'group' => 'Group',
            'weixin_id' => 'Weixin ID',
            'sys_position' => 'Sys Position',
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nick_name' => 'Nick Name',
            'true_name' => 'True Name',
            'email' => 'Email',
            'avatar' => 'Avatar',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'position_id' => 'Position ID',
            'sex' => 'Sex',
            'phone' => 'Phone',
            'group' => 'Group',
            'weixin_id' => 'Weixin ID',
        ];
    }

    /**
     * 判断用户是否有部门
     */
    public static function getUserIsPosition($uid){

        if (empty($uid)) {
            return false;
        }

        $position = self::find()->select('position_id')->where(['id'=>$uid,'status'=>0])->scalar();

        if (empty($position)) {
            return false;
        }
        return true;
    }


    /**
     *
     * 返回用户名称
     */
    public static function getName($uid){

        if (empty($uid)){
            return 0;
        }

        $data = self::find()->select('nick_name,true_name')
            ->where(['id'=>$uid,'status'=>0])->asArray()->one();

        if (empty($data)) {
            return $uid;
        } else if (!empty($data['true_name'])) {
            return $data['true_name'];
        } else if (!empty($data['nick_name'])) {
            return $data['nick_name'];
        }
    }
}
