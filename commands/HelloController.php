<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;
use app\commond\helps;
use app\models\AProject;
use Yii;
use app\models\ASendEmail;
use yii\console\Controller;
use yii\console\ExitCode;
use app\commond\PHPMailer\PHPMailer;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HelloController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex()
    {
       // echo $message . "\n";
        error_log('contab----'.PHP_EOL,3,'/tmp/crontab.log');
    }


    public function actionFixBugHasFile(){


    }

}
