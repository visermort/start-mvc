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

    /**
     * @var null
     */
    private static $user = null;

    /**
     * @var bool
     */
    private static $isConsole = false;

    /*
     * vars for run action
     */
    private $controllerName;

    private $actionName;

    private $actionSlug;

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
    public static function init($web = true)
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        self::$isConsole = !$web;
        self::$instance->makeRequest();
        self::$instance->makeConfigs();
        self::$instance->makeComponents();

        self::getComponent('auth');


        if (!self::$isConsole) {
            self::$instance->run();
        }
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
                $this->error405();
                break;
            case \FastRoute\Dispatcher::FOUND:
                $this->actionParams = $routeInfo[2];
                $handler = explode('.', $routeInfo[1]);
                $this->controllerName = 'app\controllers\\' . ucfirst($handler[0]) . 'Controller';
                $controllerFile = ucfirst($handler[0]) . 'Controller.php';
                $this->actionName = 'action' . ucfirst($handler[1]);
                $this->actionSlug = $handler[0] . '.' . $handler[1];
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
                    if ($handler[2] == 'auth') {
                        //need login and not logged
                        if (App::isGuest()) {
                            $this->redirect(App::getConfig('app.login_url'));
                        }
                    } else {
                        //check permission for $handler[2]
                        $auth = self::getComponent('auth');
                        $user = self::getUser();
                        $checkUser = $user ? $auth->hasAccessTo($user->email, $handler[2]) : false;
                        if (!$checkUser) {
                            //user does not exists or user does not have a permission
                            $this->error405();
                        }
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
     * return class and method or list of classes-methods if class/method do not exists or is wrong
     * @param $action  class/method
     * @return array
     */
    public function consoleActionsList($action)
    {
        $directory = self::getRequest('root_path');
        $help = self::getComponent('help');

        if ($action) {
            $actionPath = explode('/', $action);
            if (isset($actionPath[1])) {
                $className = 'app\console\controllers\\'.ucfirst($actionPath[0]).'Controller';
                $actionName = 'action'.$help->commandToAction($actionPath[1]);
                if (method_exists($className, $actionName) && is_subclass_of($className, 'app\console\Controller')) {
                    //slass and method exist, and class extends app\console\Controller
                    return [
                        'class' => $className,
                        'action' => $actionName,
                        'list' => [],
                    ];
                }
            }
        }
        $controllerFiles = array_diff(scandir($directory . '/app/console/controllers'), ['.', '..']);
        $controllers = [];
        foreach ($controllerFiles as $file) {
            if (is_dir($directory . '/app/console/controllers/' . $file)) {
                continue;
            }
            $file = substr($file, 0, -4);
            $className = 'app\console\controllers\\'.ucfirst($file);
            if (!class_exists($className) || !is_subclass_of($className, 'app\console\Controller')) {
                continue;
            }
            $reflectionClass = new \ReflectionClass($className);
            $controllerIndex = strtolower(substr($file, 0, -10));
            $controllers[$controllerIndex] = [
                'comment' => trim(preg_replace(
                    "/[\/\*\r\n]/",
                    '',
                    $reflectionClass->getDocComment()
                )),
                'methods' => [],
            ];

            $methods = get_class_methods($className);
            foreach ($methods as $method) {
                if (substr($method, 0, 6) == 'action') {
                    $methodIndex = $help->commandToAction(substr($method, 6), true);
                    $reflection = new \ReflectionMethod($className, $method);
                    //$methodIndex = $help->commandToAction($methodName, true);
                    $controllers[$controllerIndex]['methods'][$methodIndex]['method'] = $method;

                    $controllers[$controllerIndex]['methods'][$methodIndex]['comment'] = trim(preg_replace(
                        "/[\/\*\r\n]/",
                        '',
                        $reflection->getDocComment()
                    ));
                }
            }
        }
        return [
            'class' => false,
            'action' => false,
            'list' => $controllers,
        ];
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
            self::$components[$name] = $className::init();
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
     * @return bool
     */
    public static function isConsole()
    {
        return self::$isConsole;
    }

    public static function getController()
    {
        return self::$instance->controller;
    }


    /**
     * run action
     */
    private function startAction()
    {
        //$actionName = strtolower(substr($this->actionName, 6));
        $this->controller = new $this->controllerName($this->actionSlug, $this->actionParams);
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
//        self::$request['root_path'] = substr(
//            self::$request['server']['DOCUMENT_ROOT'],
//            0,
//            strlen(self::$request['server']['DOCUMENT_ROOT']) - 4
//        );
        self::$request['root_path'] = realpath(__DIR__ . '/../');
        self::$request['url'] = isset(self::$request['server']['REQUEST_URI']) ?
            self::$request['server']['REQUEST_URI'] : '';
        $path = explode('?', self::$request['url']);
        self::$request['path'] = rawurldecode($path[0]);
        self::$request['isPost'] = !empty(self::$request['post']);
        self::$request['method'] = isset(self::$request['server']['REQUEST_METHOD']) ?
            self::$request['server']['REQUEST_METHOD'] : '';
        self::$request['isAjax'] =  !empty(self::$request['server']['HTTP_X_REQUESTED_WITH'])
            && strtolower(self::$request['server']['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
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
                if (class_exists('app\components\\' . $fileKey) &&
                    is_subclass_of('app\components\\' . $fileKey, 'app\Component')) {
                    self::$components[$fileKey] = null;
                }
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
     * if access denyed
     */
    private function error405()
    {
        $this->controllerName = 'app\Controller';
        $this->actionName = 'actionNotaccess';
        $this->controller = new Controller('notaccess');
        $this->controller->actionNotaccess();
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