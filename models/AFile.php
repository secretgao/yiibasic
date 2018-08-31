<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%a_file}}".
 *
 * @property string $id
 * @property int $uid 用户id
 * @property int $type 文件类型 1图片 2视频 3附件 4 笔记
 * @property string $name 文件名
 * @property string $true_name 真实文件名
 * @property string $ext 文件后缀
 * @property int $status 文件状态 0带审核  1正常  2拒绝 3删除
 * @property int $create_time 创建时间
 * @property string $path 文件路径
 * @property string $small_path 图片和视频缩略图地址
 * @property int $project_id 项目id
 * @property int $catalog_id 目录id
 * @property string $size 文件大小
 * @property string $exif_date exif 日期时间
 * @property string $exif_latitude exif 纬度
 * @property string $exif_longitude exif 经度
 * @property string $gps_latitude gps 纬度
 * @property string $gps_longitude gps 经度
 * @property string $remark 备注
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
            [['uid', 'create_time', 'project_id', 'catalog_id'], 'integer'],
            [['type'], 'required'],
            [['remark'], 'string'],
            [['type', 'status'], 'string', 'max' => 3],
            [['name', 'true_name'], 'string', 'max' => 100],
            [['ext'], 'string', 'max' => 5],
            [['path', 'size'], 'string', 'max' => 200],
            [['small_path'], 'string', 'max' => 255],
            [['exif_date'], 'string', 'max' => 30],
            [['exif_latitude', 'exif_longitude', 'gps_latitude', 'gps_longitude'], 'string', 'max' => 60],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'uid' => Yii::t('app', '用户id'),
            'type' => Yii::t('app', '文件类型 1图片 2视频 3附件 4 笔记'),
            'name' => Yii::t('app', '文件名'),
            'true_name' => Yii::t('app', '真实文件名'),
            'ext' => Yii::t('app', '文件后缀'),
            'status' => Yii::t('app', '文件状态 0带审核  1正常  2拒绝 3删除'),
            'create_time' => Yii::t('app', '创建时间'),
            'path' => Yii::t('app', '文件路径'),
            'small_path' => Yii::t('app', '图片和视频缩略图地址'),
            'project_id' => Yii::t('app', '项目id'),
            'catalog_id' => Yii::t('app', '目录id'),
            'size' => Yii::t('app', '文件大小'),
            'exif_date' => Yii::t('app', 'exif 日期时间'),
            'exif_latitude' => Yii::t('app', 'exif 纬度'),
            'exif_longitude' => Yii::t('app', 'exif 经度'),
            'gps_latitude' => Yii::t('app', 'gps 纬度'),
            'gps_longitude' => Yii::t('app', 'gps 经度'),
            'remark' => Yii::t('app', '备注'),
        ];
    }
}
