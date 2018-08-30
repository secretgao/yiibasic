<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%a_project_model}}".
 *
 * @property int $id
 * @property int $project_id 项目id
 * @property int $model_id 模型id
 * @property int $model_pid 模型pid
 * @property int $create_time
 * @property int $level 层级
 */
class AProjectModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%a_project_model}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['project_id', 'model_id', 'model_pid', 'create_time', 'level'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'project_id' => 'Project ID',
            'model_id' => 'Model ID',
            'model_pid' => 'Model Pid',
            'create_time' => 'Create Time',
            'level' => 'Level',
        ];
    }
}
