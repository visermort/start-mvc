<?php

namespace app\components;

use app\Component;
/**
 * Class Session
 * @package app\components
 */
class Session extends Component
{
    protected $flashPrefix = '_flash';

    public static function init()
    {
        if (session_id() == '') {
            session_start();
        }
        return parent::init();
    }

    /**
     * set key
     * @param $key
     * @param $value
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * get key
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        if (!isset($_SESSION[$key])) {
            return null;
        }
        $value = $_SESSION[$key];
        if (isset($_SESSION[$key . $this->flashPrefix])) {
            $this->deleteKey($key);
            $this->deleteKey($key . $this->flashPrefix);
        }
        return $value;
    }

    /**
     * set flash
     * @param $key
     * @param $value
     */
    public function flash($key, $value)
    {
        $this->set($key, $value);
        $this->set($key . $this->flashPrefix, 1);
    }
    /**
     *
     */
    public function destroy()
    {
        session_destroy();
    }

    /** set scrf key
     * @return string
     * @throws \Exception
     */
    public function setCsrf()
    {
        $csrf = bin2hex(random_bytes(32));
        $this->set('csrf', $csrf);
        $this->set('csrf_time', time());
        return $csrf;
    }

    /**
     * check csrf key
     * @param $key
     */
    public function checkCsrf($key)
    {
        $out = ($key == $this->get('csrf') && time() - $this->get('csrf_time') < 60 * 60 * 10) ? true : false;//10 min
        $this->deleteKey('csrf');
        $this->deleteKey('csrf_time');
        return $out;
    }

    /**
     * delete key
     * @param $key
     */
    protected function deleteKey($key)
    {
        unset($_SESSION[$key]);
    }

}