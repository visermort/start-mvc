<?php

namespace app;

use Cartalyst\Sentinel\Native\Facades\Sentinel;

/** base controller
 * Class Controller
 * @package app
 */
class Controller
{
    public $actionParams;

    protected $actionName;

    protected $h1 = 'H1 for page';

    protected $actionRules;

    protected $actionRulesNotFount = [
        'notfound' => [
            'breadcrumbs' => [
                'title' => '404',
                'url' => '',
            ],
            'h1' => '404 <small>Page Not Found</small>',
            'title' => 'Site title. 404 Not Found',
        ]

    ];

    protected $layout = 'layouts/main';


    protected $breadcrumbs = [];

    /**
     * Controller constructor.
     * @param $action
     */
    public function __construct($action = 'index', $actionParams = [])
    {
        $this->actionName = $action;
        $this->actionParams = $actionParams;
        try {
            $db = App::getComponent('db');
            $user = Sentinel::check();
            if ($user) {
                App::setUser($user);
            }
        } catch (\Exception $e) {
            if (App::getConfig('app.debug')) {
                echo $e->getMessage();
            }
        }
    }

    /**
     *
     */
    public function beforeAction()
    {
        //make breadcrumbs
        if (!empty($this->actionRules[$this->actionName]['breadcrumbs'])) {
            $this->breadcrumbs[] = [
                'title' => 'Home',
                'url' => '/',
            ];
            if ($this->actionName != 'index') {
                if (!empty($this->actionRules['index']['breadcrumbs'])) {
                    $this->breadcrumbs[] = [
                        'title' => $this->actionRules['index']['breadcrumbs']['title'],
                        'url' => !empty($this->actionRules['index']['breadcrumbs']['url']) ?
                            $this->actionRules['index']['breadcrumbs']['url'] : false,
                    ];
                }
            }
            $this->breadcrumbs[] = [
                'title' => $this->actionRules[$this->actionName]['breadcrumbs']['title'],
                'url' => !empty($this->actionRules[$this->actionName]['breadcrumbs']['url']) ?
                    $this->actionRules[$this->actionName]['breadcrumbs']['url'] : false,
            ];

        }
        //set meta and h1 if these are set in actionRules
        if (isset($this->actionRules[$this->actionName]['h1'])) {
            $this->h1 = $this->actionRules[$this->actionName]['h1'];
        }
        if (isset($this->actionRules[$this->actionName]['title'])) {
            App::setMeta('title', $this->actionRules[$this->actionName]['title']);
        }
        if (isset($this->actionRules[$this->actionName]['description'])) {
            App::setMeta('description', $this->actionRules[$this->actionName]['description']);
        }
        if (isset($this->actionRules[$this->actionName]['keywords'])) {
            App::setMeta('keywords', $this->actionRules[$this->actionName]['keywords']);
        }
        //params: count must be less or same what these are set in rules
        //params: count must be the same what these are set in rules - for a while
        $paramsEnableds = isset($this->actionRules[$this->actionName]['params']) ?
            ($this->actionRules[$this->actionName]['params']) : [];
        if (count($this->actionParams) != count($paramsEnableds)) {
            $this->actionNotfound();
        }
        if (!empty($this->actionParams)) {
            foreach ($this->actionParams as $key => $param) {
                if (isset($paramsEnableds[$key]['rule'])) {
                    //if rule is set - validate
                    $validator = App::getComponent('validator');
                    if ($validator->validate(['name' => $param], [[$paramsEnableds[$key]['rule'], ['name']]]) !== true) {
                        $this->actionNotfound();
                    }
                }
            }
        }
        //check access to action
        if (isset($this->actionRules[$this->actionName]['access'])) {
            $access = $this->actionRules[$this->actionName]['access'];
            switch ($access) {
                case ('login'):
                    if (App::isGuest()) {
                        $this->redirect(App::getConfig('app.login_url'));
                    }
                    break;
            }
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
        $params['breadcrumbs'] = $this->breadcrumbs;
        $params['layout'] = $this->layout;
        $params['h1'] = $this->h1;

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
        $this->actionName = 'notfound';
        $this->actionParams = [];
        $this->actionRules = $this->actionRulesNotFount;
        $this->breadcrumbs=[];
        $this->beforeAction();
        header("HTTP/1.x 404 Not Found");
        header("Status: 404 Not Found");

        echo $this->render('404');
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