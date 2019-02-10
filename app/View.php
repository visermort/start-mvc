<?php

namespace app;

/**
 * Class View
 * @package app
 */
class View
{
    /**
     * @var string
     */
    protected $layoutDefailt = 'layouts/main';

    protected $layoutAjax = 'layouts/main_ajax';

    protected $layout;

    public $ajax = false;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * View constructor.
     */
    public function __construct()
    {
        //twig directories
        $cachePath = App::getRequest('root_path') . '/app/runtime/twig/cache';
        $templatePath = App::getRequest('root_path') . '/app/views/';
        if (!file_exists($cachePath)) {
            mkdir($cachePath, 0777, true);
        }

        if (App::getConfig('app.debug') && App::getConfig('app.clear_twig_cache_on_debug')) {
            //clear cache if set in config
            $help = App::getComponent('fileutils');
            $help->clearDirectory($cachePath);
        }

        //init twit
        $loader = new \Twig_Loader_Filesystem($templatePath);
        $this->twig = new \Twig_Environment($loader, [
            'cache' => $cachePath,
        ]);
        //custom functions
        $functions = include(App::getRequest('root_path') . '/app/components/twig/functions.php');
        foreach ($functions as $functionName => $functionCode) {
            $function = new \Twig_Function($functionName, $functionCode);
            $this->twig->addFunction($function);
        }
    }

    /**
     * render site
     * @param $template
     * @param $params
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function renderPage($template, $params)
    {
        $this->beforeRender();
        $template = $template . '.twig';
        $params['layout'] = $this->layout;
        return $this->twig->render($template, $params);
    }

    public function beforeRender()
    {
        $this->layout = $this->ajax ? $this->layoutAjax : $this->layoutDefailt;
    }

}