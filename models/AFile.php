<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%a_file}}".
 *
 * @property int $id
 * @property int $uid 用户id
 * @property int $type 文件类型 1图片 2视频 3附件 4 笔记
 * @property string $name 文件名
 * @property string $ext 文件后缀
 * @property int $status 文件状态 0 正常  1删除
 * @property int $create_time 创建时间
 */
class AFile extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%a_file}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'create_time'], 'integer'],
            [['type'], 'required'],
            [['type', 'status'], 'string', 'max' => 3],
            [['name'], 'string', 'max' => 50],
            [['ext'], 'string', 'max' => 5],
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
            'type' => 'Type',
            'name' => 'Name',
            'ext' => 'Ext',
            'status' => 'Status',
            'create_time' => 'Create Time',
        ];
    }
}