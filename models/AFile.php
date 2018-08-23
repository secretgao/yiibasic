<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "a_file".
 *
 * @property string $id
 * @property int $uid 用户id
 * @property int $type 文件类型 1图片 2视频 3附件 4 笔记
 * @property string $name 文件名
 * @property string $ext 文件后缀
 * @property int $status 文件状态 0 正常  1删除
 * @property int $create_time 创建时间
 * @property string $path 文件路径
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
        return 'a_file';
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
            [['name'], 'string', 'max' => 100],
            [['ext'], 'string', 'max' => 5],
            [['path', 'size'], 'string', 'max' => 200],
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
            'id' => 'ID',
            'uid' => 'Uid',
            'type' => 'Type',
            'name' => 'Name',
            'ext' => 'Ext',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'path' => 'Path',
            'project_id' => 'Project ID',
            'catalog_id' => 'Catalog ID',
            'size' => 'Size',
            'exif_date' => 'Exif Date',
            'exif_latitude' => 'Exif Latitude',
            'exif_longitude' => 'Exif Longitude',
            'gps_latitude' => 'Gps Latitude',
            'gps_longitude' => 'Gps Longitude',
            'remark' => 'Remark',
        ];
    }
}
