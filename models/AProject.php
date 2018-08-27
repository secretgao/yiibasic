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
 * @property int $status 项目状态   0 未开始  1 进行中  2 已结束  3 暂停  4删除
 * @property string $description 项目描述
 * @property int $members 成员数量
 * @property int $create_uid 创建人
 * @property string $year 年份
 * @property int $sort 排序
 * @property string $model_id 选中模版id
 * @property int $finish_time 项目预计完成时间
 * @property int $position_id 项目所属部门id
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
            [['start_time', 'end_time', 'create_time', 'update_time', 'members', 'create_uid', 'finish_time', 'position_id'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 50],
            [['allow_add'], 'string', 'max' => 2],
            [['status', 'sort'], 'string', 'max' => 3],
            [['year'], 'string', 'max' => 20],
            [['model_id'], 'string', 'max' => 200],
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
            'year' => 'Year',
            'sort' => 'Sort',
            'model_id' => 'Model ID',
            'finish_time' => 'Finish Time',
            'position_id' => 'Position ID',
        ];
    }
}
