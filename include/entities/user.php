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
     * @package    MVC
     * @subpackage Entities
     * @author     Peter Lind <peter.e.lind@gmail.com>
     * @copyright  2009 Peter Lind
     * @license    http://www.gnu.org/licenses/gpl.html GPL 3
     * @link       http://www.github.com/Fake51/Infosys
     */

    /**
     * represents a user using the system
     *
     * @package MVC
     * @subpackage Entities
     */
class User extends DBObject
{

    /**
     * Name of table
     *
     * @var string
     */
    protected $tablename = "users";

    /**
     * checks the user object to see if a supplied password matches that of the user
     *
     * @param string $pass - password to check
     * @access public
     * @return bool
     */
    public function confirmPass($pass)
    {
        if (!$this->isLoaded()) {
            return false;
        }

        if (md5($pass) === $this->pass) {
            $this->pass = password_hash($pass, PASSWORD_DEFAULT);

            $this->update();
            return true;
        }

        return password_verify($pass, $this->pass);
    }

    /**
     * checks if a user has got access rights for the given controller and method
     *
     * @param string $controller - name of controller
     * @param string $method - name of method
     * @access public
     * @return bool
     */
    public function canAccess($controller, $method)
    {
        if (!$this->isLoaded()) {
            return false;
        }

        if (!($roles = $this->createEntity('UserRole')->getUserRoles($this))) {
            return false;
        }

        foreach ($roles as $role) {
            if ($role->canAccessControllermethod($controller, $method)) {
                return true;
            }

        }

        return false;
    }

    /**
     * checks if a user has a certain role
     *
     * @param object $role
     * @access public
     * @return bool
     */
    public function hasRole($role)
    {
        if (!$this->isLoaded()) {
            return false;
        }

        if (is_string($role)) {
            if (!($role = $this->createEntity('Role')->findByName($role))) {
                return false;
            }
        }

        return $this->createEntity('UserRole')->userHasRole($this,$role);
    }

    /**
     * returns a users roles
     *
     * @access public
     * @return array
     */
    public function getRoles()
    {
        if (!$this->isLoaded()) {
            return array();
        }

        return (($result = $this->createEntity('UserRole')->getUserRoles($this)) ? $result : array());
    }

    /**
     * adds a role to a user
     *
     * @param object $role - Role entity
     * @access public
     * @return bool
     */
    public function addRole($role)
    {
        if (!$this->isLoaded() || !is_object($role) || !$role->isLoaded()) {
            return false;
        }

        return $this->createEntity('UserRole')->addUserRole($this, $role);
    }

    /**
     * removes a role from a user
     *
     * @param object $role - Role entity
     * @access public
     * @return bool
     */
    public function removeRole($role)
    {
        if (!$this->isLoaded() || !is_object($role) || !$role->isLoaded()) {
            return false;
        }

        return $this->createEntity('UserRole')->removeUserRole($this, $role);
    }

    /**
     * disables a user
     *
     * @access public
     * @return bool
     */
    public function disable()
    {
        if (!$this->isLoaded()) {
            return false;
        }

        if ($this->disabled === 'ja') {
            return true;
        }

        $this->disabled = 'ja';

        return $this->update();
    }

    /**
     * enables a user
     *
     * @access public
     * @return bool
     */
    public function enable()
    {
        if (!$this->isLoaded()) {
            return false;
        }

        if ($this->disabled === 'nej') {
            return true;
        }

        $this->disabled = 'nej';

        return $this->update();
    }

    /**
     * returns true if the user was disabled
     *
     * @access public
     * @return bool
     */
    public function isDisabled()
    {
        return $this->disabled !== 'nej';
    }

    /**
     * returns instance loaded by email/username if found
     *
     * @param string $email_address Address to search by
     *
     * @access public
     * @return false|User
     */
    public function findByEmail($email_address)
    {
        $select = $this->getSelect();
        $select->setWhere('user', '=', $email_address);

        return $this->createEntity('User')->findBySelect($select);
    }
}
