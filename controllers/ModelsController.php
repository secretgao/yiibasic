<?php

namespace app\controllers;

use app\commond\Constants;
use app\models\APosition;
use app\models\AUser;
use Yii;
use app\models\AModel;


class ModelsController extends BasicController
{
        
    
    public function init(){
       parent::init();
    }

    /**
     * http://www.api.com/position/index
     * 获取
     */
    public function actionIndex(){

        $id   = $this->getParam('id',true);
        $Obj = AModel::findOne($id);

        if (!$Obj){
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);          
        }
        
        
        $this->Success(['data'=>$Obj->content]);

    }


    /**
     *  添加
     * http://www.api.com/models/add
     */
    public function actionAdd(){

        $arr['data'] = [
            0=>[
                'nodeName'=>'模块',
                'level'=>'1',
                'id'=>1,
                'isSelected'=>false,
                'nodeList'=>[
                    0=>[
                        'nodeName'=>'是钱追呗2',
                        'level'=>'2',
                        'id'=>'1_0',
                        'isSelected'=>true,
                        'nodeList'=>[
                            0=>[
                                'nodeName'=>'是钱追呗3',
                                'level'=>'3',
                                'id'=>'1_0_0',
                                'isSelected'=>true,
                                'nodeList'=>[

                                ],
                            ],
                            0=>[
                                'nodeName'=>'是钱追呗3',
                                'level'=>'3',
                                'id'=>'1_0_1',
                                'isSelected'=>true,
                                'nodeList'=>[

                                ],
                            ]
                        ],
                    ],
                    1=>[
                        'nodeName'=>'是钱追呗2',
                        'level'=>'2',
                        'id'=>'1_1',
                        'isSelected'=>false,
                        'nodeList'=>[
                            0=>[
                                'nodeName'=>'是钱追呗3',
                                'level'=>'3',
                                'id'=>'1_1_0',
                                'isSelected'=>true,
                                'nodeList'=>[

                                ],
                            ],
                            0=>[
                                'nodeName'=>'是钱追呗3',
                                'level'=>'3',
                                'id'=>'1_1_1',
                                'isSelected'=>true,
                                'nodeList'=>[

                                ],
                            ]
                        ],
                    ]
                ],
            ],
            1=>[
                'nodeName'=>'模块',
                'level'=>'1',
                'id'=>2,
                'isSelected'=>false,
                'nodeList'=>[
                    0=>[
                        'nodeName'=>'是钱追呗2',
                        'level'=>'2',
                        'id'=>'2_0',
                        'isSelected'=>true,
                        'nodeList'=>[
                            0=>[
                                'nodeName'=>'是钱追呗3',
                                'level'=>'3',
                                'id'=>'2_0_0',
                                'isSelected'=>true,
                                'nodeList'=>[

                                ],
                            ],
                            1=>[
                                'nodeName'=>'是钱追呗3',
                                'level'=>'3',
                                'id'=>'2_0_1',
                                'isSelected'=>true,
                                'nodeList'=>[

                                ],
                            ]
                        ],
                    ],
                    1=>[
                        'nodeName'=>'是钱追呗2',
                        'level'=>'2',
                        'id'=>'2_1',
                        'isSelected'=>false,
                        'nodeList'=>[
                            0=>[
                                'nodeName'=>'是钱追呗3',
                                'level'=>'3',
                                'id'=>'2_1_0',
                                'isSelected'=>true,
                                'nodeList'=>[

                                ],
                            ],
                            1=>[
                                'nodeName'=>'是钱追呗3',
                                'level'=>'3',
                                'id'=>'2_1_1',
                                'isSelected'=>true,
                                'nodeList'=>[

                                ],
                            ]
                        ],
                    ]
                ],
            ],
        ];
        $json = json_encode($arr);
        //$this->Success($arr);
       // $this->isPost();
       // $data = $this->getParam('data',true);

        //echo $data;
        $data= [];
        $arr = json_decode($json,true);
        //echo '<pre>';print_r($arr);
        foreach ($arr as $item){
            if (empty($item['nodeList']));

            
        }
        $this->arrayToTweArr($arr);
        exit();


        $Obj = new AModel();
        $Obj->content = $data;
        $Obj->create_time = time();

        if ($Obj->insert()) {
            $this->Success();
        }

        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }

    /**
     * 编辑
     */
    public function actionEdit(){
        $this->isPost();
        $id      = $this->getParam('id',true);
        $content = $this->getParam('data',true);

        $Obj = AModel::findOne($id);

        if (!$Obj){
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);

        }

        $Obj->content = $content;
        $Obj->update_time = time();
        if ($Obj->save(false)) {
            $this->Success();
        }

        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }


    /**
     * 删除
     */
    public function actionDel(){
        $this->isPost();
        $id          = $this->getParam('id',true);
        $Obj = AModel::findOne($id);

        if (!$Obj){
            $this->Error(Constants::DATA_NOT_FOUND,Constants::$error_message[Constants::DATA_NOT_FOUND]);
        }

        $Obj->status =-1;
        if ($Obj->save(false)) {
            $this->Success();
        }

        $this->Error(Constants::RET_ERROR,Constants::$error_message[Constants::RET_ERROR]);
    }




    /**
     * 多维数组转成二位数组
     */
    public function arrayToTweArr($data){

        echo '<pre>';print_r($data);

    }
}
