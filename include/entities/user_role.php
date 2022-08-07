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
     * handles the user-role link of the rights system
     *
     * @package MVC
     * @subpackage Entities
     */
class UserRole extends DBObject
{

    protected $tablename = 'users_roles';

    /**
     * returns array of user roles for a given user
     *
     * @param object $user - user object to fetch roles for
     * @access public
     * @return array
     */
    public function getUserRoles($user)
    {
        if (!is_object($user) || !$user->isLoaded())
        {
            return array();
        }
        $ur = $this->createEntity('UserRole');
        $select = $ur->getSelect();
        $select->setWhere('user_id','=',$user->id);
        $rolelinks = $ur->findBySelectMany($select);
        if (empty($rolelinks))
        {
            return array();
        }
        $ids = array();
        foreach ($rolelinks as $link)
        {
            $ids[] = $link->role_id;
        }
        $select = $this->createEntity('Role')->getSelect();
        $select->setWhere('id', 'in', $ids);
        return $this->createEntity('Role')->findBySelectMany($select);
    }

    /**
     * returns true if the user has the role
     *
     * @param object $user - User entity
     * @param object $role - Role entity
     * @access public
     * @return bool
     */
    public function userHasRole($user, $role)
    {
        if (!is_object($user) || !$user->isLoaded() || !is_object($role) || !$role->isLoaded())
        {
            return false;
        }
        $select = $this->getSelect();
        $select->setWhere('user_id','=',$user->id);
        $select->setWhere('role_id','=',$role->id);
        return (($this->findBySelectMany($select)) ? true : false);
    }

    /**
     * adds a role to a user
     *
     * @param object $user - User entity
     * @param object $role - Role entity
     * @access public
     * @return bool
     */
    public function addUserRole($user, $role)
    {
        if (!is_object($user) || !$user->isLoaded() || !is_object($role) || !$role->isLoaded())
        {
            return false;
        }
        $userrole = $this->createEntity('UserRole');
        $userrole->user_id = $user->id;
        $userrole->role_id = $role->id;
        return $userrole->insert();
    }

    /**
     * removes a role from a user
     *
     * @param object $user - User entity
     * @param object $role - Role entity
     * @access public
     * @return bool
     */
    public function removeUserRole($user, $role)
    {
        if (!is_object($user) || !$user->isLoaded() || !is_object($role) || !$role->isLoaded())
        {
            return false;
        }
        $select = $this->getSelect();
        $select->setWhere('user_id','=',$user->id);
        $select->setWhere('role_id','=',$role->id);
        if (!($userrole = $this->findBySelect($select)) || !$userrole->delete())
        {
            return false;
        }
        return true;
    }
}

