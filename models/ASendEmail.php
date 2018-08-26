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
 * @property int $status 0 代打包 ，1代发送 2，发送成功
 * @property string $project_file 项目打包文件
 * @property int $pack_time 项目打包时间
 * @property string $project_name 项目名称
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
            [['project_id', 'create_time', 'send_time', 'pack_time'], 'integer'],
            [['address'], 'string', 'max' => 30],
            [['status'], 'string', 'max' => 3],
            [['project_file'], 'string', 'max' => 40],
            [['project_name'], 'string', 'max' => 50],
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
            'pack_time' => 'Pack Time',
            'project_name' => 'Project Name',
        ];
    }
}
