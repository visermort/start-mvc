<?php

namespace app\controllers;

use app\Controller;
use app\App;
use app\models\Task;


/**
 * Class SiteController
 * @package app\controlers
 */
class TaskController extends Controller
{

    protected $actionRules = [
        'index' => [
            'breadcrumbs'=>false,
            'h1' => 'Tasks',
        ],
        'create' => [
            'breadcrumbs'=>[
                'title'=>'Create',
                'url' => '/task/create'
            ],
            'h1' => 'Task <small>create</small>',
        ],
        'update' => [
            'breadcrumbs'=>[
                'title'=>'Update',
                'url' => '/task/update'
            ],
            'h1' => 'Task <small>update</small>',
            'access' => 'login',
        ],
        'result' => [
            'breadcrumbs'=>[
                'title'=>'Result',
                'url' => '/task/result'
            ],
            'h1' => 'Task <small>create result</small>',
        ],

    ];

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
     * create task or render form
     * @param $params
     * @return string
     */
    public function actionCreate()
    {
        if (App::getRequest('method') == 'POST') {
            //if post
            // validate and clean post data
            $postData = App::getRequest('post');
            $validator = App::getComponent('validator');
            if (isset($postData['email'])) {
                $postData['email'] = strtolower($postData['email']);
            }
            $postData = $validator->clean($postData);
            $validateResult = $validator->validate($postData, Task::$rules);
            if ($validateResult === true) {
                //write data
                $task = Task::createNew($postData);

                //redirect with flash data
                $this->redirect('/task/result', 302, [
                    'success' => $task != null,
                    'text' => $task != null ? 'Task was created successfully. obcaecati impedit odit illo dolorum ab tempora nihil dicta earum fugia' :
                        'There was error creating task. obcaecati impedit odit illo dolorum ab tempora nihil dicta earum fugia'
                ]);
            } else {
                //validate fails
                return $this->render('task/create', ['old' => $postData, 'errors' => $validateResult]);
            }
        }
        //start
        return $this->render('task/create');
    }

    /**
     * update task
     * @return stringp
     */
    public function actionUpdate()
    {
        $id = $this->actionParams['id'];
        if (App::getRequest('method') == 'POST') {
            //if post
            // validate and clean post data
            $postData = App::getRequest('post');
            $validator = App::getComponent('validator');
            $postData = $validator->clean($postData);
            $postData['status'] = $postData['status'] ? 1 : 0;
            $validateResult = $validator->validate($postData, Task::$rulesUpdate);
            if ($validateResult === true) {
                //write data
                $task = Task::find($id);
                $task->text = $postData['text'];
                $task->status = $postData['status'];
                $result = $task->save();
                    //redirect with flash data
                $this->redirect('/task/result', 302, [
                    'success' => $result,
                    'text' => $result ? 'Task was updated. obcaecati impedit odit illo dolorum ab tempora nihil dicta earum fugia' :
                        'There was an error updating task. obcaecati impedit odit illo dolorum ab tempora nihil dicta earum fugia'
                ]);
            } else {
                //validate fails
                $task = Task::find($id);
                return $this->render('task/update', ['task'=> $task, 'old' => $postData, 'errors' => $validateResult]);
            }
        }


        $task = Task::find($id);
        if (!$task) {
            return $this->actionNotfound();
        }
        return  $this->render('task/update', ['task' => $task]);
    }

    /**
     * get flash data and render result form
     * @return string
     */
    public function actionResult()
    {
        $session = App::getComponent('session');
        $success = $session->get('success');
        if ($success !== null) {
            $title = $success ? 'Successfull' : 'Error';
            $className = $success ? 'success' : 'error';
            $text = $session->get('text');
            return $this->render('results/result', ['className' => $className, 'title' => $title, 'text' => $text]);
        }
        $this->redirect('/');
    }

}
