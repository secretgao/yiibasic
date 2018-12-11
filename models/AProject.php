<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%a_project}}".
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
 * @property int $secretary_tag_id 项目书记标签id
 * @property string $financial_number 项目财政编号
 * @property int $model_num 项目模板数量/分母
 * @property int $file_agree_num 文件通过数量/分子
 */
class AProject extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%a_project}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['start_time', 'end_time', 'create_time', 'update_time', 'members', 'create_uid', 'finish_time', 'position_id', 'secretary_tag_id', 'model_num', 'file_agree_num'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 50],
            [['allow_add'], 'string', 'max' => 2],
            [['status', 'sort'], 'string', 'max' => 3],
            [['year', 'financial_number'], 'string', 'max' => 20],
            [['model_id'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', '项目名称'),
            'start_time' => Yii::t('app', '项目开始时间'),
            'end_time' => Yii::t('app', 'End Time'),
            'create_time' => Yii::t('app', 'Create Time'),
            'update_time' => Yii::t('app', 'Update Time'),
            'allow_add' => Yii::t('app', '是否允许增加目录 0 不允许    1 允许'),
            'status' => Yii::t('app', '项目状态   0 未开始  1 进行中  2 已结束  3 暂停  4删除'),
            'description' => Yii::t('app', '项目描述'),
            'members' => Yii::t('app', '成员数量'),
            'create_uid' => Yii::t('app', '创建人'),
            'year' => Yii::t('app', '年份'),
            'sort' => Yii::t('app', '排序'),
            'model_id' => Yii::t('app', '选中模版id'),
            'finish_time' => Yii::t('app', '项目预计完成时间'),
            'position_id' => Yii::t('app', '项目所属部门id'),
            'secretary_tag_id' => Yii::t('app', '项目书记标签id'),
            'financial_number' => Yii::t('app', '项目财政编号'),
            'model_num' => Yii::t('app', '项目模板数量/分母'),
            'file_agree_num' => Yii::t('app', '文件通过数量/分子'),
        ];
    }
}
