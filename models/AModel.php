<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%a_model}}".
 *
 * @property int $id
 * @property string $name
 * @property int $status 状态   0 正常  -1 删除
 * @property int $create_time
 * @property int $update_time
 * @property int $pid
 * @property int $project_id
 * @property int $create_uid
 */
class AModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%a_model}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time', 'update_time', 'pid', 'project_id', 'create_uid'], 'integer'],
            [['name'], 'string', 'max' => 20],
            [['status'], 'string', 'max' => 2],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'pid' => 'Pid',
            'project_id' => 'Project ID',
            'create_uid' => 'Create Uid',
        ];
    }
}
