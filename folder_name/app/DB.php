<?php

namespace App;

use PDO;

if (!defined('TMVC_SQL_NONE'))
    define('TMVC_SQL_NONE', 0);
if (!defined('TMVC_SQL_INIT'))
    define('TMVC_SQL_INIT', 1);
if (!defined('TMVC_SQL_ALL'))
    define('TMVC_SQL_ALL', 2);

final class DB {

    public function __construct() {
        if (!is_null($this->pdo))
            return $this->pdo;

        $this->pdo = new PDO('mysql:dbname=' . DB_NAME . ';host=' . DB_HOST, DB_USER, DB_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $this->pdo;
    }

    /**
     * $pdo
     *
     * the PDO object handle
     *
     * @access	public
     */
    public $pdo = null;

    /**
     * $result
     *
     * the query result handle
     *
     * @access	public
     */
    public $result = null;

    /**
     * $fetch_mode
     *
     * the results fetch mode
     *
     * @access	public
     */
    public $fetch_mode = PDO::FETCH_ASSOC;

    /**
     * $query_params
     *
     * @access	public
     */
    public $query_params = array('select' => '*');

    /**
     * $last_query
     *
     * @access	public
     */
    public $last_query = null;

    /**
     * $last_query_type
     *
     * @access	public
     */
    public $last_query_type = null;

    /**
     * select
     *
     * set the  active record select clause
     *
     * @access	public
     * @param   string $clause
     */
    function select($clause, $join = false) {
        if ($join)
            return $this->query_params['select'] .= ', ' . $clause;

        return $this->query_params['select'] = $clause;
    }

    /**
     * from
     *
     * set the  active record from clause
     *
     * @access	public
     * @param   string $clause
     */
    function from($clause) {
        return $this->query_params['from'] = $clause;
    }

    /**
     * where
     *
     * set the  active record where clause
     *
     * @access	public
     * @param   string $clause
     */
    function where($clause, $args) {
        if (empty($clause))
            throw new Exception(sprintf("where cannot be empty"));

        if (!preg_match('![=<>]!', $clause))
            $clause .= '=';

        if (strpos($clause, '?') === false)
            $clause .= '?';

        $this->_where($clause, (array )$args, 'AND');
    }

    /**
     * orwhere
     *
     * set the  active record orwhere clause
     *
     * @access	public
     * @param   string $clause
     */
    function orwhere($clause, $args) {
        $this->_where($clause, $args, 'OR');
    }

    /**
     * _where
     *
     * set the active record where clause
     *
     * @access	public
     * @param   string $clause
     */
    public function _where($clause, $args = array(), $prefix = 'AND') {
        // sanity check
        if (empty($clause))
            return false;

        // make sure number of ? match number of args
        if (($count = substr_count($clause, '?')) && (count($args) != $count))
            throw new Exception(sprintf("Number of where clause args don't match number of ?: '%s'", $clause));

        if (!isset($this->query_params['where']))
            $this->query_params['where'] = array();

        return $this->query_params['where'][] = array(
            'clause' => $clause,
            'args' => $args,
            'prefix' => $prefix);
    }

    /**
     * join
     *
     * set the  active record join clause
     *
     * @access	public
     * @param   string $clause
     */
    function join($join_table, $join_on, $join_type = null) {
        $clause = "JOIN {$join_table} ON {$join_on}";

        if (!empty($join_type))
            $clause = $join_type . ' ' . $clause;

        if (!isset($this->query_params['join']))
            $this->query_params['join'] = array();

        $this->query_params['join'][] = $clause;
    }

    /**
     * in
     *
     * set an active record IN clause
     *
     * @access	public
     * @param   string $clause
     */
    function in($field, $elements, $list = false) {
        $this->_in($field, $elements, $list, 'AND');
    }

    /**
     * orin
     *
     * set an active record OR IN clause
     *
     * @access	public
     * @param   string $clause
     */
    function orin($field, $elements, $list = false) {
        $this->_in($field, $elements, $list, 'OR');
    }

    /**
     * _in
     *
     * set an active record IN clause
     *
     * @access	public
     * @param   string $clause
     */
    public function _in($field, $elements, $list = false, $prefix = 'AND') {
        if (!$list) {
            if (!is_array($elements))
                $elements = explode(',', $elements);

            // quote elements for query
            foreach ($elements as $idx => $element)
                $elements[$idx] = $this->pdo->quote($element);

            $clause = sprintf("{$field} IN (%s)", implode(',', $elements));
        } else
            $clause = sprintf("{$field} IN (%s)", $elements);

        $this->_where($clause, array(), $prefix);
    }

    /**
     * orderby
     *
     * set the  active record orderby clause
     *
     * @access	public
     * @param   string $clause
     */
    function orderby($clause) {
        $this->_set_clause('orderby', $clause);
    }

    /**
     * groupby
     *
     * set the active record groupby clause
     *
     * @access	public
     * @param   string $clause
     */
    function groupby($clause) {
        $this->_set_clause('groupby', $clause);
    }

    /**
     * limit
     *
     * set the active record limit clause
     *
     * @access	public
     * @param   int    $limit
     * @param   int    $offset
     */
    function limit($limit, $offset = 0) {
        if (!empty($offset))
            $this->_set_clause('limit', sprintf('%d,%d', (int)$offset, (int)$limit));
        else
            $this->_set_clause('limit', sprintf('%d', (int)$limit));
    }

    /**
     * _set_clause
     *
     * set an active record clause
     *
     * @access	public
     * @param   string $clause
     */
    public function _set_clause($type, $clause, $args = array()) {
        // sanity check
        if (empty($type) || empty($clause))
            return false;

        $this->query_params[$type] = array('clause' => $clause);

        if (isset($args))
            $this->query_params[$type]['args'] = $args;

    }

    /**
     * _query_assemble
     *
     * get an active record query
     *
     * @access	public
     * @param   string $fetch_mode the PDO fetch mode
     */
    public function _query_assemble(&$params, $fetch_mode = null) {

        if (empty($this->query_params['from'])) {
            throw new Exception("Unable to get(), set from() first");
            return false;
        }

        $query = array();
        $query[] = "SELECT {$this->query_params['select']}";
        $query[] = "FROM {$this->query_params['from']}";

        // assemble join clause
        if (!empty($this->query_params['join']))
            foreach ($this->query_params['join'] as $cjoin)
                $query[] = $cjoin;

        // assemble where clause
        if ($where = $this->_assemble_where($where_string, $params))
            $query[] = $where_string;

        // assemble groupby clause
        if (!empty($this->query_params['groupby']))
            $query[] = "GROUP BY {$this->query_params['groupby']['clause']}";

        // assemble orderby clause
        if (!empty($this->query_params['orderby']))
            $query[] = "ORDER BY {$this->query_params['orderby']['clause']}";

        // assemble limit clause
        if (!empty($this->query_params['limit']))
            $query[] = "LIMIT {$this->query_params['limit']['clause']}";

        $query_string = implode(' ', $query);
        $this->last_query = $query_string;

        $this->query_params = array('select' => '*');

        return $query_string;

    }

    /**
     * _assemble_where
     *
     * assemble where query
     *
     * @access	public
     */
    public function _assemble_where(&$where, &$params) {
        if (!empty($this->query_params['where'])) {
            $where_init = false;
            $where_parts = array();
            $params = array();
            foreach ($this->query_params['where'] as $cwhere) {
                $prefix = !$where_init ? 'WHERE' : $cwhere['prefix'];
                $where_parts[] = "{$prefix} {$cwhere['clause']}";
                $params = array_merge($params, (array )$cwhere['args']);
                $where_init = true;
            }
            $where = implode(' ', $where_parts);
            return true;
        }
        return false;
    }

    /**
     * query
     *
     * execute a database query
     *
     * @access	public
     * @param   array $params an array of query params
     * @param   int $fetch_mode the fetch formatting mode
     */
    function query($query = null, $params = null, $fetch_mode = null) {
        if (!isset($query))
            $query = $this->_query_assemble($params, $fetch_mode);

        return $this->_query($query, $params, TMVC_SQL_NONE, $fetch_mode);
    }

    /**
     * query_all
     *
     * execute a database query, return all records
     *
     * @access	public
     * @param   array $params an array of query params
     * @param   int $fetch_mode the fetch formatting mode
     */
    function query_all($query = null, $params = null, $fetch_mode = null) {
        if (!isset($query))
            $query = $this->_query_assemble($params, $fetch_mode);

        return $this->_query($query, $params, TMVC_SQL_ALL, $fetch_mode);
    }

    /**
     * query_one
     *
     * execute a database query, return one record
     *
     * @access	public
     * @param   array $params an array of query params
     * @param   int $fetch_mode the fetch formatting mode
     */
    function query_one($query = null, $params = null, $fetch_mode = null) {
        if (!isset($query)) {
            $this->limit(1);
            $query = $this->_query_assemble($params, $fetch_mode);
        }

        return $this->_query($query, $params, TMVC_SQL_INIT, $fetch_mode);
    }

    /**
     * _query
     *
     * internal query method
     *
     * @access	public
     * @param   string $query the query string
     * @param   array $params an array of query params
     * @param   int $return_type none/all/init
     * @param   int $fetch_mode the fetch formatting mode
     */
    function _query($query, $params = null, $return_type = TMVC_SQL_NONE, $fetch_mode = null) {

        /* if no fetch mode, use default */
        if (!isset($fetch_mode))
            $fetch_mode = PDO::FETCH_ASSOC;

        /* prepare the query */
        try {
            $this->result = $this->pdo->prepare($query);
        }
        catch (PDOException $e) {
            throw new Exception(sprintf("PDO Error: %s Query: %s", $e->getMessage(), $query));
            return false;
        }

        /* execute with params */
        try {
            $this->result->execute($params);
        }
        catch (PDOException $e) {
            throw new Exception(sprintf("PDO Error: %s Query: %s", $e->getMessage(), $query));
            return false;
        }

        //echo $query;

        /* get result with fetch mode */
        $this->result->setFetchMode($fetch_mode);

        switch ($return_type) {
            case TMVC_SQL_INIT:
                return $this->result->fetch();
                break;
            case TMVC_SQL_ALL:
                return $this->result->fetchAll();
                break;
            case TMVC_SQL_NONE:
            default:
                return true;
                break;
        }

    }

    /**
     * update
     *
     * update records
     *
     * @access	public
     * @param   int $fetch_mode the fetch formatting mode
     */
    function update($table, $columns) {
        if (empty($table)) {
            throw new Exception("Unable to update, table name required");
            return false;
        }
        if (empty($columns) || !is_array($columns)) {
            throw new Exception("Unable to update, at least one column required");
            return false;
        }
        $query = array("UPDATE `{$table}` SET");
        $fields = array();
        $params = array();
        foreach ($columns as $cname => $cvalue) {
            if (!empty($cname)) {
                $fields[] = "{$cname}=?";
                $params[] = $cvalue;
            }
        }
        $query[] = implode(',', $fields);

        // assemble where clause
        if ($this->_assemble_where($where_string, $where_params)) {
            $query[] = $where_string;
            $params = array_merge($params, $where_params);
        }

        $query = implode(' ', $query);

        $this->query_params = array('select' => '*');

        return $this->_query($query, $params);
    }

    /**
     * insert
     *
     * update records
     *
     * @access	public
     * @param   string $table
     * @param   array  $columns
     */
    function insert($table, $columns) {
        if (empty($table)) {
            throw new Exception("Unable to insert, table name required");
            return false;
        }
        if (empty($columns) || !is_array($columns)) {
            throw new Exception("Unable to insert, at least one column required");
            return false;
        }

        $column_names = array_keys($columns);

        $query = array(sprintf("INSERT INTO `{$table}` (`%s`) VALUES", implode('`,`', $column_names)));
        $fields = array();
        $params = array();
        foreach ($columns as $cname => $cvalue) {
            if (!empty($cname)) {
                $fields[] = "?";
                $params[] = $cvalue;
            }
        }
        $query[] = '(' . implode(',', $fields) . ')';

        $query = implode(' ', $query);

        $this->_query($query, $params);
        return $this->last_insert_id();
    }

    /**
     * delete
     *
     * delete records
     *
     * @access	public
     * @param   string $table
     * @param   array  $columns
     */
    function delete($table) {
        if (empty($table)) {
            throw new Exception("Unable to delete, table name required");
            return false;
        }
        $query = array("DELETE FROM `{$table}`");
        $params = array();

        // assemble where clause
        if ($this->_assemble_where($where_string, $where_params)) {
            $query[] = $where_string;
            $params = array_merge($params, $where_params);
        }

        $query = implode(' ', $query);

        $this->query_params = array('select' => '*');

        return $this->_query($query, $params);
    }

    /**
     * next
     *
     * go to next record in result set
     *
     * @access	public
     * @param   int $fetch_mode the fetch formatting mode
     */
    function next($fetch_mode = null) {
        if (isset($fetch_mode))
            $this->result->setFetchMode($fetch_mode);
        return $this->result->fetch();
    }

    /**
     * last_insert_id
     *
     * get last insert id from previous query
     *
     * @access	public
     * @return	int $id
     */
    function last_insert_id() {
        return $this->pdo->lastInsertId();
    }

    /**
     * num_rows
     *
     * get number of returned rows from previous select
     *
     * @access	public
     * @return	int $id
     */
    function num_rows() {
        return $this->result->rowCount();
    }

    /**
     * affected_rows
     *
     * get number of affected rows from previous insert/update/delete
     *
     * @access	public
     * @return	int $id
     */
    function affected_rows() {
        return $this->result->rowCount();
    }

    /**
     * last_query
     *
     * return last query executed
     *
     * @access	public
     */
    function last_query() {
        return $this->last_query;
    }

    /**
     * class destructor
     *
     * @access	public
     */
    function __destruct() {
        $this->pdo = null;
    }

}
