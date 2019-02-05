<?php

namespace app\controllers;

use app\Controller;
use app\App;
use Cartalyst\Sentinel\Native\Facades\Sentinel;

/**
 * Class SiteController
 * @package app\controlers
 */
class AccountController extends Controller
{

    protected $actionRules = [
        'index' => [
            'breadcrumbs'=>[
                'title' => 'Account',
                'url' => '/account/logout'
            ],
            'h1' => 'Account <small>home page</small>',
        ],
        'logout' => [
            'breadcrumbs'=>[
                'title'=>'Logout',
                'url' => '/account/logout'
            ],
            'h1' => 'Account <small>logout</small>',
        ],
    ];

    public function beforeAction()
    {
        parent::beforeAction();
        App::getComponent('db');
    }

    /**
     * create task or render form
     * @param $params
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('results/result', [
            'title'=> 'Under constuction',
            'text'=> 'Sorry. This page is under consruction! Please, visit it soon :)',
            'className' => 'error',
        ]);
    }
    /**
     * create task or render form
     * @param $params
     * @return string
     */
    public function actionLogout()
    {
        Sentinel::logout();
        return $this->redirect('/');
    }


}
