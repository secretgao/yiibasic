<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%a_position_apply}}".
 *
 * @property int $id
 * @property int $uid 用户id
 * @property int $position_id 部门id
 * @property int $create_time 申请时间
 * @property int $status 状态 0申请    1 通过  2 不通过 
 * @property int $update_time
 */
class APositionApply extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%a_position_apply}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'position_id', 'create_time', 'update_time'], 'integer'],
            [['status'], 'string', 'max' => 3],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => 'Uid',
            'position_id' => 'Position ID',
            'create_time' => 'Create Time',
            'status' => 'Status',
            'update_time' => 'Update Time',
        ];
    }
}
