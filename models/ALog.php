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
class ALog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%a_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time', 'type', 'uid'], 'integer'],
            [['operation'], 'string'],
            [['c_name', 'a_name'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'create_time' => 'Create Time',
            'operation' => 'Operation',
            'type' => 'Type',
            'c_name' => 'C Name',
            'a_name' => 'A Name',
            'uid' => 'Uid',
        ];
    }
}
