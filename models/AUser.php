<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "a_user".
 *
 * @property int $id 主键
 * @property string $nick_name 昵称
 * @property string $true_name 真实姓名
 * @property string $avatar 头像
 * @property int $status 状态:0 正常  -1 删除
 * @property int $create_time 创建时间
 * @property int $position_id 职位id
 * @property int $sex 状态:0 男  1女
 * @property string $phone 手机号
 * @property int $group 是否是超级管理员
 * @property string $weixin_id 微信id
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
            [['nick_name'], 'string', 'max' => 50],
            [['true_name', 'phone'], 'string', 'max' => 20],
            [['avatar'], 'string', 'max' => 255],
            [['status', 'sex', 'group'], 'string', 'max' => 3],
            [['weixin_id'], 'string', 'max' => 100],
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
}
