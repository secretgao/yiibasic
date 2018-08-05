<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%a_project_ext}}".
 *
 * @property int $id
 * @property int $project_id 项目id
 * @property int $uid 参与id
 */
class AProjectExt extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%a_project_ext}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['project_id', 'uid'], 'integer'],
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
            'uid' => 'Uid',
        ];
    }
}
