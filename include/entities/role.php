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
     * handles the roles part of the access system
     *
     * @package MVC
     * @subpackage Entities
     */
class Role extends DBObject
{

    protected $tablename = 'roles';

    /**
     * tests if a role has the privileges needed to access a given controller->method
     *
     * @param string $controller - controller to check against
     * @param string $method - controller->method to check against
     * @access public
     * @return bool
     */
    public function canAccessControllermethod($controller, $method)
    {
        if (!$this->isLoaded())
        {
            return false;
        }

        $privs = $this->createEntity('RolePrivilege')->getRolePrivileges($this);
        if (!empty($privs))
        {
            foreach ($privs as $priv)
            {
                if ($priv->canAccessControllermethod($controller, $method))
                {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * returns all privileges for the role
     *
     * @access public
     * @return array
     */
    public function getPrivileges()
    {
        if (!$this->isLoaded())
        {
            return array();
        }
        $select = $this->createEntity('RolePrivilege')->getSelect();
        $select->setWhere('role_id','=',$this->id);
        if (!($roleprivs = $this->createEntity('RolePrivilege')->findBySelectMany($select)))
        {
            return array();
        }
        $ids = array();
        foreach ($roleprivs as $rp)
        {
            $ids[] = $rp->privilege_id;
        }
        $select = $this->createEntity('Privilege')->getSelect();
        $select->setWhere('id','in',$ids);
        return $this->createEntity('Privilege')->findBySelectMany($select);
    }
}
