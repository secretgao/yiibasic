<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%a_position}}".
 *
 * @property int $id ä¸»é”®
 * @property string $name æ˜µç§°
 * @property int $status çŠ¶æ€:0 æ­£å¸¸  -1 åˆ é™¤
 * @property int $create_time åˆ›å»ºæ—¶é—´
 * @property int $pid çˆ¶çº§id
 */
class APosition extends  \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%a_position}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time', 'pid'], 'integer'],
            [['name'], 'string', 'max' => 50],
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
            'status' => 'Status',
            'create_time' => 'Create Time',
            'pid' => 'Pid',
        ];
    }

    /**
     * 返回所有父类
     * @return array
     */
    public static function  getAll(){
        return self::find()->select('id,name')
            ->where(['status'=>0,'pid'=>0])->asArray()->all();
    }

    /**
     * @param $pid
     * 根据pid 返回所有子类
     */
    public static function getChildren($pid){

        if(!$pid){
            return false;
        }
        return self::find()->select('id,name')
            ->where(['status'=>0,'pid'=>$pid])->asArray()->all();

    }

    /**获取职位名称
     * @param $id
     * @return array
     */
    public static function getPositionName($id){

        if(!$id){
            return false;
        }
        return self::find()->select('name')
            ->where(['status'=>0,'id'=>$id])->asArray()->one();

    }

    /**
     * 获取所有部门
     */
    public static function getAllPosition(){

        return self::find()->select('id,name')
            ->where(['status'=>0,'pid'=>0])->orderBy('sort_id ASC')
            ->asArray()->all();
    }

}
