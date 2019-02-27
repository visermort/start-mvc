<?php

use app\App;

/**
 * custom functions for twig
 */
return [
    'config' => function ($name) {
        return App::getConfig($name);
    },
    'date' => function ($pattern, $date = null) {
        if ($date) {
            $date = is_integer($date) ? $date : strtotime($date);
        } else {
            $date = time();
        }
        return date($pattern, $date);
    },
    'user' => function () {
        return App::getUser();
    },
    'sort_by' => function ($name, $title) {
        $help = App::getComponent('help');
        return $help->sortBy($name, $title);
    },
    'user_has' => function ($permission) {
        $user =  App::getUser();
        $auth = App::getComponent('auth');
        return $user && $auth->hasAccessTo($user->email, $permission) ? 1 : 0;
    },
    'status' => function ($status) {
        return $status ? 'Completed' : 'In process';
    },
    'csrf' => function () {
        $session = App::getComponent('session');
        return $session->setCsrf();
    }

];