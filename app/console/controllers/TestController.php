<?php

namespace app\console\controllers;

use app\App;
use app\console\Controller;

/**
 *  test controller desctiption
 */
class TestController extends Controller
{
    /**
     * test action description
     */
    public function actionTest($arg1 = null, $arg2 = null)
    {
        echo 'test test arg1 '.$arg1. ' arg2'.$arg2."\n";
        echo App::isConsole();
    }
    /**
     * test-and-test action description
     */
    public function actionTestAndTest()
    {
        echo 'test test2';
    }
}
