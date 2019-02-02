<?php

namespace app;

/**
 * Class Model
 * @package app
 */
class Model
{
    public $tableName;

    protected $database;

    /**
     * Model constructor.
     */
    public function __construct()
    {
        $this->connection = App::getConfig('database.connection');
        //make pdo
        $this->database = new \PDO(
            'mysql:host=' . $this->connection['host'] . ';dbname=' . $this->connection['database'] .
                ';charset=' . $this->connection['charset'],
            $this->connection['user'],
            $this->connection['password'],
            [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION, // вывод ошибок
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC // PDO::FETCH_NUM // тип вывода данных
            ]
        );
        $this->tablePrefix = $this->connection['table_prefix'];
    }

    /**
     * @param array $params
     */
    public function create($params = [])
    {

    }

    /**
     * @param array $params
     */
    public function update($params = ['where' => ['id' => 0]])
    {
        //
    }

    /**
     * @param array $params
     */
    public function delete($params = ['where' => ['id' => 0]])
    {
        $sql = 'DELETE FROM `' . $this->db->tablePrefix . $this->tableName . '`';
        if (!empty($params['where'])) {
            $where = [];
            foreach ($params['where'] as $key => $condition) {
                $where[] = '`' . $key . '` = :' . $key;
            }
            $where = implode(' AND ', $where);
            $sql .= ' WHERE ' . $where;
        }
        try {
            $smtp = $this->database->prepare($sql);
            $smtp->execute(!empty($params['where']) ? $params['where'] : null);
        } catch (\PDOException $e) {
            if (App::getConfig('debug')) {
                echo "Error !: " . $e->getMessage();
            }
        }
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function select($params = [])
    {
        $select = '';
        if (!empty($params['select'])) {
            //$select
        } else {
            $select = '`' . $this->tablePrefix . $this->tableName . '`.*';
        }
        $sql = 'SELECT ' . $select . ' FROM `' . $this->tablePrefix . $this->tableName . '`';
        if (!empty($params['where'])) {
            $where = [];
            foreach ($params['where'] as $key => $condition) {
                $where[] = '`' . $key . '` = :' . $key;
            }
            $where = implode(' AND ', $where);
            $sql .= ' WHERE ' . $where;
        }
        if (isset($params['limit'])) {
            $sql .= ' LIMIT ' . $params['limit'];
            if (isset($params['offset'])) {
                $sql .= ',' . $params['offset'];
            }
        }
        $smtp = $this->database->prepare($sql);
        $smtp->execute(!empty($params['where']) ? $params['where'] : null);
        $out = $smtp->fetchAll();
        return $out;
    }

    /**
     * @param $sql
     */
    public function rawSql($sql)
    {
        try {
            $smtp = $this->database->sql($sql);
            $smtp->execute();
        } catch (\PDOException $e) {
            if (App::getConfig('debug')) {
                echo "Error !: " . $e->getMessage();
            }
        }
    }
}
