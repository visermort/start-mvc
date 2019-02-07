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
     *
     * @param $template
     * @param $params
     * @return string
     */
    public function renderPage($template, $params)
    {
        $basePath = App::getRequest('root_path');
        //$metaTags = App::getConfigSection('meta');
        $this->layout = isset($params['layout']) ? $params['layout'] : $this->layoutDefailt;
        $breadcrumbs = !empty($params['breadcrumbs']) ?
            $this->renderPart('parts/breadcrumbs', ['breadcrumbs'=>$params['breadcrumbs']]) : '';
        //$h1 = isset($params['h1']) ? $params['h1'] : 'Sample H1 for page';


        $layoutFile = $basePath . '/app/views/' . $this->layout.'.html';

        if (!file_exists($layoutFile)) {
            if (App::getConfig('app.debug')) {
                echo 'Layout file not found "' . $layoutFile . '"';
            }
            exit;
        }
        //layout data
        $layout = file_get_contents($layoutFile);
        //add includes
        $layout = preg_replace_callback('/\{\{\s*include.*\s*\}\}/', function ($matches) use ($basePath) {
            $pos = strpos($matches[0], 'include') + 7;
            $end = strpos($matches[0], '}}');

            $file = $basePath . '/app/views/'. trim(substr($matches[0], $pos, $end-$pos)) . '.html';
            if (!file_exists($file)) {
                if (App::getConfig('app.debug')) {
                    echo 'Included file not found "' . $file . '"';
                }
                exit;
            }
            return file_get_contents($file);
        }, $layout);

        //content data
        $content = $this->renderPart($template, $params);
        //replace placeholders
        $layout = $this->replace($layout, [
            'content' => $content,
            'breadcrumbs' => $breadcrumbs,
        ]);
        $layout = $this->replace($layout, $params['meta']);

        //put site configs
        $siteConfigs = App::getConfigSection('site');
        $layout = $this->replace($layout, $siteConfigs);

        //run Helpers
        $layout = preg_replace_callback('/\{\{\s*Help\:\:\w*\(.*\)*\s*\}\}/', function ($matches) {
            return $this->runHelper($matches[0]);
        }, $layout);


        if (!App::getConfig('app.debug')) {
            //if not debug remove empty placeholders
            $layout = preg_replace('/\{\{.*\}\}/', '', $layout);
        }

        return $layout;
    }

    /**
     * @param $template
     * @param $params
     * @return string
     */
    public function renderPart($template, $params)
    {
        $basePath = App::getRequest('root_path');
        $templateFile = $basePath . '/app/views/' . $template.'.html';
        if (!file_exists($templateFile)) {
            if (App::getConfig('app.debug')) {
                echo 'Template file not found "' . $templateFile . '"';
            }
            return '';
        }
        extract($params);
        ob_start();
        include($templateFile);
        $out = ob_get_clean();
        return $out;
    }

    /**
     * run functions of Help conponents in template like {{ Help::functionName($params)}}
     * @param $match
     * @return mixed
     */
    private function runHelper($match)
    {
        $methodName = substr($match, strpos($match, '::') + 2);
        $methodName = substr($methodName, 0, strpos($methodName, '('));
        $arguments = substr($match, strpos($match, '(') + 1);
        $arguments = substr($arguments, 0, strpos($arguments, ')'));

        $helper = App::getComponent('help');
        if (method_exists($helper, $methodName)) {
            return  $helper->$methodName($arguments);
        }
    }

    /**
     * replace placeholders in page content
     * @param $content
     * @param array $replaces
     * @return null|string|string[]
     */
    private function replace($content, $replaces = [])
    {
        if (!empty($replaces)) {
            foreach ($replaces as $key => $value) {
                $content = preg_replace('/\{\{\s*' . $key . '\s*\}\}/', $value, $content);
            }
        }
        return $content;
    }

}