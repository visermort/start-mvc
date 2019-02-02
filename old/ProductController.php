<?php

namespace app\controllers;

use app\Controller;
use app\models\Banner;
use app\App;
use app\Model;
use Illuminate\Database\Capsule\Manager as Capsule;
use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Cartalyst\Sentinel\Activations\EloquentActivation as Activation;


/**
 * Class SiteController
 * @package app\controlers
 */
class ProductController extends Controller
{

    protected $actionRules = [
        'index' => [
            'breadcrumbs'=>[
                'title' => 'Products',
                'url' => '/product'
            ],
        ],
        'test' => [
            'breadcrumbs'=>[
                'title'=>'Test',
                'url' => '/product/test'
            ],
            'permission' => 'user',
        ],
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
        //Model
        //$model = new Model();
        //$model->tableName= 'banners';
        //$banners = $model->select();

        //capsule
        //$db = App::getComponent('db');
//        $banners = Capsule::table('banners')->get();
//        $database = App::getComponent('db');
//        $banners = Banner::all();

        //eloquent
        //$banners = Banner::all();


        return $this->render('product', ['banners'=> $banners]);
    }

    /**
     * @param $params
     * @return string
     */
    public function actionTest()
    {
/*        Sentinel::register([
            'email' => 'admin@admin.ua',
            'password' => '123'
        ]);*/
//        $user = Sentinel::findById(5);
//        d($user, $user->toArray());
//        $activation = Activation::create($user->toArray());
//        d($activation);exit;
//
//        if (Activation::complete($user, 'activation_code_here'))
//        {
//            d('comtlete');exit;
//        }
//        else
//        {
//           d('errer');exit; // Activation not found or not completed.
//        }
        return $this->render('index', ['foo'=>'bar']);
    }

}
