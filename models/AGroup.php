<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%a_log}}".
 *
 * @property int $id
 * @property int $create_time
 * @property string $operation 操作记录
 * @property int $type 操作类型
 * @property string $c_name 控制器名
 * @property string $a_name 方法名
 * @property int $uid 操作人id
 */
class AGroup extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%a_group}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['group_name','year'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'group_name' => 'Group Name',
            'year' => 'Year',
            'money' => 'Money',
            'project_ids' => 'Project Ids',
        ];
    }
}
