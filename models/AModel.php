<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "a_model".
 *
 * @property int $id
 * @property string $content
 * @property int $status 状态   0 正常  -1 删除
 * @property int $create_time
 * @property int $update_time
 */
class AModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'a_model';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content'], 'string'],
            [['create_time', 'update_time'], 'integer'],
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
            'content' => 'Content',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }
}
