<?php

use app\App;

return [

    'create' => function () {
        $path = App::getRequest('root_path') . '/app/runtime/disarolla/cache';
        return new Desarrolla2\Cache\Adapter\File($path);
    },
    'duration' => '3600',
    'clear' => function () {
        $path = App::getRequest('root_path') . '/app/runtime/disarolla/cache';
        App::getComponent('fileutils')->clearDirectory($path);
    }
];