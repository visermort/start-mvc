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

    public function pagination($pagination)
    {
        if ((isset($pagination['single']) && $pagination['single']) || $pagination['pages'] == 1) {
            return '';
        }
        $path = App::getRequest('path');
        $params = App::getRequest('get');
        $buttonCount = App::getConfig('grids.paginage_buttons');
        $start = $pagination['page'] - round(floor($buttonCount/2));
        $buttonStart = $start < 1 ? 1 : $start;
        $buttonStart = $pagination['pages'] > $buttonCount && $buttonStart + $buttonCount - 1 > $pagination['pages'] ?
            $pagination['pages'] - $buttonCount + 1 : $buttonStart;

        //make pagination html
        $out = '<ul class="pagination">';

        if ($pagination['page'] > 1) {
            $params['page'] = null;
            $paramsQuery = http_build_query($params);
            $href = $path . ($paramsQuery ? '?' . $paramsQuery : '');
            $out .= '<li><a href="'.$href.'">&laquo;</a></li>';
        }
        if ($pagination['page'] > 1) {
            $params['page'] = $pagination['page'] - 1;
            $paramsQuery = http_build_query($params);
            $href = $path . ($paramsQuery ? '?' . $paramsQuery : '');
            $out .= '<li><a href="'.$href.'">&lsaquo;</a></li>';
        }
        for ($i = $buttonStart; $i <= $pagination['pages']; $i++) {
            $active = $i == $pagination['page'];
            $params['page'] = $active || $i==1 ? null : $i;
            $paramsQuery = http_build_query($params);
            $href = $path . ($paramsQuery ? '?' . $paramsQuery : '');
            $out .= $active ? '<li class="active"><span>'.$i.'</span></li>' :
                '<li><a href="'.$href.'">'.$i.'</a></li>';
            if ($i == $buttonStart + $buttonCount - 1) {
                break;
            }
        }
        if ($pagination['page'] < $pagination['pages']) {
            $params['page'] = $pagination['page'] + 1;
            $paramsQuery = http_build_query($params);
            $href = $path . ($paramsQuery ? '?' . $paramsQuery : '');
            $out .= '<li><a href="'.$href.'">&rsaquo;</a></li>';
        }
        if ($pagination['page'] < $pagination['pages']) {
            $params['page'] = $pagination['pages'];
            $paramsQuery = http_build_query($params);
            $href = $path . ($paramsQuery ? '?' . $paramsQuery : '');
            $out .= '<li><a href="'.$href.'">&raquo;</a></li>';
        }

        $out .= '</ul>';
        return $out;
    }

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

        return '<a href="'.$href.'">'.$icon.'&nbsp;'.$title.'</a>';
    }

    public function arrayMap($array, $from, $to)
    {
        $out = [];
        foreach ($array as $item) {
            $out[$item[$from]] = $item[$to];
        }
        return $out;
    }
}