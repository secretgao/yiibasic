<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%a_user}}".
 *
 * @property int $id ä¸»é”®
 * @property string $nick_name æ˜µç§°
 * @property string $true_name çœŸå®žå§“å
 * @property string $img å¤´åƒ
 * @property int $status çŠ¶æ€:0 æ­£å¸¸  -1 åˆ é™¤
 * @property int $create_time åˆ›å»ºæ—¶é—´
 * @property int $position_id èŒä½id
 */
class AUser extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%a_user}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time', 'position_id'], 'integer'],
            [['position_id'], 'required'],
            [['nick_name'], 'string', 'max' => 50],
            [['true_name'], 'string', 'max' => 20],
            [['img'], 'string', 'max' => 255],
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
            'nick_name' => 'Nick Name',
            'true_name' => 'True Name',
            'img' => 'Img',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'position_id' => 'Position ID',
        ];
    }
}
