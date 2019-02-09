<?php

namespace app\Components;

use app\App;
use app\Component;
use Cartalyst\Sentinel\Native\Facades\Sentinel;

/**
 * Class Auth
 * @package app\Components
 */
class Auth extends Component
{
    /**
     * Db init
     */
    public static function init()
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
        return parent::init();
    }

    /**
     * activatioin key
     * @param $userId
     * @return mixed
     */
    public function getActivation($email)
    {
        $user = Sentinel::findByCredentials(['email' => $email]);
        $activationRepository = Sentinel::getActivationRepository();
        $activation = $activationRepository->create($user);
        return $activation;
    }

    /**
     * complete activation
     * @param $email
     * @param $activationCode
     * @return mixed
     */
    public function activate($email, $activationCode)
    {
        $user = Sentinel::findByCredentials(['email' => $email]);
        $activationRepository = Sentinel::getActivationRepository();
        if ($user) {
            $activation = $activationRepository->complete($user, $activationCode);
            return $activation;
        }
    }

    /**
     * check activation
     * @param $email
     * @return mixed
     */
    public function checkActivation($email)
    {
        $user = Sentinel::findByCredentials(['email' => $email]);
        $activationRepository = Sentinel::getActivationRepository();
        if ($user) {
            $activation = $activationRepository->exists($user);
            return $activation;
        }
    }

    /**
     * @param $credentials
     * @return mixed
     */
    public function register($credentials)
    {
        $user = Sentinel::authenticate(['email' => $credentials['email']]);
        if ($user) {
            return [
                'result' => 'false',
                'user' => $user,
                'message' => 'User with this email allredy exists',
            ];
        }
        $user =  Sentinel::register($credentials);
        return [
            'result' => $user != false,
            'user' => $user,
            'message' => '',
        ];
    }

    /**
     * authenticate
     * @param $credentials
     * @return mixed
     */
    public function authenticate($credentials)
    {
        return Sentinel::authenticate($credentials);
    }

    /**
     * login and remember
     * @param $user
     * @return mixed
     */
    public function login($user)
    {
        return Sentinel::loginAndRemember($user);
    }

    /**
     * @return mixed
     */
    public function logout()
    {
        return Sentinel::logout();
    }

    /**
     * set permission
     * @param $email
     * @param $role
     * @param bool $value
     * @return array
     */
    public function setUserPermision($email, $permission, $value = true)
    {
        $user = Sentinel::findByCredentials(['email' => $email]);
        $permissions = [];
        foreach ($user->permissions as $key => $val) {
            $permissions[$key] = $val;
        }
        $permissions[$permission] = $value ? true : false;
        $user->permissions = $permissions;
        if ($user->save()) {
            return $user->permissions;
        }
    }

    /** revoke all permissions
     * @param $email
     * @return null
     */
    public function revokeUserPermisions($email)
    {
        $user = Sentinel::findByCredentials(['email' => $email]);
        $user->permissions = [];
        if ($user->save()) {
            return $user->permissions;
        }
    }

    /**
     * check permission
     * @param $email
     * @param $permission
     * @return mixed
     */
    public function hasAccessTo($email, $permission)
    {
        $user = Sentinel::findByCredentials(['email' => $email]);
        return $user->hasAccess($permission);
    }

}
