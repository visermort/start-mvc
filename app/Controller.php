<?php

namespace app;


/** base controller
 * Class Controller
 * @package app
 */
class Controller
{
    public $actionParams;

    protected $actionSlug;

    protected $meta;

    protected $layout = 'layouts/main';

    protected $breadcrumbs = [];

    protected $ajaxResponse = false;

    /**
     * Controller constructor.
     * @param $action
     */
    public function __construct($actionSlug = 'site.index', $actionParams = [])
    {
        $this->actionSlug = $actionSlug;
        $this->actionParams = $actionParams;
        $this->beforeAction();
    }

    /**
     *
     */
    public function beforeAction()
    {
        //metadata
        $this->meta = App::getConfig('meta.' . $this->actionSlug) ?
            App::getConfig('meta.' . $this->actionSlug) : App::getConfig('site.index');
        //breadcrumbs
        $this->breadcrumbs[] = ['title' => 'Home', 'url' => '/'];
        $slugs = explode('.', $this->actionSlug);
        $help = App::getComponent('help');
        $routes = $help->arrayMap(App::getConfigSection('routes'), 2, 1);
        $routesArr = [];
        foreach ($routes as $key => $route) {
            $keyArr = explode('.', $key);
            $index = $keyArr[0].'.'.$keyArr[1];
            $routesArr[$index] = preg_replace('/\\/*{.*\}/', '', $route);
        }
        if (isset(App::getConfig('meta.'.$slugs[0] . '.index')['breadcrumbs'])) {
            $this->breadcrumbs[] = [
                'title' => App::getConfig('meta.'.$slugs[0] . '.index')['breadcrumbs'],
                'url' => isset($routesArr[$slugs[0] . '.index']) ? $routesArr[$slugs[0] . '.index'] : false,
            ];
        }
        if ($slugs[0] . '.index' != $this->actionSlug && isset(App::getConfig('meta.'.$this->actionSlug)['breadcrumbs'])) {
            $this->breadcrumbs[] = [
                'title' => App::getConfig('meta.'.$this->actionSlug)['breadcrumbs'],
                'url' => isset($routesArr[$this->actionSlug]) ? $routesArr[$this->actionSlug] : false,
            ];
        }
    }
    /**
     * @param $tempalte
     * @param array $params
     * @return string
     */
    public function render($tempalte, $params = [])
    {
        $view = new View();
        $view->ajax = $this->ajaxResponse;
        $params['meta'] = $this->meta;
        $params['layout'] = $this->layout;
        $params['breadcrumbs'] = $this->breadcrumbs;

        return $view->renderPage($tempalte, $params);
    }

    /**
     * @param $params
     * @return string
     */
    public function renderJson($params)
    {
        header("Content-type:application/json");
        return json_encode($params);
    }

    /**
     * exit - can be run both from App and fron controller
     * @return string
     */
    public function actionNotfound()
    {
        $this->actionSlug = 'notfound';
        $this->actionParams = [];
        $this->breadcrumbs=[];
        $this->beforeAction();
        $this->breadcrumbs[] = ['title' => 404];
        header("HTTP/1.x 404 Not Found");
        header("Status: 404 Not Found");
        echo $this->render('404');
        exit;
    }

    public function actionNotaccess()
    {
        $this->actionSlug = 'notaccess';
        $this->actionParams = [];
        $this->breadcrumbs=[];
        $this->beforeAction();
        $this->breadcrumbs[] = ['title' => 405];
        header("HTTP/1.0 405 Method Not Allowed");
        header("Status: 405 Method Not Allowed");
        echo $this->render('405');
        exit;
    }

    /**
     * @param $url
     * @param int $statusCode
     * @param array $flashData
     */
    public function redirect($url, $statusCode = 302, $flashData = [])
    {
        if (!empty($flashData)) {
            $session = App::getComponent('session');
            if ($session) {
                foreach ($flashData as $key => $value) {
                    $session->flash($key, $value);
                }
            }
        }
        header('Location: ' . $url, true, $statusCode);
        exit;
    }


}