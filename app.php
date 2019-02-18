<?php

require 'vendor/autoload.php';
require 'autoload.php';

$app = app\App::init(false);

$actionList = $app->consoleActionsList(isset($argv[1]) ? $argv[1] : null);

if ($actionList['class'] && $actionList['action']) {
    $class = new $actionList['class']();
    $method = $actionList['action'];
    //params
    $params = array_slice($argv, 2);
    //run action
    $class->$method(...$params);
} else {
    //list of enabled commands
    if (!empty($actionList['list'])) {
        echo "\n";
        foreach ($actionList['list'] as $controllerIndex => $controller) {
            echo $controllerIndex . "\n";
            echo $controller['comment'] . "\n";
            foreach ($controller['methods'] as $method => $methodInfo) {
                echo $controllerIndex . '/' . $method . '   ' . $methodInfo['comment'] . "\n";
            }
            echo "\n";
        }
    }

}
