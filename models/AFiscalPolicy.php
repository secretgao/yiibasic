<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%a_project_money}}".
 */
class AFiscalPolicy extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%a_fiscal_policy}}';
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
            'date' => 'Date',
            'content' => 'Content',
            'status' => 'Status',
            'ratio' => 'Ratio',
            'project_id' => 'Project Id',
            'create_time' => 'Create Time',
        ];
    }


}
