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
     * @subpackage Models
     * @author     Peter Lind <peter.e.lind@gmail.com>
     * @copyright  2009 Peter Lind
     * @license    http://www.gnu.org/licenses/gpl.html GPL 3
     * @link       http://www.github.com/Fake51/Infosys
     */

    /**
     * handles data for the admin MVC
     *
     * @package    MVC
     * @subpackage Models
     * @author     Peter Lind <peter.e.lind@gmail.com>
     */
class AdminModel extends Model
{

    /**
     * fetches all users
     *
     * @access public
     * @return array
     */
    public function getAllUsers()
    {
        return (($result = $this->createEntity('User')->findAll()) ? $result : array());
    }

    /**
     * fetches all roles in the system
     *
     * @access public
     * @return array
     */
    public function getAllRoles()
    {
        return (($result = $this->createEntity('Role')->findAll()) ? $result : array());
    }

    /**
     * fetches all privileges in the system
     *
     * @access public
     * @return array
     */
    public function getAllPrivileges()
    {
        return (($result = $this->createEntity('Privilege')->findAll()) ? $result : array());
    }

    /**
     * attempts to create a user entity
     *
     * @param RequestVars $post - POST vars
     *
     * @access public
     * @return bool|object
     */
    public function createUser(RequestVars $post)
    {
        if (empty($post->user) || empty($post->pass))
        {
            return false;
        }
        $user = $this->createEntity('User');
        $user->user = trim($post->user);
        $user->pass = md5(trim($post->pass));
        return (($user->insert()) ? $user : false);
    }


    /**
     * attempts to create a privilege entity
     *
     * @param RequestVars $post - POST vars
     *
     * @access public
     * @return bool|object
     * @todo make function check for class and method
     */
    public function createPrivilege(RequestVars $post)
    {
        if (empty($post->controller) || empty($post->method))
        {
            return false;
        }
        $priv = $this->createEntity('Privilege');
        $priv->controller = trim($post->controller);
        $priv->method = trim($post->method);
        return (($priv->insert()) ? $priv : false);
    }

    /**
     * adds a privilege to a role
     *
     * @param int $role_id      Id of role
     * @param int $privilege_id Id of privilege
     *
     * @access public
     * @return array
     */
    public function addPrivilege($role_id, $privilege_id)
    {
        if (!($role = $this->findEntity('Role', $role_id)) || !($privilege = $this->findEntity('Privilege', $privilege_id))) {
            throw new FrameworkException('Role or privilege does not exist');
        }

        $role_privilege               = $this->createEntity('RolePrivilege');
        $role_privilege->role_id      = $role->id;
        $role_privilege->privilege_id = $privilege->id;
        if (!$role_privilege->insert()) {
            throw new FrameworkException('Failed to insert role/privilege relationship');
        }

        return array($role, $privilege);
    }

    /**
     * removes a privilege from a role
     *
     * @param int $role_id      Id of role
     * @param int $privilege_id Id of privilege
     *
     * @access public
     * @return array
     */
    public function removePrivilege($role_id, $privilege_id)
    {
        if (!($role = $this->findEntity('Role', $role_id)) || !($privilege = $this->findEntity('Privilege', $privilege_id))) {
            throw new FrameworkException('Role or privilege does not exist');
        }

        $role_privilege = $this->createEntity('RolePrivilege');
        $role_privilege->findBySelect(
            $role_privilege->getSelect()
                ->setWhere('role_id', '=', $role->id)
                ->setWhere('privilege_id', '=', $privilege->id)
        );

        if (!$role_privilege || !$role_privilege->delete()) {
            throw new FrameworkException('Failed to find or delete role/privilege relationship');
        }

        return array($role, $privilege);
    }

    /**
     * attempts to create a role entity
     *
     * @param RequestVars $post POST vars
     *
     * @access public
     * @return bool|object
     */
    public function createRole(RequestVars $post)
    {
        if (empty($post->name) || empty($post->description)) {
            return false;
        }

        $role              = $this->createEntity('Role');
        $role->name        = trim($post->name);
        $role->description = trim($post->description);
        return (($role->insert()) ? $role : false);
    }

    /**
     * resets all signups
     *
     * @throws Exception
     * @access public
     * @return void
     */
    public function resetSignups()
    {
        $this->db->exec('DELETE FROM deltagere_gdstilmeldinger');
        $this->db->exec('DELETE FROM deltagere_gdsvagter');
        $this->db->exec('DELETE FROM deltagere_madtider');
        $this->db->exec('DELETE FROM deltagere_tilmeldinger');
        $this->db->exec('DELETE FROM deltagere_wear');
        $this->db->exec('DELETE FROM deltagere_indgang');
        $this->db->exec('DELETE FROM pladser');
        $this->db->exec('DELETE FROM participants_sleepingplaces');
        $this->db->exec('DELETE FROM deltagere');
        $this->db->exec('ALTER TABLE deltagere AUTO_INCREMENT = 1');
    }

    /**
     * returns config values
     *
     * @access public
     * @return array
     */
    public function getConfigValues()
    {
        return array_reduce(
            $this->db->query('SELECT id, name, configgroup, value FROM configuration'),
            function ($aggregate, $row) {
                $aggregate[$row['group']] = [
                    'id'    => $row['id'],
                    'name'  => $row['name'],
                    'value' => $row['value'],
                ];

                return $aggregate;
            },
            $this->getDefaultConfigValues()
        );
    }

    /**
     * returns detaulf config values
     *
     * @access public
     * @return array
     */
    public function getDefaultConfigValues()
    {
        return [
            'Convention' => [
                [
                    'id' => 0,
                    'name' => 'con.start',
                    'value' => ''
                ],
            ]
        ];
    }
}
