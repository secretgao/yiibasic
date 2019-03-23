<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "a_project_follow".
 *
 * @property int $id
 * @property int $project_id 项目id
 * @property int $uid 关注人
 * @property int $create_time 关注时间
 * @property int $state 状态 0关注 -1取消关注
 * @property int $update_time
 */
class AProjectFollow extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'a_project_follow';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['project_id', 'uid', 'create_time'], 'required'],
            [['project_id', 'uid', 'create_time', 'update_time'], 'integer'],
            [['state'], 'string', 'max' => 3],
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
            'uid' => 'Uid',
            'create_time' => 'Create Time',
            'state' => 'State',
            'update_time' => 'Update Time',
        ];
    }


    /**
     * 获取项目关注人数
     */
    public static function getFollowNum($projectId,$uid = 0){

        if (empty($uid)){
            $where = ['project_id'=>$projectId,'state'=>1];
        } else {
            $where = ['project_id'=>$projectId,'uid'=>$uid,'state'=>1];
        }

        return intval(self::find()->where($where)->count());

    }
}
