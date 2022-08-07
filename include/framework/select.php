<?php
    /**
     * Copyright (C) 2009  Peter Lind
     *
     * This program is free software: you can redistribute it and/or modify
     * it under the terms of the GNU General Public License as published by
     * the Free Software Foundation, either version 3 of the License, or
     * (at your option) any later version.
     *
     * This program is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     * GNU General Public License for more details.
     *
     * You should have received a copy of the GNU General Public License
     * along with this program.  If not, see <http://www.gnu.org/licenses/gpl.html>.
     *
     * PHP version 5
     *
     * @package    Framework
     * @author     Peter Lind <peter.e.lind@gmail.com>
     * @copyright  2009 Peter Lind
     * @license    http://www.gnu.org/licenses/gpl.html GPL 3
     * @link       http://www.github.com/Fake51/Infosys
     */

    /**
     * used to generate SQL select statements instead of handwriting it
     * makes sure all vars are escaped
     *
     * @package Framework
     */
class Select
{

    /**
     * stores the where and fields
     *
     * @var array
     */
    protected $where_and_fields = array();

    /**
     * stores the where and values
     *
     * @var array
     */
    protected $where_and_values = array();

    /**
     * stores the where or fields
     *
     * @var array
     */
    protected $where_or_fields = array();

    /**
     * stores the where or values
     *
     * @var array
     */
    protected $where_or_values = array();

    /**
     * stores the order clauses
     *
     * @var array
     */
    protected $order = array();

    /**
     * stores the limit
     *
     * @var int
     */
    protected $limit;

    /**
     * stores the offset
     *
     * @var int
     */
    protected $offset;

    /**
     * stores the from clauses
     *
     * @var array
     */
    protected $from = array();

    /**
     * stores the left joins
     *
     * @var array
     */
    protected $left_join= array();

    /**
     * stores the fields to return
     *
     * @var array
     */
    protected $fields = array();

    /**
     * stores the group by clause
     *
     * @var array
     */
    protected $group_by = array();

    protected static $matches = array('=', '!=', '<', '>','<=','>=','LIKE', 'IN', 'NOT IN','IS NULL','IS NOT NULL');

    /**
     * constructor for the class - initiates the select for the calling object
     *
     * @param string $tablename - name of the table the select will work from
     * @param DB     $db        - db connection object
     *
     * @access public
     * @return void
     */
    public function __construct($tablename, DB $db)
    {
        $this->db = $db;
        $this->setFrom($tablename);
    }

    /**
     * sets the limit part of the offset/limit by clause
     *
     * @param int $limit
     *
     * @throws DBException
     * @access public
     * @return Select
     */
    public function setLimit($limit)
    {
        if (intval($limit) != $limit || $limit < 1)
        {
            throw new DBException("Bad value for Select::limit(): {$limit}");
        }
        $this->limit = intval($limit);
        return $this;
    }

    /**
     * sets the offset part of the offset/limit by clause
     *
     * @param int $offset
     *
     * @throws DBException
     * @access public
     * @return Select
     */
    public function setOffset($offset)
    {
        if (intval($offset) != $offset || $offset < 0)
        {
            throw new DBException("Bad value for Select::offset(): {$offset}");
        }
        $this->offset = intval($offset);
        return $this;
    }

    /**
     * sets an entry in the order by clause for the select
     *
     * @param string $field - column to order by
     * @param string $direction - asc|desc
     *
     * @throws DBException
     * @access public
     * @return Select
     */
    public function setOrder($field, $direction)
    {
        if (!is_string($field) || !is_string($direction) || !in_array(strtolower($direction), array('asc','desc')))
        {
            throw new DBException("Bad values for Select::setOrder(): {$field} - {$direction}");
        }
        $field = $this->fixField($field, true);
        $this->order[] = "{$field} " . strtoupper($direction);
        return $this;
    }

    /**
     * quotes fields and prefixed table names
     *
     * @param string $field - field to quote
     *
     * @access protected
     * @return string
     */
    protected function fixField($field, $quote = true)
    {
        $fields = explode('.', $field);
        foreach ($fields as &$field)
        {
            if ($quote)
            {
                $field = "`{$field}`";
            }
        }
        $field = implode('.', $fields);
        return $field;
    }

    /**
     * sets an entry in the where clause for the select, for AND queries
     *
     * @param string $field
     * @param string $match - what kind of match to use
     * @param string|number $value - value to match against
     * @param bool $quote - if the $field var should be quoted
     *
     * @throws DBException
     * @access public
     * @return Select
     */
    public function setWhere($field, $match, $value = null, $quote = true)
    {
        $vars = $this->handleWhereInput($field, $match, $value, $quote);
        if (empty($vars))
        {
            throw new DBException("Bad values for Select::setWhere(): {$field} - {$match} - {$value} - {$quote}");
        }
        $this->where_and_fields[] = "{$vars['field']} {$vars['match']}{$vars['value']}";
        $this->where_and_values[] = $value;
        return $this;
    }

    /**
     * sets a number of clauses in a parenthesis
     *
     * @param bool $intern_and - whether to combine the clauses internally with and or or
     * @param array $clauses - array of clauses to set
     * @param bool $extern_and - whether to connect the parenthesis to the rest of the query with and or or
     *
     * @throws DBException
     * @access public
     * @return Select
     */
    public function setWhereParens($intern_and, $clauses, $extern_and)
    {
        if (!is_array($clauses))
        {
            throw new DBException("Bad values for Select::setWhereParens()");
        }
        $DB = $this->getDB();
        $int_logic = (($intern_and) ? ' AND ' : ' OR ');
        $array = array();
        $store = $extern_and ? '_where_and_values' : '_where_or_values';
        foreach ($clauses as $field => $value)
        {
            $array[] =  $field . ' ?';
            $this->{$store}[] = $value;
        }
        $string = "(" . implode($int_logic, $array) . ")";
        if ($extern_and)
        {
            $this->where_and_fields[] = $string;
        }
        else
        {
            $this->where_or_fields[] = $string;
        }
        return $this;
    }

    /**
     * sets a where clause using a raw string, for those times when the other methods don't cut it
     *
     * @param string $clause - where clause
     * @param string $logic - and|or
     *
     * @throws DBException
     * @access public
     * @return Select
     */
    public function setRawWhere($clause, $logic)
    {
        if (!in_array(strtolower($logic), array('and', 'or')))
        {
            throw new DBException("Bad values for Select::setRawWhere()");
        }
        if (strtolower($logic) == 'and')
        {
            $this->where_and_fields[] = $clause;
        }
        else
        {
            $this->where_or_fields[] = $clause;
        }
        return $this;
    }

    /**
     * sets an entry in the where clause for the select, specifically for table links
     *
     * @param string $field1 - field in first table
     * @param string $field2 - field in second table
     *
     * @throws DBException
     * @access public
     * @return Select
     */
    public function setTableWhere($field1, $field2)
    {
        if (!is_string($field1) || !is_string($field2))
        {
            throw new DBException("Bad values for Select::setTableWhere(): {$field1} - {$field2}");
        }
        $this->where_and_fields[] = "{$this->fixField($field1)} = {$this->fixField($field2)}";
        return $this;
    }

    /**
     * sets an entry in the where clause for the select, for OR queries
     *
     * @param string $field
     * @param string $match - what kind of match to use
     * @param string|number $value - value to match against
     * @param bool $quote - if the $field var should be quoted
     *
     * @throws DBException
     * @access public
     * @return Select
     */
    public function setWhereOr($field, $match, $value = null, $quote = true)
    {
        $vars = $this->handleWhereInput($field, $match, $value, $quote);
        if (empty($vars))
        {
            throw new DBException("Bad values for Select::setWhereOr(): {$field} - {$match} - {$value}");
        }
        $this->where_or_fields[] = "{$vars['field']} {$vars['match']}{$vars['value']}";
        $this->where_or_values[] = $value;
        return $this;
    }

    /**
     * handles input to the setWhere methods
     *
     * @param string $field
     * @param string $match - what kind of match to use
     * @param string|number|object $value - value to match against, can be a Select object
     * @param bool $quote - if the $field var should be quoted
     *
     * @access public
     * @return bool
     */
    protected function handleWhereInput($field, $match, $value = null, $quote = true)
    {
        if (!is_string($field) || !in_array(strtoupper($match),self::$matches))
        {
            return false;
        }
        $field = $this->fixField($field, $quote);
        switch (strtoupper($match)) {
            case 'NOT IN':
            case 'IN':
                if ($value instanceof Select) {
                    $value = '(' . $value->assemble() . ')';
                    break;
                }

                if (!is_array($value)) {
                    return false;
                }

                foreach ($value as &$val) {
                    if (!is_scalar($val)) {
                        return false;
                    }

                    $val = '?';

                }

                $value = " (" . implode(',',$value) . ")";
                break;
            case 'IS NULL':
            case 'IS NOT NULL':
                $value = '';
                break;
            default:
                if (!isset($value) || !is_scalar($value)) {
                    return false;
                }

                $value = " ?";
                break;
        }

        return array(
            'field' => $field,
            'match' => $match,
            'value' => $value,
        );
    }

    /**
     * sets a left join clause in the query
     *
     * @param string $right_table - name of table to join
     * @param string $left_column - name of left table column to join on
     * @param string $right_column - name of right table column to join on
     *
     * @throws DBException
     * @access public
     * @return Select
     */
    public function setLeftJoin($right_table, $left_column, $right_column)
    {
        if (!is_string($right_table) || !is_string($left_column) || !is_string($right_column))
        {
            throw new DBException("Bad values for Select::setLeftJoin(): {$right_table} - {$left_column} - {$right_column}");
        }
        $this->left_join[] = "LEFT JOIN {$this->fixField($right_table)} ON {$this->fixField($left_column)} = {$this->fixField($right_column)}";
        return $this;
    }

    /**
     * sets an entry in the select query's from clause
     *
     * @param string $table - name of table to join with
     *
     * @throws DBException
     * @access public
     * @return Select
     */
    public function setFrom($table, $quote = true)
    {
        if (!is_string($table) || count(explode('.',$table)) > 1)
        {
            throw new DBException("Bad values for Select::setFrom(): {$table}");
        }
        $this->from[] = $this->fixField($table, $quote);
        return $this;
    }

    /**
     * sets an entry in the select query's from clause, using supplied subquery
     *
     * @param string $query - subquery (must start with SELECT and have no SQL quotes or ; in it)
     *
     * @throws DBException
     * @access public
     * @return Select
     */
    public function setSubqueryFrom($query)
    {
        if (!is_string($query) || strtolower(substr($query,0,6)) != 'select' || stristr($query,';'))
        {
            throw new DBException("Bad values for Select::setSubQueryFrom(): {$query}");
        }
        $this->from[] = $query;
        return $this;
    }

    /**
     * sets a field to retrieve for the query - default query uses *
     *
     * @param string $field - name of field to return
     * @param bool $quote - if $field should be quoted
     *
     * @throws DBException
     * @access public
     * @return Select
     */
    public function setField($field, $quote = true)
    {
        if (!is_string($field))
        {
            throw new DBException("Bad values for Select::setField(): {$field}");
        }
        $this->fields[] = $this->fixField($field, $quote);
        return $this;
    }

    /**
     * sets a group by clause entry
     *
     * @param string $field - column to group by
     * @param bool $quote - if the $field string should be quoted
     *
     * @throws DBException
     * @access public
     * @return Select
     */
    public function setGroupBy($field, $quote = true)
    {
        if (!is_string($field))
        {
            throw new DBException("Bad values for Select::setGroupBy(): {$field}");
        }
        $this->group_by[] = $this->fixField($field, $quote);
        return $this;
    }

    /**
     * returns the assembled select string
     *
     * @access public
     * @return string
     */
    public function assemble()
    {
        $return = "SELECT " . ((empty($this->fields)) ? '*' : implode(',', $this->fields));
        $return .= " FROM " . implode(',', array_reverse($this->from));
        if (!empty($this->left_join))
        {
            $return .= ' ' . implode(' ', $this->left_join);
        }
        if (!empty($this->where_or_fields) || !empty($this->where_and_fields))
        {
            $array = array();
            if (!empty($this->where_or_fields))
            {
                $array[] = implode(' OR ', $this->where_or_fields);
            }
            if (!empty($this->where_and_fields))
            {
                $array[] = implode(' AND ', $this->where_and_fields);
            }
            if (count($array))
            {
                $return .= " WHERE " . implode(' OR ',$array);
            }
        }
        $return .= ((!empty($this->group_by)) ? ' GROUP BY ' .implode(',',$this->group_by) : '');
        $return .= ((!empty($this->order)) ? ' ORDER BY ' .implode(',',$this->order) : '');
        if (!empty($this->offset) || !empty($this->limit))
        {
            $string = '';
            $offset = ((empty($this->offset)) ? 0 : $this->offset);
            $limit = ((empty($this->limit)) ? 0 : $this->limit);
            if ($offset != 0 || $limit != 0)
            {
                $string = " LIMIT {$offset},{$limit}";
            }
            $return .= $string;
        }
        return $return;
    }

    /**
     * returns the set arguments for the select
     *
     * @access public
     * @return array
     */
    public function getArguments()
    {
        return array_merge($this->getArgumentsFromArray($this->where_or_values), $this->getArgumentsFromArray($this->where_and_values));
    }

    /**
     * returns arguments from an array flattened
     *
     * @param array $array - arguments in an array, either scalar, arrays or selects
     *
     * @access protected
     * @return array
     */
    protected function getArgumentsFromArray(array $array)
    {
        $return = array();
        foreach ($array as $value)
        {
            if ($value instanceof Select)
            {
                $return = array_merge($return, $value->getArguments());
            }
            elseif (is_array($value))
            {
                $return = array_merge($return, $value);
            }
            else
            {
                $return[] = $value;
            }
        }
        return $return;
    }

    /**
     * returns the instance of the database connection
     *
     * @access protected
     * @return DB
     */
    protected function getDB()
    {
        return $this->db;
    }
}
