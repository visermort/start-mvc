<?php

namespace app\Components;

use app\App;
use Cartalyst\Sentinel\Native\Facades\Sentinel;

/**
 * Class Auth
 * @package app\Components
 */
class Auth
{

    public function __construct()
    {
        try {
            App::getComponent('db');
            $user = Sentinel::check();
            if ($user) {
                App::setUser($user);
            }
        } catch (\Exception $e) {
            if (App::getConfig('app.debug')) {
                echo $e->getMessage();
            }
        }
    }

}
