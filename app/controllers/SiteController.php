<?php

namespace app\controllers;

use app\Controller;
use app\App;
use app\models\Task;
use app\models\User;
use Cartalyst\Sentinel\Native\Facades\Sentinel;

/**
 * Class SiteController
 * @package app\controlers
 */
class SiteController extends Controller
{

    protected $actionRules = [
        'index' => [
            'breadcrumbs'=>false,
            'h1' => 'Tasks',
        ],
        'login' => [
            'breadcrumbs'=>[
                'title'=>'Login',
                'url' => '/login'
            ],
            'h1' => 'Login',
            'title' => 'title for index/login',
            'description' => 'desct for index/login',
            'keywords' => 'kw for index login',
        ]
    ];


    public function beforeAction()
    {
        parent::beforeAction();
        $db = App::getComponent('db');
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $this->layout = 'layouts/index';

        $sortBy = App::getRequest('get', 'sort');
        $page = App::getRequest('get', 'page');
        $direction = App::getRequest('get', 'order');

        $database = Task::select('tasks.*', 'users.first_name', 'users.last_name', 'users.email')
            ->leftJoin('users', 'users.id', '=', 'tasks.user_id');
        if ($sortBy) {
            $database = $database->orderBy($sortBy, $direction);
        }
        //app component
        $pagination = App::getComponent('paginate');
        //component init
        $pagination->init($database, ['page' => $page]);
        //component data and pagination data
        $tasks = $pagination->data();
        $pagination = $pagination->pagination();

        return $this->render('index/tasks', ['tasks' => $tasks, 'pagination' => $pagination]);
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
        if (App::getRequest('isPost')) {
            //if post
            // validate and clean post data
            $postData = App::getRequest('post');
            $validator = App::getComponent('validator');
            $postData = $validator->clean($postData);
            $validateResult = $validator->validate($postData, User::$loginRules);
            if ($validateResult === true) {
                //try to find user by part of email
                $user = User::where('email', 'like', $postData['name'].'@%')->first();
                if ($user) {
                    //try to login
                    $credentials = [
                        'email' => $user->email,
                        'password' => $postData['password'],
                    ];
                    $response = Sentinel::authenticate($credentials);
                    if ($response) {
                        $email = $response['email'];
                        //check by email now  - we can check by permissions late
                        if ($email == App::getConfig('app.admin_email')) {
                            //checked  - login
                            $login = Sentinel::loginAndRemember($response);
                            if ($login) {
                                $this->redirect(App::getConfig('app.account_start_page'));
                            }
                        }
                    }
                }
            }
            return $this->render('account/login', ['old' => $postData, 'errors' => $validateResult]);
        }
        //start
        return $this->render('account/login');
    }

}
