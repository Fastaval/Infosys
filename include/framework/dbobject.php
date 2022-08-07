<?php
/**
 * Copyright (C) 2009-2012 Peter Lind
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
 * @category  Infosys
 * @package   Framework
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2009-2012 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

/**
 * Base table for all classes that represent objects from the database
 * In other words, the base of the ORM model
 *
 * @category Infosys
 * @package  Framework
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class DBObject
{

    /**
     * database object
     *
     * @var object
     */
    protected $db;

    /**
     * name of table object matches
     *
     * @var string
     */
    protected $tablename;

    /**
     * stores a copy of the entity factory used to create various objects
     *
     * @var object
     */
    protected $entity_factory;

    /**
     * stores info on the tables columns
     *
     * @var array
     */
    protected static $columninfos = array();

    /**
     * stores info on nullable columns
     *
     * @var array
     */
    protected static $nullable_columns = array();

    /**
     * stores info on the tables primary key
     *
     * @var array
     */
    protected static $primarykeys = array();

    /**
     * stores values for the primary key, to make sure they're never overwritten
     *
     * @var array
     */
    protected $pkvals = array();

    /**
     * stores various variables for use by the object
     *
     * @var array
     */
    protected $storage = array();

    /**
     * shows current loadstate of the object. Set to true by any of the find functions
     *
     * @var bool
     */
    protected $has_loaded = false;

    /**
     * @todo Do something useful here one day
     * @access public
     * @return void
     */
    public function __construct(EntityFactory $entity_factory)
    {
        $this->entity_factory = $entity_factory;
    }

    /**
     * returns an entity
     *
     * @param string $entityname - name of entity to create
     *
     * @access protected
     * @return bool|object - false on fail
     */
    protected function createEntity($entityname)
    {
        return $this->entity_factory->create($entityname);
    }

    /**
     * returns value from $this->storage if they're set
     *
     * @param string $var - name of variable to set
     *
     * @access public
     * @return mixed - the value asked for or null
     */
    public function __get($var)
    {
        $val = ((array_key_exists($var, $this->storage)) ? $this->storage[$var] : null);
        /*
        if (strpos($val, 'Ã¦') || strpos($val, 'Ã¸') || strpos($val, 'Ã¸') || strpos($val, 'Ã¥')) {
            $val = utf8_decode($val);
        }
        */
        if (is_string($val) && !ctype_digit($val) && mb_detect_encoding($val, 'UTF-8', true)) {
            if (stripos($val, 'ø') !== false || stripos($val, 'æ') !== false || stripos($val, 'å') !== false) return $val;
            if (stripos($val, 'Ø') !== false || stripos($val, 'Æ') !== false || stripos($val, 'Å') !== false) return $val;
            if (stripos($val, 'ö') !== false || stripos($val, 'ü') !== false || stripos($val, 'ä') !== false) return $val;
            if (stripos($val, 'Þ') !== false) return $val;
            if (stripos($val, 'ß') !== false) return $val;
            if (stripos($val, 'é') !== false) return $val;
            return utf8_decode($val);
        }
        return $val;
    }

    /**
     * This function sets a var in the objects storage array
     *
     * @param string name of var to set
     * @param mixed value to set
     * @return boolean
     */
    public function __set($varname, $value)
    {
        if (is_scalar($value)) {
            $value = htmlspecialchars_decode($value, ENT_QUOTES);
        }

        $this->storage[$varname] = $value;
    }

    /**
     * proper isset check, to check dynamic variables
     *
     * @param string $property
     *
     * @access public
     * @return bool
     */
    public function __isset($property)
    {
        if (isset($this->storage[$property])) {
            return true;
        }

        return false;
    }

    /**
     * returns info on the given table
     *
     * @access public
     * @return bool|array - false on fail, otherwise array of column names => column data type
     */
    public function getColumnInfo()
    {
        if (empty(self::$columninfos[get_class($this)])) {
            $this->buildTableInfo();
        }

        return self::$columninfos[get_class($this)];
    }

    /**
     * checks whether a column is nullable or not
     *
     * @param string $field
     *
     * @access public
     * @return bool
     */
    public function isFieldNullable($field)
    {
        if (!is_string($field)) {
            return false;
        }

        if (empty(self::$nullable_columns[get_class($this)])) {
            $this->buildTableInfo();
        }

        if (!isset(self::$nullable_columns[get_class($this)][$field])) {
            return false;
        }

        return self::$nullable_columns[get_class($this)][$field];
    }

    /**
     * returns valid values for a column
     * going by table definition
     *
     * @param string $column - name of column to get info for
     *
     * @access public
     * @return array
     */
    public function getValidColumnValues($column)
    {
        $info = $this->getColumnInfo();

        if (!isset($info[$column])) {
            return array();
        }

        if (strtolower($info[$column]) === 'text') {
            return array('type' => 'text');
        }

        if (substr(strtolower($info[$column]), 0, 3) === 'int') {
            return array('type' => 'int', 'size' => substr($info[$column], 4, -1));
        }

        if (substr(strtolower($info[$column]), 0, 7) === 'varchar') {
            return array('type' => 'varchar', 'size' => substr($info[$column], 8, -1));
        }

        if (substr(strtolower($info[$column]), 0, 4) === 'enum') {
            $types = array_map(create_function('$a', 'return str_replace("\'", "", $a);'), explode(',', substr($info[$column], 5, -1)));
            return array('type' => 'enum', 'values' => $types);
        }

        // default
        return array();
    }

    /**
     * returns info on the given table
     *
     * @access public
     * @return bool|array - false on fail, otherwise array of column names => column data type
     */
    public function getColumns()
    {
        if (empty(self::$columninfos[get_class($this)]))
        {
            $this->buildTableInfo();
        }
        return array_keys(self::$columninfos[get_class($this)]);
    }

    /**
     * returns info on the given table
     *
     * @access public
     * @return bool|array - false on fail, otherwise array of field names for the primary key
     */
    public function getPrimaryKey()
    {
        if (empty(self::$primarykeys[get_class($this)])) {
            $this->buildTableInfo();
        }

        return self::$primarykeys[get_class($this)];
    }

    /**
     * extracts data from a DESCRIBE TABLE call, to populate info on table columns
     * and primary key
     *
     * @access protected
     * @return bool
     */
    protected function buildTableInfo()
    {
        $DB = $this->getDB();
        $query = "DESCRIBE {$this->quoteTable($this->tablename)}";

        if (!($results = $DB->query($query))) {
            return false;
        }

        $class = get_class($this);

        foreach ($results as $result) {
            self::$columninfos[$class][$result['Field']] = $result['Type'];

            if ($result['Key'] == 'PRI') {
                self::$primarykeys[get_class($this)][] = $result['Field'];
            }

            self::$nullable_columns[$class][$result['Field']] = $result['Null'] == 'NO' ? false : true;

        }

        return true;
    }

//{{{ select methods: findById, findAll, findBySelect, findBySelectMany
    /**
     * returns a array of objects, loaded with one db row each, grabbing all rows from a table
     *
     * @access public
     * @return array
     */
    public function findAll()
    {
        $DB = $this->getDB();
        $pk = $this->getPrimaryKey();
        $select = $this->getSelect();
        foreach ($pk as $key)
        {
            $select->setOrder($key, 'asc');
        }
        $results = $DB->query($select);
        if (empty($results))
        {
            return array();
        }
        return $this->loadObjects($results);
    }

    /**
     * attempts to load an object by it's id
     *
     * @param int|array $id - int or array of values used for compound primary key
     *
     * @access public
     * @return object|bool - false on fail
     */
    public function findById($id)
    {
        $select = $this->getSelect();
        if (is_array($id) && is_array($this->getPrimaryKey()))
        {
            $fields = array_keys($this->getPrimaryKey());
            foreach ($id as $key => $value)
            {
                if (!in_array($key,$fields))
                {
                    continue;
                }
                $select->setWhere($key, "=", $value);
            }
        }
        elseif ((is_string($id) || is_numeric($id)) && count($this->getPrimaryKey()) == 1)
        {
            $pk = $this->getPrimaryKey();
            $select->setWhere($pk[0], '=', $id);
        }
        else
        {
            return false;
        }

        $results = $this->getDB()->query($select);
        if (!$results || !count($results))
        {
            return false;
        }
        return $this->loadObject($results[0], $this);
    }

    /**
     * attempts to load an object by it's name, if it has a name field
     *
     * @param string $name - string to look for
     *
     * @access public
     * @return object|bool - false on fail
     */
    public function findByName($name) {
        if (in_array('navn',$this->getColumns())) {
            $field = 'navn';
        } elseif (in_array('name',$this->getColumns())) {
            $field = 'name';
        } else {
            return false;
        }

        $select = $this->getSelect();
        $select->setWhere($field, '=', $name);
        $results = $this->getDB()->query($select);
        if (!$results || !count($results))
        {
            return false;
        }
        return $this->loadObject($results[0], $this);
    }

    /**
     * uses a select object to try to load the current object
     *
     * @param object $select - select object with various criteria set
     *
     * @return bool|object - false on fail
     * @access public
     */
    public function findBySelect(Select $select)
    {
        $results = $this->getDB()->query($select);
        return (($results && count($results)) ? $this->loadObject($results[0], $this) : false);
    }

    /**
     * uses a select object to try to load several objects of same kind as current
     *
     * @param object $select - select object with various criteria set
     *
     * @return array
     * @access public
     */
    public function findBySelectMany(Select $select)
    {
        $results = $this->getDB()->query($select);
        return (($results) ? $this->loadObjects($results) : array());
    }

    /**
     * returns the result of a SQL count query
     * basically wraps the param Select objects query in a count query
     *
     * @param object $Select - Select object
     * @access public
     * @return int
     */
    public function selectCount($select)
    {
        if (!$select instanceof Select)
        {
            return 0;
        }
        $results = $this->getDB()->query("SELECT COUNT(*) AS count FROM ({$select->assemble()}) AS COUNT", $select->getArguments());
        return (($results) ? $results[0]['count'] : 0);
    }

//}}}


    /**
     * updates a loaded object
     *
     * @access public
     * @return bool
     */
    public function update()
    {
        if (!$this->isLoaded()) {
            return false;
        }

        if (!$where = $this->generatePKWhere()) {
            return false;
        }

        $data = $args = array();
        $fields = $this->getColumns();
        $DB = $this->getDB();
        foreach ($fields as $field) {
            if ($this->checkIfPrimaryField($field) || ($this->$field === null && !$this->isFieldNullable($field))) {
                continue;
            }

            $data[] = " {$this->quoteTable($field)} = ?";
            $args[] = $this->$field;
        }
        $query = "UPDATE {$this->quoteTable($this->tablename)} SET " . implode(',',$data) . " WHERE " . implode(' AND ', array_keys($where));
        $args = array_merge($args, $where);
        $blob = array();
        foreach ($args as $arg) {
            $blob[] = $arg;
        }

        return $DB->exec($query, $blob);
    }

    /**
     * inserts a new object
     *
     * @access public
     * @return bool
     */
    public function insert()
    {
        if ($this->isLoaded()) {
            return false;
        }

        $data   = $args = array();
        $fields = $this->getColumns();

        foreach ($fields as $field) {
            if (($val = $this->$field) === null || !is_scalar($val)) {
                continue;
            }

            $data[$field] = "?";
            $args[]       = $this->$field;
        }

        if (in_array('created', $fields) && !in_array('created', array_keys($data))) {
            $data['created'] = '?';
            $args[] = date('Y-m-d H:i:s');
        }

        $query = "INSERT INTO {$this->quoteTable($this->tablename)} (`" . implode('`,`',array_keys($data)) . "`) VALUES (" . implode(',',$data) . ")";
        if ($id = $this->getDB()->exec($query, $args)) {
            return (($id && $id !== true) ? $this->findById($id) : true);
        }

        return false;
    }


    /**
     * deletes the currently loaded object and invalidates it
     *
     * @access public
     * @return bool
     */
    public function delete()
    {
        if (!$this->isLoaded() || !($where = $this->generatePKWhere())) {
            return false;
        }

        $query = "DELETE FROM {$this->quoteTable($this->tablename)} WHERE " . implode(' AND ', array_keys($where));
        $args = array();
        foreach ($where as $arg) {
            $args[] = $arg;
        }

        $this->getDB()->exec($query, $args);
        $this->invalidate();
        return true;
    }

//{{{ methods dealing with loading objects and invalidating them
    /**
     * creates a number of new objects and calls loadObject to fill them with data
     *
     * @param array $array Array of rows from the database
     *
     * @return array     Array of loaded objects of class $this
     * @access protected
     */
    protected function loadObjects($array)
    {
        $return = array();
        $class  = get_class($this);
        foreach ($array as $row) {
            $object   = $this->createEntity($class);
            $return[] = $this->loadObject($row, $object);
        }

        return $return;
    }

    /**
     * fills a model object with data from one row
     *
     * @param object $rowset Zend_Db_Table_Rowset
     * @access protected
     */
    protected function loadObject($row, $object)
    {
        $object->invalidate();
        $fields = $this->getColumns();
        foreach ($row as $field => $value)
        {
            if (in_array($field, $fields))
            {
                $object->$field = $value;
            }
        }
        $object->setLoaded();
        return $object;
    }

    /**
     * resets an object to an unloaded state
     *
     * @access public
     * @return object - returns itself
     */
    public function invalidate()
    {
        $this->storage = array();
        $this->has_loaded = false;
        $this->pkvals = array();
        return $this;
    }

    /**
     * checks that an object has been properly loaded, sets the loaded flag if so
     *
     * @access public
     */
    public function setLoaded()
    {
        $fields = $this->getPrimaryKey();

        foreach ($fields as $field) {
            if ($this->$field === null) {
                $this->invalidate();
                return;

            }

        }

        if (!$this->storePKVals()) {
            $this->invalidate();
            return;

        }

        $this->has_loaded = true;
    }

    /**
     * stores the primary key values for the object
     *
     * @access protected
     * @return bool
     */
    protected function storePKVals()
    {
        if (!$primary = $this->getPrimaryKey()) {
            return false;
        }

        foreach ($primary as $field) {
            if ($this->$field === null) {
                return false;
            }

            $this->pkvals[$field] = $this->$field;

        }

        return true;
    }


//}}}

//{{{ general methods dealing with checks of object consistency
    /**
     * returns the load state of the object
     *
     * @access public
     * @return bool
     */
    public function isLoaded()
    {
        return $this->has_loaded;
    }

    /**
     * checks if a given field is part of the tables primary key
     *
     * @param string $field - field to check
     * @access public
     * @return bool
     */
    public function checkIfPrimaryField($field)
    {
        if (is_array($this->getPrimaryKey()) && in_array($field, $this->getPrimaryKey()))
        {
            return true;
        }
        return false;
    }

    /**
     * generates a where clause that identifies the current object
     * it's based on the primary key for the table
     *
     * @access public
     * @return mixed - false on fail, string for single column pks, array for compound
     */
    public function generatePKWhere()
    {
        $DB      = $this->getDB();
        $primary = $this->getPrimaryKey();
        $where   = array();

        foreach ($primary as $field) {
            if (is_null($id = $this->pkvals[$field])) {
                return false;
            }

            $where["{$this->quoteTable($field)} = ?"] =  $id;

        }

        return $where;
    }

    public function quoteTable($string)
    {
        if (strstr($string, '.'))
        {
            $parts = explode('.', $string);
            foreach ($parts as &$part)
            {
                $part = "`{$part}`";
            }
            return implode('.', $parts);
        }
        return "`{$string}`";
    }

//}}}

    /**
     * returns a select object to be used in the select() method
     *
     * @access public
     * @return object
     */
    public function getSelect()
    {
        return new Select($this->tablename, $this->getDB());
    }

    /**
     * set the database object for the entity
     * called by the entity factory when entity is instantiated
     *
     * @param DB $db - instance of DB
     *
     * @throws EntityException
     * @access public
     * @return void
     */
    public function setDB(DB $db)
    {
        if (!empty($this->db))
        {
            throw new EntityException('DB object already set');
        }
        $this->db = $db;
    }

    /**
     * wrapper to get a database connection
     *
     * @throws EntityException
     * @access protected
     * @return resource
     */
    protected function getDB()
    {
        if (empty($this->db))
        {
            throw new EntityException('DB object not injected into entity');
        }
        return $this->db;
    }
}
