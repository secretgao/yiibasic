<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "a_project".
 *
 * @property int $id
 * @property string $name 项目名称
 * @property int $start_time 项目开始时间
 * @property int $end_time
 * @property int $create_time
 * @property int $update_time
 * @property int $allow_add 是否允许增加目录 0 不允许    1 允许
 * @property int $status 项目状态   0 未开始  1 进行中  2 已结束  3 暂停
 * @property string $description 项目描述
 * @property int $members 成员数量
 * @property int $create_uid 创建人
 */
class AProject extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'a_project';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['start_time', 'end_time', 'create_time', 'update_time', 'members', 'create_uid'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 50],
            [['allow_add'], 'string', 'max' => 2],
            [['status'], 'string', 'max' => 3],
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
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'allow_add' => 'Allow Add',
            'status' => 'Status',
            'description' => 'Description',
            'members' => 'Members',
            'create_uid' => 'Create Uid',
        ];
    }
}
