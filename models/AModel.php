<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%a_model}}".
 *
 * @property int $id
 * @property string $name
 * @property int $status 状态   0 正常  -1 删除
 * @property int $type 区分 0是 模版  1是目录 
 * @property int $create_time
 * @property int $update_time
 * @property int $pid 父id
 * @property int $project_id 项目id
 * @property int $create_uid 创建id
 * @property int $level 层级
 * @property string $remark 模块备注
 */
class AModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%a_model}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time', 'update_time', 'pid', 'project_id', 'create_uid', 'level'], 'integer'],
            [['name'], 'string', 'max' => 20],
            [['status', 'type'], 'string', 'max' => 2],
            [['remark'], 'string', 'max' => 100],
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
            'status' => 'Status',
            'type' => 'Type',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'pid' => 'Pid',
            'project_id' => 'Project ID',
            'create_uid' => 'Create Uid',
            'level' => 'Level',
            'remark' => 'Remark',
        ];
    }

    /**
     * 获取模版一级
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getFirstModels(){

        return self::find()->select('id,name')
            ->where([
                'status'=>0,
                'type'=>0,
                'level'=>1
            ])
            ->asArray()->all();
    }
}
