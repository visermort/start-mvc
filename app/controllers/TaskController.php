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
            'breadcrumbs'=>[
                'title' => 'Tasks',
            ],
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
            'params' => [
                ['rule' => ['integer'=>[['param']]], 'required' => true],
            ],
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

    public function beforeAction()
    {
        parent::beforeAction();
        $db = App::getComponent('db');
    }

    /*
     * page is not exists
     */
    public function actionIndex()
    {
        $this->redirect('/');
    }

    /**
     * create task or render form
     * @param $params
     * @return string
     */
    public function actionCreate()
    {
        if (App::getRequest('isPost')) {
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
        $id = $this->actionParams[0];
        if (App::getRequest('isPost')) {
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
