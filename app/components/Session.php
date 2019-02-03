<?php

namespace app\components;

/**
 * Class Session
 * @package app\components
 */
class Session
{
    protected $flashPrefix = '_flash';

    public function __construct()
    {
        if (session_id() == '') {
            session_start();
        }
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

    /**
     * delete key
     * @param $key
     */
    protected function deleteKey($key)
    {
        unset($_SESSION[$key]);
    }

}