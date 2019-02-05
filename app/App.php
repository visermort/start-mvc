<?php

namespace app;

/**
 * Class App
 * @package app
 */
class App
{
    private static $instance;
    /**
     * @var configs for app
     */
    private static $config;
    /**
     * @var request data
     */
    private static $request;
    /**
     * @var array
     */
    private static $components = [];

    private static $user = null;

    /*
     * vars for run action
     */
    private $controllerName;

    private $actionName;

    private $actionParams =[];

    /**
     * @var controller instance
     */
    protected $controller;

    /**
     * App constructor.
     */
    private function __construct()
    {
    }
    private function __clone()
    {
    }
    private function __wakeup()
    {
    }

    /**run application
     * @return App
     */
    public static function init()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        self::$instance->makeRequest();
        self::$instance->makeConfigs();
        self::$instance->makeComponents();

        self::getComponent('auth');

        self::$instance->run();
        return self::$instance;
    }


    /**
     * parse url path and run controller/action($params)
     * run application
     */
    private function run()
    {
        $dispatcher = $this->getDispatcher(self::getConfigSection('routes'));

        $routeInfo = $dispatcher->dispatch(self::getRequest('method'), self::getRequest('path'));

        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                $this->errorNotFound();
                break;
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $this->controllerName = 'app\controllers\SiteController';
                $this->actionName = 'actionIndex';
                $this->actionParams = [];
                break;
            case \FastRoute\Dispatcher::FOUND:
                $this->actionParams = $routeInfo[2];
                $handler = explode('.', $routeInfo[1]);
                $this->controllerName = 'app\controllers\\' . ucfirst($handler[0]) . 'Controller';
                $controllerFile = ucfirst($handler[0]) . 'Controller.php';
                $this->actionName = 'action' . ucfirst($handler[1]);
                if (!file_exists(self::$request['root_path'] . '/app/controllers/' . $controllerFile) ||
                    !method_exists($this->controllerName, $this->actionName)) {
                    //there is not a controller file or action name == index
                    if (App::getConfig('app.debug')) {
                        echo 'There is not a controller file "' . $controllerFile . '" or action name == index';
                    }
                    $this->errorNotFound();
                }
                if (isset($handler[2])) {
                    //part of hangler for checking permissions
                    switch ($handler[2]) {
                        case 'admin':
                            if (App::isGuest()) {
                                $this->redirect(App::getConfig('app.login_url'));
                            }
                            break;
                    }
                }
                break;
        }
        $this->startAction();
    }

    /**
     * @param $routesConfig
     * @return \FastRoute\Dispatcher
     */
    private function getDispatcher($routesConfig)
    {
        $dispatcher = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $route) use ($routesConfig) {
            foreach ($routesConfig as $routeGroupItem) {
                $route->addRoute($routeGroupItem[0], $routeGroupItem[1], $routeGroupItem[2]);
            }
        });
        return $dispatcher;
    }

    /**
     * get a config
     * @param $name
     * @return bool
     */
    public static function getConfig($name)
    {
        return isset(self::$config[$name]) ? self::$config[$name] : null;
    }

    /**
     * get a whole section of config
     * @param $sectionName
     * @return array
     */
    public static function getConfigSection($sectionName)
    {
        $out = [];
        foreach (self::$config as $key => $config) {
            if (strpos($key, $sectionName . '.') === 0) {
                $out[$key] = $config;
            }
        }
        return $out;
    }

    /**
     * @param $name
     * @param $value
     */
    public static function setMeta($name, $value)
    {
        self::$config['meta.'.$name] = $value;
    }

    public static function getRequest($name, $param = null)
    {
        if ($param) {
            return (isset(self::$request[$name][$param]) ? self::$request[$name][$param] : null);
        }
        return (isset(self::$request[$name]) ? self::$request[$name] : null);
    }

    /**
     * get component instance
     * @param $name
     * @return mixed
     */
    public static function getComponent($name)
    {
        $name = ucfirst($name);
        $className = 'app\components\\' . $name;
        if (self::$components[$name] === null && class_exists($className)) {
            self::$components[$name] = new $className();
        }
        return self::$components[$name];
    }

    /**
     * @param $user
     */
    public static function setUser($user)
    {
        self::$user = $user;
    }

    /**
     * @return bool
     */
    public static function isGuest()
    {
        return self::$user == null;
    }


    /**
     * @return null
     */
    public static function getUser()
    {
        return self::$user;
    }


    /**
     * run action
     */
    private function startAction()
    {
        $actionName = strtolower(substr($this->actionName, 6));
        $this->controller = new $this->controllerName($actionName, $this->actionParams);
        if ($this->controller) {
            $method = $this->actionName;
            echo $this->controller->$method();
            exit;
        } else {
            if (self::getConfig('debug')) {
                echo 'Error! Controller or action not found';
            }
            exit;
        }
    }

    /**
     * request
     */
    private function makeRequest()
    {
        self::$request['server'] = $_SERVER;
        self::$request['get'] = $_GET;
        self::$request['post'] = $_POST;
        self::$request['file'] = $_FILES;
        self::$request['root_path'] = substr(
            self::$request['server']['DOCUMENT_ROOT'],
            0,
            strlen(self::$request['server']['DOCUMENT_ROOT']) - 4
        );
        self::$request['url'] = self::$request['server']['REQUEST_URI'];
        $path = explode('?', self::$request['url']);
        self::$request['path'] = rawurldecode($path[0]);
        self::$request['isPost'] = !empty(self::$request['post']);
        self::$request['method'] = self::$request['server']['REQUEST_METHOD'];
    }

    /**
     * configs
     */
    private function makeConfigs()
    {
        $configDirectory = self::$request['root_path'] . '/app/config';
        $configFiles = array_diff(scandir($configDirectory), ['.', '..']);
        foreach ($configFiles as $configFile) {
            if (is_file($configDirectory . '/' . $configFile)) {
                $fileKey = str_replace('.php', '', $configFile);
                $configs = include 'config/' . $configFile;
                foreach ($configs as $key => $config) {
                    self::$config[$fileKey . '.' . $key] = $config;
                }
            }
        }
        if (self::getConfig('app.debug')) {
            ini_set('display_errors', 1);
        }
    }

    /**
     * make links to empty components
     */
    private function makeComponents()
    {
        $componentsDirectory = self::$request['root_path'] . '/app/components';
        $componentFiles = array_diff(scandir($componentsDirectory), ['.', '..']);
        foreach ($componentFiles as $file) {
            if (is_file($componentsDirectory . '/' . $file)) {
                $fileKey = str_replace('.php', '', $file);
                self::$components[$fileKey] = null;
            }
        }
    }

    /**
     * if wrong request
     */
    private function errorNotFound()
    {
        $this->controllerName = 'app\Controller';
        $this->actionName = 'actionNotfound';
        $this->controller = new Controller();
        $this->controller->actionNotfound();
    }

    /**
     * @param $url
     */
    private function redirect($url)
    {
        $this->controllerName = 'app\Controller';
        $this->controller = new Controller();
        $this->controller->redirect($url);
    }

}