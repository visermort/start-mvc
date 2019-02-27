<?php

namespace app\lib;

use app\App;
/**
 * Class Paginator
 * @package app\lib
 */
class Paginator
{
    protected $limit;
    protected $page = 1;
    protected $offset = 0;
    protected $pageCount = 1;
    protected $single = false;//one page
    protected $count = 0;
    protected $queryResult;

    /**
     * @param $query
     * @param array $params
     */
    public function __construct($query, $params = [])
    {
        $this->params = $params;
        $this->page = isset($params['page']) ? $params['page'] : $this->page;
        $this->single = isset($params['single']) ? $params['single'] : false;
        $this->limit = isset($params['limit']) ? $params['limit'] : App::getConfig('grids.limit');
        $this->page = $this->page == 0 ? 1 : $this->page;
        $this->offset =  $this->single ? 0 : $this->limit * ($this->page - 1);
        //query all count
        $this->count = $query->count();
        $this->pageCount = $this->count && $this->limit ? ceil($this->count / $this->limit) : 0;
        $this->pageCount = $this->single ? 1 : $this->pageCount;

        if ($this->page > $this->pageCount) {
            App::getController()->actionNotfound();
        }

        //result data
        $this->queryResult = $this->single ?  $query->get() : $query->limit($this->limit)->offset($this->offset)->get();
    }

    public function data()
    {
        return $this->queryResult;
    }

    public function buttons()
    {
        if ($this->single || $this->pageCount == 1) {
            return '';
        }
        $path = App::getRequest('path');
        $params = App::getRequest('get');
        $buttonCount = App::getConfig('grids.paginage_buttons');
        $start = $this->page - round(floor($buttonCount/2));
        $buttonStart = $start < 1 ? 1 : $start;
        $buttonStart = $this->pageCount > $buttonCount && $buttonStart + $buttonCount - 1 > $this->pageCount ?
            $this->pageCount - $buttonCount + 1 : $buttonStart;

        //make pagination html
        $out = '<ul class="pagination">';

        if ($this->page > 1) {
            $params['page'] = null;
            $paramsQuery = http_build_query($params);
            $href = $path . ($paramsQuery ? '?' . $paramsQuery : '');
            $out .= '<li><a class="ajax-button" href="'.$href.'">&laquo;</a></li>';
        }
        if ($this->page > 1) {
            $params['page'] = $this->page - 1;
            $paramsQuery = http_build_query($params);
            $href = $path . ($paramsQuery ? '?' . $paramsQuery : '');
            $out .= '<li><a class="ajax-button" href="'.$href.'">&lsaquo;</a></li>';
        }
        for ($i = $buttonStart; $i <= $this->pageCount; $i++) {
            $active = $i == $this->page;
            $params['page'] = $active || $i==1 ? null : $i;
            $paramsQuery = http_build_query($params);
            $href = $path . ($paramsQuery ? '?' . $paramsQuery : '');
            $out .= $active ? '<li class="active"><span>'.$i.'</span></li>' :
                '<li><a class="ajax-button" href="'.$href.'">'.$i.'</a></li>';
            if ($i == $buttonStart + $buttonCount - 1) {
                break;
            }
        }
        if ($this->page < $this->pageCount) {
            $params['page'] = $this->page + 1;
            $paramsQuery = http_build_query($params);
            $href = $path . ($paramsQuery ? '?' . $paramsQuery : '');
            $out .= '<li><a class="ajax-button" href="'.$href.'">&rsaquo;</a></li>';
        }
        if ($this->page < $this->pageCount) {
            $params['page'] = $this->pageCount;
            $paramsQuery = http_build_query($params);
            $href = $path . ($paramsQuery ? '?' . $paramsQuery : '');
            $out .= '<li><a class="ajax-button" href="'.$href.'">&raquo;</a></li>';
        }

        $out .= '</ul>';
        return $out;
    }

    public function info()
    {
        $from = $this->offset + 1;
        if ($this->single) {
            $to = $this->count;
        } else {
            $to = $this->offset + $this->limit < $this->count ? $this->offset + $this->limit : $this->count;
        }
        return 'Items ' . $from . ' - ' . $to . ' total '. $this->count;
    }
}
