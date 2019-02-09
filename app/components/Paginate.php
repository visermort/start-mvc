<?php

namespace app\components;
use app\App;
use app\Component;

/**
 * Class Paginate
 * @package app\components
 */
class Paginate extends Component
{
    protected $database;
    protected $limit;
    protected $page = 1;
    protected $offset = 0;
    protected $pageCount = 1;
    protected $single = false;//one page
    protected $count = 0;

    /** start action
     * @param $database
     * @param array $params
     */
    public function start($database, $params = [])
    {
        $this->database = $database;
        $this->page = isset($params['page']) ? $params['page'] : $this->page;
        $this->single = isset($params['single']) ? $params['single'] : false;
        $this->limit = isset($params['limit']) ? $params['limit'] : App::getConfig('grids.limit');
        $this->page = $this->page == 0 ? 1 : $this->page;
        $this->params = $params;
        $this->offset = $this->limit * ($this->page - 1);
    }

    /** main data
     * @return mixed
     */
    public function data()
    {
        $this->count = $this->database->count();
        $data = $this->database->limit($this->limit)->offset($this->offset)->get();
        $this->pageCount = $this->count && $this->limit ? ceil($this->count / $this->limit) : 0;
        return $data;
    }

    /**pagination data
     * @return array
     */
    public function pagination()
    {
        return [
            'page' => $this->page,
            'limit' => $this->limit,
            'offset' => $this->offset,
            'count' => $this->count,
            'single' => $this->single,
            'pages' => $this->pageCount,
        ];
    }

}