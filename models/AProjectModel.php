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
 * @property int $type 区分 0是 模版  1是目录 
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
            [['type'], 'string', 'max' => 2],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'project_id' => Yii::t('app', '项目id'),
            'model_id' => Yii::t('app', '模型id'),
            'model_pid' => Yii::t('app', '模型pid'),
            'create_time' => Yii::t('app', 'Create Time'),
            'level' => Yii::t('app', '层级'),
            'type' => Yii::t('app', '区分 0是 模版  1是目录 '),
        ];
    }
}
