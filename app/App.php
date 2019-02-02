<?php

namespace app;


/**
 * Class App
 * @package app
 */
class App
{
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
    public function __construct()
    {
        $this->makeRequest();
        $this->makeConfigs();
        $this->makeComponents();
    }

    /**
     * parse url path and run controller/action($params)
     * run application
     */
    public function run()
    {
        $pathArray = explode('/', self::getRequest('path'));
        if (empty($pathArray[1])) {
            //start page
            $this->controllerName = 'app\controllers\SiteController';
            $this->actionName = 'actionIndex';
        } elseif (!empty($pathArray[1]) && $pathArray[1] != 'index' &&
            method_exists('app\controllers\SiteController', 'action' . $pathArray[1])) {
            //action of SiteController
            $this->controllerName = 'app\controllers\SiteController';
            $this->actionName = 'action' . $pathArray[1];
            $this->actionParams = array_values(array_slice($pathArray, 2));
        } else {
            //find action of other controller
            $this->controllerName = !empty($pathArray[1]) ? 'app\controllers\\' . ucfirst($pathArray[1]) . 'Controller' : '';
            $controllerFile = ucfirst($pathArray[1]) . 'Controller.php';

            if (!file_exists(self::$request['root_path'] . '/app/controllers/' . $controllerFile) ||
                (isset($pathArray[2]) && $pathArray[2] == 'index')) {
                //there is not a controller file or action name == index
                if (App::getConfig('app.debug')) {
                    echo 'There is not a controller file "'.$controllerFile.'" or action name == index';
                }
                $this->errorNotFound();
            }
            $this->actionName = !empty($pathArray[2]) ? 'action' . ucfirst($pathArray[2]) : false;
            if ($this->actionName && method_exists($this->controllerName, $this->actionName)) {
                // route controler/action
                $this->actionParams = array_values(array_slice($pathArray, 3));
            } else if ($this->actionName) {
                //there is not such action
                if (App::getConfig('app.debug')) {
                    echo 'There is not a class "'.$this->controllerName.'" or an action "'.$this->actionName;
                }
                $this->errorNotFound();
            } else {
                // route /countroller
                $this->actionName = 'actionIndex';
                $this->actionParams = [];
            }
        }
        $this->startAction();
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
            $this->controller->beforeAction();
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
        self::$request['path'] = $path[0];
        self::$request['isPost'] = !empty(self::$request['post']);
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

}