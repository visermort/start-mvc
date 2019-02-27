<?php

namespace app\controllers;

use app\components\Paginate;
use app\Controller;
use app\App;
use app\models\User;

/**
 * Class SiteController
 * @package app\controlers
 */
class AccountController extends Controller
{
    /**
     * create task or render form
     * @param $params
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('results/result', [
            'title'=> 'Under constuction',
            'text'=> 'Sorry. This page is under consruction! Please, visit it soon',
            'className' => 'success',
        ]);
    }
    /**
     * create task or render form
     * @param $params
     * @return string
     */
    public function actionLogout()
    {
        $auth = App::getComponent('auth');
        $auth->logout();
        return $this->redirect('/');
    }

    /**
     * @param $params
     * @return string
     */
    public function actionLogin()
    {
        $user = App::getUser();
        if ($user) {
            $this->redirect('/');
        }
        if (App::getRequest('method') == 'POST') {
            //if post
            // validate and clean post data
            $postData = App::getRequest('post');
            $validator = App::getComponent('validator');
            $postData = $validator->clean($postData);
            $validateResult = $validator->validate($postData, User::$loginRules);
            if ($validateResult === true) {
                //try to find user by part of email
                $user = User::where('email', 'like', trim($postData['name']).'@%')->first();
                if ($user) {
                    //try to login
                    $credentials = [
                        'email' => $user->email,
                        'password' => $postData['password'],
                    ];
                    $auth = App::getComponent('auth');
                    $authUser = $auth->authenticate($credentials);
                    if ($authUser) {
                        //$checkUser = $auth->hasAccessTo($authUser['email'], 'admin');
                        //if ($checkUser) {
                            //checked  - login
                        $login = $auth->login($authUser);
                        if ($login) {
                            $this->redirect(App::getConfig('app.account_start_page'));
                        }
                        //}
                    }
                }
                $validateResult = [
                    'password' => 'You user name or password are invalid',
                    'name' => 'You user name or password are invalid',
                ];
            }
            return $this->render('account/login', ['old' => $postData, 'errors' => $validateResult]);
        }
        //start
        return $this->render('account/login');
    }

    /**
     * TEST
     * create user with admin permission
     */
    public function actionCreateadmin()
    {
        $auth = App::getComponent('auth');
        $user = $auth->register([
            'email' => 'account@emial.email',
            'password' => '123456',
            'first_name' => 'Developer',
            'last_name' => 'Admin',
        ]);
        d($user);
        $registration = $auth->getActivation('account@emial.email');
        d($registration);
        $activation = $auth->activate('account@emial.email', $registration['code']);//bool
        d($activation);
        $activationCheck = $auth->checkActivation('account@emial.email'); //bool true if complete object if not completed
        d($activationCheck);
        $permissions =  $auth->setUserPermision('account@emial.email', 'admin');
        d($permissions);
    }


}
