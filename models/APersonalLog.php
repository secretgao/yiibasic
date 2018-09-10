<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%a_personal_log}}".
 *
 * @property int $id
 * @property int $uid 创建人
 * @property int $project_id 项目id
 * @property string $content 内容
 * @property int $create_time 创建时间
 * @property int $status 0 正常 1 删除  2 完成
 * @property int $update_time 更新时间
 */
class APersonalLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%a_personal_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'project_id', 'create_time', 'update_time'], 'integer'],
            [['content'], 'string'],
            [['status'], 'string', 'max' => 3],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'uid' => Yii::t('app', '创建人'),
            'project_id' => Yii::t('app', '项目id'),
            'content' => Yii::t('app', '内容'),
            'create_time' => Yii::t('app', '创建时间'),
            'status' => Yii::t('app', '0 正常 1 删除  2 完成'),
            'update_time' => Yii::t('app', '更新时间'),
        ];
    }
}
