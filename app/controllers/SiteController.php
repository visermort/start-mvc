<?php

namespace app\controllers;

use app\Controller;
use app\App;
use app\models\Task;
use app\models\User;
use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Cartalyst\Sentinel\Activations\EloquentActivation;

/**
 * Class SiteController
 * @package app\controlers
 */
class SiteController extends Controller
{

    protected $actionRules = [
        'notaccess' => [
            'breadcrumbs'=>[
                'title'=>'503',
                'url' => '/error503'
            ],
            'h1' => '503 <small>not access to aciton</small>',
        ],


    ];


    public function actionNotaccess()
    {
        $session = App::getComponent('session');
        $message = $session->get('notaccess');
        if ($message !== null) {
            $title = 'Error';
            $className = 'error';
            $text = 'Sorry! You do not have access to this action. Call our admin to get permission';
            return $this->render('results/result', ['className' => $className, 'title' => $title, 'text' => $text]);
        }
        $this->redirect('/');
    }

}
