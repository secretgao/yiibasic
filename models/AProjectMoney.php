<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%a_project_money}}".
 */
class AProjectMoney extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%a_project_money}}';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'project_id' => 'Project Id',
            'money' => 'Money',
            'create_time' => 'Create Time',
        ];
    }


}
