<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "a_message".
 *
 * @property int $id
 * @property int $uid
 * @property string $content
 * @property int $create_time
 */
class AMessage extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'a_message';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'create_time'], 'integer'],
            [['content'], 'string'],
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
            'content' => 'Content',
            'create_time' => 'Create Time',
        ];
    }
}
