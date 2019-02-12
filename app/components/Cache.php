<?php

namespace app\components;

use app\App;
use app\Component;
/**
 * Class Help
 * @package app\components
 */
class Cache extends Component
{
    public $engine;
    public $duration;

    protected $clear;


    /**
     * Db init
     */
    public static function init()
    {
        $instance = parent::init();
        $create = App::getConfig('cache.create');
        $instance->engine = call_user_func($create);
        $duration = App::getConfig('cache.duration');
        $instance->duration = $duration ? $duration : 3600;
        $instance->clear = App::getConfig('cache.clear');
        return $instance;
    }

    /**
     * @param $key
     * @param $callback
     * @return mixed
     */
    public function getOrSet($key, $callback)
    {
        $value = $this->engine->get($key);
        if (!$value) {
            $value = $callback();
            $this->engine->set($key, $value, $this->duration);
        }
        return $value;
    }

    /**
     * clear cache
     */
    public function clear()
    {
        if ($this->clear) {
            call_user_func($this->clear);
        }
    }

}