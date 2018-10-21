<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "a_secretary_tag".
 *
 * @property int $id
 * @property string $name 书记名称
 * @property int $create_uid 创建者uid
 * @property int $create_time 创建时间
 */
class ASecretaryTag extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'a_secretary_tag';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_uid', 'create_time'], 'integer'],
            [['name'], 'string', 'max' => 20],
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
            'create_uid' => 'Create Uid',
            'create_time' => 'Create Time',
        ];
    }
}
