<?php

namespace app\controllers;

use Yii;


class IndexController extends BasicController
{
        
    
    public function init(){
       parent::init();
    }
    
    public function actionIndex(){
            echo 'aaaa';exit();
    }
}
