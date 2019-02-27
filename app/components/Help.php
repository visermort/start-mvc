<?php

namespace app\components;

use app\App;
use app\Component;
/**
 * Class Help
 * @package app\components
 */
class Help extends Component
{
    /**
     * @param $name
     * @param $title
     * @return string
     */
    public function sortBy($name, $title)
    {
        $path = App::getRequest('path');
        $sort = App::getRequest('get', 'sort');
        $order = App::getRequest('get', 'order');
        $params = App::getRequest('get');

        $newOrder = $name != $sort ? 'asc' :  ($order == 'asc' ? 'desc' : 'asc');
        $params['sort'] = $name;
        $params['order'] = $newOrder;
        $paramsQuery = http_build_query($params);
        $href = $path . ($paramsQuery ? '?' . $paramsQuery : '');

        $icon = $name == $sort ? '<i class="fa fa-sort-'.($order == 'desc' ? 'desc' : 'asc' ).
            '" aria-hidden="true"></i>  ' : '' ;

        return '<a class="ajax-button" href="'.$href.'">'.$icon.'&nbsp;'.$title.'</a>';
    }

    /**
     * @param $array
     * @param $from
     * @param $to
     * @return array
     */
    public function arrayMap($array, $from, $to)
    {
        $out = [];
        foreach ($array as $item) {
            $out[$item[$from]] = $item[$to];
        }
        return $out;
    }

    /**
     * @param $glue
     * @param $array
     * @return string
     */
    public function multiImplode($glue, $array)
    {
        $out = '';
        if (is_array($array)) {
            foreach ($array as $key => $item) {
                $out .= ($glue. $key . $glue . $this->multiImplode($glue, $item));
            }
        } else {
            $out .= (str_replace(' ', $glue, $array));

        }
        return $out;
    }

    /**
     * partone-parttwo-partthree to PartoneParttwoPartthree or back if $reverse
     * @param string $command
     * @return string
     */
    public function commandToAction($command, $reverse = false)
    {
        if ($reverse) {
            $commandArray = array_diff(preg_split('/(?=[A-Z])/', $command), ['']);
            foreach ($commandArray as &$item) {
                $item = strtolower($item);
            }
            return implode('-', $commandArray);
        }
        $commandArray = explode('-', $command);
        foreach ($commandArray as &$item) {
            $item = ucfirst($item);
        }
        return implode('', $commandArray);
    }
}