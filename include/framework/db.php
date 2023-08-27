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
 * @package   Framework
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2009 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

/**
 * DB exception type
 *
 * @package Framework
 * @author  Peter Lind <peter.e.lind@gmail.com>
 */
class DBException extends FrameworkException
{
}

/**
 * This class gives access to a database. It holds a few public properties, and has a couple of methods
 *
 * @package Framework
 * @author  Peter Lind <peter.e.lind@gmail.com>
 */
class DB
{
    /**
     * tracks transaction state
     *
     * @var bool
     */
    protected $transaction = false;

    /**
     * mysql connection resource
     *
     * @var resource
     */
    protected $connection;

    protected $last_error;
    protected $query_result;
    protected $last_query;
    protected $last_id;

    /**
     * number of rows changed by query
     *
     * @var int
     */
    protected $affected_rows;

    /**
     * makes sure the result variables are shielded from changes
     *
     * @param string $var - variable to fetch
     *
     * @access public
     * @return mixed
     */
    public function __get($var)
    {
        if (isset($this->$var)) {
            return $this->$var;
        } else if (($underline_var = '_' . $var) && isset($this->$underline_var)) {
            return $this->$underline_var;
        }

        return null;
    }

    /**
     * checks current state, inits things if they haven't been
     *
     * @throws DBException
     * @access public
     * @return void
     */
    public function __construct(Config $config)
    {
        try {
            $this->connection = new PDO($config->get('db.type') . ':host=' . $config->get('db.host') . ';port=' . $config->get('db.port') . ';dbname=' . $config->get('db.database'), $config->get('db.user'), $config->get('db.password'));
            if (false === $this->connection->exec("SET NAMES 'utf8mb4';") || false === $this->connection->exec("SET CHARACTER SET 'utf8mb4';") || false ===  $this->connection->exec("SET SESSION sql_mode='STRICT_ALL_TABLES';")) {
                $this->last_error = "Could not init database connection with proper settings.";
                throw new DBException($this->last_error);

            }

        } catch (Exception $e) {
            $this->last_error = "Connection to DataBase failed. Errormessage: {$e->getMessage()}";
            throw new DBException($this->last_error);
        }

    }

    /**
     * close the mysql connection and remove the resource
     *
     * @access public
     * @return void
     */
    public function __destruct()
    {
        $this->destroy();
    }

    /**
     * This function ends the connection with the database, if any has been opened.
     *
     * @access public
     * @return void
     */
    public function destroy()
    {
        if ($this->getState()) {
            if (is_resource($this->connection)) {
                if ($this->getTransactionState()) {
                    $this->rollback();
                }
            }

            $this->connection = null;
        }
    }

    /**
     * returns the current state of the connection to the database
     *
     * @return bool
     * @access public
     */
    public function getState()
    {
        return !empty($this->connection);
    }

    /**
     * sanitizes a given input string. It runs it through the mysql check
     *
     * @param mixed $input - input value to be escaped
     *
     * @throws DBException
     * @return bool
     * @access public
     */
    public function sanitize($input)
    {
        // Check if there's a connection to use
        if (!$this->getState()) {
            $this->last_error = "No connection has been established. Can't sanitize input.";
            throw new DBException($this->last_error);
        }

        if (!is_scalar($input)) {
            throw new DBException("Input is not scalar - cannot sanitize!");
        }

        $output = $this->connection->quote($input);
        if ($output == "" && $input != $output) {
            throw new DBException("Failed to sanitize input: {$input}");
        }

        return $output;
    }

    /**
     * executes a supplied SQL command, and records the result (if any)
     * in the public query_result variable.
     *
     * @throws DBException
     * @return bool
     * @access public
     */
    public function command(/* args */)
    {
        throw new DBException("This method should not be used any longer. Backtrace: " . serialize(debug_backtrace()));
    }

    /**
     * performs a select
     *
     * @throws DBException
     * @access public
     * @return array
     */
    public function query(/* args */)
    {
        $args = func_get_args();
        if (empty($args)) {
            throw new DBException("DB query method called with no arguments");
        }

        $query = array_shift($args);
        $mode = PDO::FETCH_DEFAULT;

        if ($query instanceof Select) {
            $args  = $query->getArguments();
            $query = $query->assemble();
        } else {
            if (
                !empty($args) 
                && (count($args) == 1 || (count($args) == 2 && $args[1] == 'assoc'))
                && is_array($args[0])
            ) {
                if ($args[1] == 'assoc') $mode = PDO::FETCH_ASSOC;
                $args = $args[0];
            }
        }

        $this->last_query = $query;
        // Check if there's a connection to use
        if (!$this->getState()) {
            $this->last_error = "No connection to database has been established.";
            error_log($this->last_error);
            throw new DBException($this->last_error);
        } elseif (!preg_match('/^\s*(describe|select)\s+/i', $query)) {
            $this->last_error = "Query is not a SELECT, exec() should have been used. Query: {$query}";
            error_log($this->last_error);
            throw new DBException($this->last_error);
        }

        $this->query_result = array();

        try {
            if (false === ($statement = $this->connection->prepare($query))) {
                error_log("Failed to prepare statement: {$query}");
                throw new DBException("Failed to prepare statement: {$query}");
            }

            if (false !== $statement->execute($args)) {
                while (false !== ($row = $statement->fetch($mode))) {
                    $this->query_result[] = $row;
                }
            } else {
                $error           = $statement->errorInfo();
                $this->last_error = "Query failed. Error: ". $error[2] . '. Query: ' . $statement->queryString;
                error_log($this->last_error);
                throw new DBException($this->last_error);
            }
        } catch (PDOException $e) {
            $text = "Issuing prepared statement failed\nQuery: $query\nArgs:".print_r($args,true)."\nError:".print_r($e->errorInfo,true)."\n";
            error_log("$text".$e->getMessage()."\n");
            throw new DBException("$text");
        }

        return $this->query_result;
    }

    /**
     * performs an insert, update or delete
     *
     * @throws DBException
     * @access public
     * @return array
     */
    public function exec(/* args */)
    {
        $args = func_get_args();
        if (empty($args)) {
            error_log($this->last_error);
            throw new DBException("DB exec method called with no arguments");
        }

        $query = array_shift($args);

        if ($query instanceof Select) {
            $args = $query->getArguments();
            $query = $query->assemble();
        } else {
            if (!empty($args) && count($args) == 1 && is_array($args[0])) {
                $args = $args[0];
            }
        }

        $this->last_query = $query;
        // Check if there's a connection to use
        if (!$this->getState()) {
            $this->last_error = "No connection to database has been established.";
            error_log($this->last_error);
            throw new DBException("No connection to database has been established.");
        } elseif (!preg_match('/^\s*(delete|insert|update|drop|alter|create|set)\s+/is', $query)) {
            $this->last_error = "Query is not one of INSERT, UPDATE or DELETE, query() should have been used. Query: {$query}";
            error_log($this->last_error);
            throw new DBException("Query is not one of INSERT, UPDATE or DELETE, query() should have been used. Query: {$query}");
        }

        try {
            if (false === ($statement = $this->connection->prepare($query))) {
                error_log("Failed to prepare statement: {$query}");
                throw new DBException("Failed to prepare statement: {$query}");
            }

            if (false !== $statement->execute($args)) {
                $this->last_id = false;
                if (preg_match('/^s*insert\s+/i', $query)) {
                    $this->last_id = $this->connection->lastInsertId();
                }

                $this->affected_rows = $statement->rowCount();
                return $this->last_id ? $this->last_id : true;
            } else {
                $error           = $statement->errorInfo();
                $this->last_error = "Query failed. Error: ". $error[2] . '. Query: ' . $statement->queryString;
                error_log($this->last_error);
                throw new DBException($this->last_error);
            }
        } catch (PDOException $e) {
            error_log("Issuing prepared statement failed - {$query}\n".$e->getMessage()."\n");
            $info = $statement->errorInfo();
            $message = "Issuing prepared statement failed\n";
            $message .= "Error Code:$info[0] Message:$info[2]\n";
            $message .= "Query:{$query}\n";
            $message .= "Args:".print_r($args, true);
            throw new DBException($message);
        }
    }

    /**
     * starts a transaction
     *
     * @throws DBException
     * @access public
     * @return void
     */
    public function begin()
    {
        if ($this->transaction) {
            return;
        }

        if (!$this->getState()) {
            error_log('No database connection established');
            throw new DBException('No database connection established');
        }

        if (!$this->connection->beginTransaction()) {
            error_log('Could not start transaction');
            throw new DBException('Could not start transaction');
        }

        $this->transaction = true;
    }

    /**
     * commits a transaction
     *
     * @throws DBException
     * @access public
     * @return void
     */
    public function commit()
    {
        if (!$this->getState()) {
            error_log('No database connection established');
            throw new DBException('No database connection established');
        }

        if (!$this->transaction) {
            error_log('No transaction started');
            throw new DBException('No transaction started');
        }

        if (!$this->connection->commit()) {
            throw new DBException('Could not commit transaction');
        }

        $this->transaction = false;
    }


    /**
     * rolls back a transaction
     *
     * @throws DBException
     * @access public
     * @return void
     */
    public function rollback()
    {
        if (!$this->getState()) {
            error_log('No database connection established');
            throw new DBException('No database connection established');
        }

        if (!$this->transaction) {
            error_log('No transaction started');
            throw new DBException('No transaction started');
        }

        if (!$this->connection->rollback()) {
            error_log('Could not rollback transaction');
            throw new DBException('Could not rollback transaction');
        }

        $this->transaction = false;
    }

    /**
     * returns true if a transaction has been started
     *
     * @access public
     * @return bool
     */
    public function getTransactionState()
    {
        return $this->transaction;
    }
}
