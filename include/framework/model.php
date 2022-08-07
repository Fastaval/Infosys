<?php
/**
 * Copyright (C) 2009-2012  Peter Lind
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
 * PHP version 5.3+
 *
 * @category  Infosys
 * @package   Framework
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2009-2012 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

/**
 * base class for all models used in the MVC
 *
 * @category Infosys
 * @package  Framework
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class Model extends Common
{
    /**
     * db connection object
     *
     * @var DB
     */
    protected $db;

    /**
     * user object
     *
     * @var User
     */
    protected $user;

    /**
     * stores a copy of the entity factory
     *
     * @var string
     */
    protected $entity_factory;

    /**
     * public storage space
     *
     * @var array
     */
    protected $storage = array();

    /**
     * Config object
     *
     * @var Config
     */
    protected $config;

    /**
     * DIC object
     *
     * @var DIC
     */
    protected $dic;

    /**
     * common magic get function, to grab things from the storage space of the classes
     *
     * @param string $var Name of variable to get
     *
     * @access public
     * @return mixed
     */
    public function __get($var)
    {   
        return ((array_key_exists($var, $this->storage)) ? $this->storage[$var] : null);
    }   

    /** 
     * Set a variable in the view, checks that the variable exists first
     *
     * @param string $varname Name of var to set
     * @param mixed  $value   Value to set variable to
     *
     * @access public
     * @return void
     */
    public function __set($varname, $value)
    {   
        $this->storage[$varname] = $value;
    }   

    /**
     * Loads a page object using page name
     *
     * @param DB $db - db connection object
     *
     * @access public
     * @return void
     */
    public function __construct(DB $db, Config $config, DIC $dic)
    {
        $this->db     = $db;
        $this->config = $config;
        $this->dic    = $dic;
    }

    /**
     * returns an entity
     *
     * @param string $entityname Name of entity to create
     *
     * @access protected
     * @return bool|object - false on fail
     */
    protected function createEntity($entityname)
    {
        return $this->dic->get('EntityFactory')->create($entityname);
    }

    /**
     * Log the current user out, ending the session as well
     *
     * @access public
     * @return void
     */
    public function logOut()
    {
        $session = new Session($this->config);
        $session->delete('user_id');
        $session->end();
    }

    /**
     * Try to log the user in using session vars
     * - doubles as a check to see if a user is logged in
     *
     * @access public
     * @return false|User
     */
    public function getLoggedInUser()
    {
        if (empty($this->user)) {
            $user    = $this->createEntity('User');
            $session = new Session($this->config);

            if (!($user_id = $session->user_id) || intval($user_id) != $user_id || !($user = $user->findById($user_id))) {
                unset($user);
                return false;
            }

            $this->user = $user;
        }

        return $this->user;
    }

    /**
     * handles a user trying to log in to the system
     *
     * @param string $username Username of user logging in
     * @param string $pass     Password for user
     *
     * @access public
     * @return bool
     */
    public function handleLogin($username, $pass)
    {
        $user   = $this->createEntity('User');
        $select = $user->getSelect();
        $select->setWhere('user', '=', $username);

        if (!$user->findBySelect($select) || !$user->confirmPass($pass)) {
            $this->log("User failed login. Username: {$username}, IP: {$_SERVER['REMOTE_ADDR']}", 'Login', $user);
            return false;

        }

        if ($user->disabled == 'ja') {
            $this->log("User has been disabled. Username: {$username}, IP: {$_SERVER['REMOTE_ADDR']}", 'Login', $user);
            return false;

        }

        $this->log("User logged in: {$user->user}", 'Login', $user);
        $this->sessionSet('user_id', $user->id);
        return true;
    }

    /**
     * searches for a given kind of entity, given type and id
     *
     * @param string $entity - type of entity to look for
     * @param int    $id     - id of entity to find
     *
     * @access public
     * @return bool|object
     */
    public function findEntity($entity, $id)
    {
        return $this->createEntity($entity)->findById($id);
    }

    /**
     * extracts ids from an array of entities
     *
     * @param array  $array Array of entities
     * @param string $field Field to use as id
     *
     * @access public
     * @return array
     */
    public function getIds($array, $field = 'id')
    {
        $temp_array = array();
        foreach ($array as $arr) {
            $temp_array[] = $arr->$field;
        }
        return $temp_array;
    }

    /**
     * Model factory
     *
     * @param string $model Name of model to create
     *
     * @throws Exception
     * @access public
     * @return Model
     */
    public function factory($model)
    {
        if (!class_exists($model . 'Model')) {
            throw new FrameworkException('No such model exists: ' . $model . 'Model');
        }

        $class = $model . 'Model';

        return new $class($this->db, $this->config, $this->dic);
    }

    /**
     * checks if the current user matches one of the
     * supplied usernames
     *
     * @param array $usernames Users to check against
     *
     * @access public
     * @return bool
     */
    public function isUser(array $usernames)
    {
        return in_array($this->getLoggedInUser()->user, $usernames);
    }

    /**
     * checks if the current user matches one of the
     * supplied usernames
     *
     * @param array $usernames Users to check against
     *
     * @access public
     * @return bool
     */
    public function userHasRole($role)
    {
        return $this->getLoggedInUser()->hasRole($role);
    }

    /**
     * hack to avoid duplicate instantiation of karma object
     *
     * @access public
     * @return Karma
     */
    public function buildKarma()
    {
        $ruleset = [
                    new \KarmaFlatValueRule('potential', 0, -7),
                    new \KarmaDiscreteValuesRule('potential', 1, [1 => -12, 2 => -20, 3 => -26, 4 => -29, 5 => -32, 6 => -34, 7 => -35, 8 => -36]),
                    new \KarmaDiscreteValuesRule('potential', 2, [1 => -1, 2 => -2, 3 => -3]),
                    new \KarmaDiscreteValuesRule('potential', 3, [1 => -1]),
                    new \KarmaDiscreteValuesRule('factual', 1, [1 => 10, 2 => 20, 3 => 30, 4 => 40, 5 => 50, 6 => 60, 7 => 70, 8 => 80, 9 => 90, 10 => 100]),
                    new \KarmaDiscreteValuesRule('factual', 3, [1 => 7, 2 => 14, 3 => 21, 4 => 28, 5 => 35, 6 => 42, 7 => 49, 8 => 56, 9 => 63, 10 => 70]),
                   ];

        $karma = new \Karma($this->db, $ruleset);

        return $karma;
    }
}
