<?php

namespace app\controllers;

use app\commond\Constants;
use app\commond\helps;
use app\models\ALog;
use app\models\AProject;
use app\models\AUser;
use Yii;
use yii\db\Query;


class HController extends BasicController
{
        
    
    public function init(){
       parent::init();
    }
    

    /**
     *
     * http://www.api.com/h/operation-guide
     * @return array
     * 操作指南
     */
    public function actionOperationGuide()
    {
        $this->layout=false;
        return $this->render('operation-guide');

    }

    /**
     * http://www.api.com/h/performance-interpretation
     * 绩效解读
     *
     */
    public function actionPerformanceInterpretation(){

        $this->layout=false;
        return $this->render('performance-interpretation');
    }


    /**
     * 软件效能
     * @return array
     */
    public function actionSoftwareEfficiency()
    {
        $this->layout=false;
        return $this->render('software-efficiency');
    }
}
