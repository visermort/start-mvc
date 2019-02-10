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
    public $layoutDefailt = 'layouts/main';

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
        $template = $template . '.twig';
        return $this->twig->render($template, $params);
    }


//    /**
//     * @param $template
//     * @param $params
//     * @return string
//     */
//    public function renderPart($template, $params)
//    {
//        $basePath = App::getRequest('root_path');
//        $templateFile = $basePath . '/app/views/' . $template.'.html';
//        if (!file_exists($templateFile)) {
//            if (App::getConfig('app.debug')) {
//                echo 'Template file not found "' . $templateFile . '"';
//            }
//            return '';
//        }
//        extract($params);
//        ob_start();
//        include($templateFile);
//        $out = ob_get_clean();
//        return $out;
//    }


}