<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%a_app_version}}".
 *
 * @property int $id
 * @property string $version 版本号
 * @property int $system 1 ios 2 安卓
 * @property int $create_time
 */
class AAppVersion extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%a_app_version}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time'], 'integer'],
            [['version'], 'string', 'max' => 20],
            [['system'], 'string', 'max' => 3],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'version' => Yii::t('app', '版本号'),
            'system' => Yii::t('app', '1 ios 2 安卓'),
            'create_time' => Yii::t('app', 'Create Time'),
        ];
    }
}
