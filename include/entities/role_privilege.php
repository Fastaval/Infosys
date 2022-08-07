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
     * handles the role-privilege link of the rights system
     *
     * @package MVC
     * @subpackage Entities
     */
class RolePrivilege extends DBObject
{

    protected $tablename = 'roles_privileges';

    /**
     * returns array of privileges for a given role
     *
     * @param object $role - role object to fetch privileges for
     * @access public
     * @return array
     */
    public function getRolePrivileges($role)
    {
        if (!is_object($role) || !$role->isLoaded())
        {
            return array();
        }
        $rp = $this->createEntity('RolePrivilege');
        $select = $rp->getSelect();
        $select->setWhere('role_id','=',$role->id);
        $rolelinks = $rp->findBySelectMany($select);
        if (empty($rolelinks))
        {
            return array();
        }
        $ids = array();
        foreach ($rolelinks as $link)
        {
            $ids[] = $link->privilege_id;
        }
        $select = $this->createEntity('Privilege')->getSelect();
        $select->setWhere('id', 'in', $ids);
        return $this->createEntity('Privilege')->findBySelectMany($select);
    }
}

