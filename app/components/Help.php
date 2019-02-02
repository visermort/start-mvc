<?php

namespace app\components;

use app\App;
/**
 * Class Help
 * @package app\components
 */
class Help
{
    /**
     * @param $format
     * @param bool $date
     * @return string
     */
    public function date($format, $date = false)
    {
        $date = $date ? $date : time();
        return date($format, $date);
    }

    public function pagination($pagination)
    {
        if ((isset($pagination['single']) && $pagination['single']) || $pagination['pages'] == 1) {
            return '';
        }
        $path = App::getRequest('path');
        $params = App::getRequest('get');

        //make pagination html
        $out = '<ul class="pagination">';
        for ($i=1; $i <= $pagination['pages']; $i++) {
            $active = $i == $pagination['page'];
            $params['page'] = $active || $i==1 ? null : $i;
            $paramsQuery = http_build_query($params);
            $href = $path . ($paramsQuery ? '?' . $paramsQuery : '');
            if ($i == 1) {
                $out .= $active ? '<li class="active"><span>&laquo;</span></li>' :
                    '<li><a href="'.$href.'">&laquo;</a></li>';
            }
            $out .= $active ? '<li class="active"><span>'.$i.'</span></li>' :
                '<li><a href="'.$href.'">'.$i.'</a></li>';
            if ($i==$pagination['pages']) {
                $out .= $active ? '<li class="active"><span>&raquo;</span></li>' :
                    '<li><a href="'.$href.'">&raquo;</a></li>';
            }

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

    /**
     * @return string
     */
    public function user()
    {
        $user = App::getUser();
        if ($user) {
            $out = '<li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">'.$user['first_name'].' '.$user['last_name'].'<b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="/account">Home</a>
                        </li>
                        <li>
                            <a href="/account/logout">Logout</a>
                        </li>
                    </ul>
                </li>';
        } else {
            $out = '<li><a href="/login">Login</a></li>';
        }
        return $out;
    }

    /**
     * @param $status
     * @return string
     */
    public function status($status)
    {
        return $status ? 'Completed' : 'In process';
    }
}