<?php

spl_autoload_register(function ($className) {
    if (strpos($className, 'app\\') === 0) {
        $pathArray = explode('\\', $className);
        $newPath = __DIR__ . '/' . implode('/', $pathArray) . '.php';
        include $newPath;
    }
});