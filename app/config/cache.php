<?php

use app\App;

//$cache = 'file';
//$cache = 'memcached';
$cache = 'nocache';

switch ($cache) {
    case ('file'):
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
        break;

    case ('memcached'):
        return [
            'create' => function () {
                $server = new \Memcached();
                return new Desarrolla2\Cache\Adapter\Memcached($server);
            },
            'duration' => '3600',
            'clear' => function () {
                App::getComponent('cache')->engine->flush();
            }
        ];
        break;

    default:
        //nocache
        return [
            'create' => function () {
                return new Desarrolla2\Cache\Adapter\NotCache();
            },

        ];

}
