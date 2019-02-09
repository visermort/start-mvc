<?php

namespace app;

/**
 * Class App
 * @package app
 */
class Component
{
    private static $instances = [];
     /**
     * App constructor.
     */
    private function __construct()
    {
    }
    private function __clone()
    {
    }
    private function __wakeup()
    {
    }

    /**run application
     * @return App
     */
    public static function init()
    {
        if (!isset(self::$instances[static::class])) {
            self::$instances[static::class] = new static();
        }

        return self::$instances[static::class];
    }


}