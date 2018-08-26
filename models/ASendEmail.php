<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%A_send_email}}".
 *
 * @property int $id
 * @property int $project_id 项目id
 * @property string $address 收件人
 * @property int $create_time 创建时间
 * @property int $send_time 发送时间
 * @property int $status 0 代发送 ，1发送
 * @property string $project_file 项目打包文件
 */
class ASendEmail extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%A_send_email}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['project_id', 'create_time', 'send_time'], 'integer'],
            [['address'], 'string', 'max' => 30],
            [['status'], 'string', 'max' => 3],
            [['project_file'], 'string', 'max' => 40],
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
            'address' => 'Address',
            'create_time' => 'Create Time',
            'send_time' => 'Send Time',
            'status' => 'Status',
            'project_file' => 'Project File',
        ];
    }
}
