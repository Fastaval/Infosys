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
 * @package   Controllers 
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2009-2012 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

/**
 * handles users, roles and such
 *
 * @category Infosys
 * @package  Controllers 
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class AdminController extends Controller
{
    protected $prerun_hooks = array(
        array('method' => 'checkUser', 'exclusive' => true),
    );

    /**
     * displays main options for admin
     *
     * @access public
     * @return void
     */
    public function main()
    {
    }

    /**
     * displays all users and a form for creating new ones
     * also displays ajax interface for handling users
     *
     * @access public
     * @return void
     */
    public function handleUsers()
    {
        $this->page->users      = $this->model->getAllUsers();
        $this->page->all_roles  = $this->model->getAllRoles();
        $this->page->model      = $this->model;
    }

    /**
     * displays all roles and a form for creating new ones
     * also displays ajax interface for handling roles
     *
     * @access public
     * @return void
     */
    public function handleRoles()
    {
        $this->page->roles      = $this->model->getAllRoles();
        $this->page->privileges = $this->model->getAllPrivileges();
    }

    /**
     * displays all privileges and a form for creating new ones
     * also displays ajax interface for handling privileges
     *
     * @access public
     * @return void
     */
    public function handlePrivileges()
    {
        $this->page->privileges = $this->model->getAllPrivileges();
    }

    /**
     * shows a form to reset signups
     *
     * @access public
     * @return void
     */
    public function showConfirmReset()
    {
    }

    /**
     * resets signups
     *
     * @access public
     * @return void
     */
    public function resetSignups()
    {
        if (!$this->page->request->isPost() || empty($this->page->request->post->confirmReset)) {
            $this->errorMessage('Not resetting as you did not confirm');

        } else {
            $this->model->resetSignups();
            $this->successMessage('Signups reset');
        }

        $this->hardRedirect($this->url('index'));
    }

    //{{{ ajax functions be here

    /**
     * ajax function, updates password for a user
     *
     * @access public
     * @return void
     */
    public function ajaxChangePass()
    {
        $this->ajaxHeader();
        if (empty($this->vars['id']) || !($user = $this->model->findEntity('User', $this->vars['id'])) || !$this->page->request->isPost() || empty($this->page->request->post->pass)) {
            echo "validation failed";
            exit;
        }

        $user->pass = md5($this->page->request->post->pass);
        if ($user->update()) {
            $this->log("User #{$user->id} ({$user->user}) fik skiftet password af {$this->model->getLoggedInUser()->user}", 'Users', $this->model->getLoggedInUser());
            echo "worked";
        } else {
            echo "update failed";
        }

        exit;
    }

    /**
     * ajax function, removes a role from a user
     *
     * @access public
     * @return void
     */
    public function ajaxRemoveRole()
    {
        $this->ajaxHeader();
        if (empty($this->vars['id']) || empty($this->vars['role_id']) || !($user = $this->model->findEntity('User', $this->vars['id'])) || !($role = $this->model->findEntity('Role', $this->vars['role_id']))) {
            echo "failed - vars";
            exit;
        }

        if ($user->removeRole($role)) {
            $this->log("User #{$user->id} ({$user->user}) fik frataget sin {$role->name}-rolle af {$this->model->getLoggedInUser()->user}", 'Users', $this->model->getLoggedInUser());
            echo "worked";
        } else {
            echo "failed - action";
        }

        exit;
    }

    /**
     * ajax function, adds a role to a user
     *
     * @access public
     * @return void
     */
    public function ajaxAddRole()
    {
        $this->ajaxHeader();
        if (empty($this->vars['id']) || empty($this->vars['role_id']) || !($user = $this->model->findEntity('User', $this->vars['id'])) || !($role = $this->model->findEntity('Role', $this->vars['role_id']))) {
            echo "failed - vars";
            exit;
        }

        if ($user->addRole($role)) {
            $this->log("User #{$user->id} ({$user->user}) fik tilføjet {$role->name}-rollen af {$this->model->getLoggedInUser()->user}", 'Users', $this->model->getLoggedInUser());
            echo "worked";
        } else {
            echo "failed - action";
        }

        exit;
    }

    /**
     * ajax function, disables a user
     *
     * @access public
     * @return void
     */
    public function ajaxDisableUser()
    {
        $this->ajaxHeader();
        if (empty($this->vars['id']) || !($user = $this->model->findEntity('User', $this->vars['id']))) {
            echo "failed";
            exit;
        }

        if ($user->disable()) {
            $this->log("User #{$user->id} ({$user->user}) blev disabled af {$this->model->getLoggedInUser()->user}", 'Users', $this->model->getLoggedInUser());
            echo "worked";
        } else {
            echo "failed";
        }

        exit;
    }

    /**
     * ajax function, enables a user
     *
     * @access public
     * @return void
     */
    public function ajaxEnableUser()
    {
        $this->ajaxHeader();
        if (empty($this->vars['id']) || !($user = $this->model->findEntity('User', $this->vars['id']))) {
            echo "failed";
            exit;
        }

        if ($user->enable()) {
            $this->log("User #{$user->id} ({$user->user}) blev enabled af {$this->model->getLoggedInUser()->user}", 'Users', $this->model->getLoggedInUser());
            echo "worked";
        } else {
            echo "failed";
        }

        exit;
    }

    /**
     * ajax function, deletes a user
     *
     * @access public
     * @return void
     */
    public function ajaxDeleteUser()
    {
        $this->ajaxHeader();
        if (empty($this->vars['id']) || !($user = $this->model->findEntity('User', $this->vars['id']))) {
            echo "failed";
            exit;
        }

        foreach ($user->getRoles() as $role) {
            $user->removeRole($role);
        }

        $name = $user->user;
        $id = $user->id;
        if ($user->delete()) {
            $this->log("User #{$id} ({$name}) blev slettet af {$this->model->getLoggedInUser()->user}", 'Users', $this->model->getLoggedInUser());
            echo "worked";
        } else {
            echo "failed";
        }

        exit;

    }

    /**
     * ajax function, creates a user
     *
     * @access public
     * @return void
     */
    public function ajaxCreateUser()
    {
        $this->ajaxHeader();
        if (!$this->page->request->isPost() || empty($this->page->request->post->user) || empty($this->page->request->post->pass)) {
            echo "failed";
            exit;
        }

        $post = $this->page->request->post;
        if ($user = $this->model->createUser($post)) {
            $this->log("User #{$user->id} ({$user->user}) blev oprettet af {$this->model->getLoggedInUser()->user}", 'Users', $this->model->getLoggedInUser());
            if (!empty($post->role) && ($role = $this->model->findEntity('Role', $post->role))) {
                if ($user->addRole($role)) {
                    $this->log("User #{$user->id} ({$user->user}) fik tilføjet {$role->name}-rollen af {$this->model->getLoggedInUser()->user}", 'Users', $this->model->getLoggedInUser());
                } else {
                    $this->log("User #{$user->id} ({$user->user}) blev slettet af {$this->model->getLoggedInUser()->user}", 'Users', $this->model->getLoggedInUser());
                    $user->delete();

                    header('HTTP 500 Failed');
                    exit;
                }
            }
            echo json_encode(array('user' => $user->user, 'id' => $user->id));
        } else {
            header('HTTP 500 Failed');
        }

        exit;
    }

    /**
     * creates a privilege
     *
     * @access public
     * @return void
     */
    public function ajaxCreatePrivilege()
    {
        $this->ajaxHeader();
        if (!$this->page->request->isPost()) {
            echo 'failed';
            exit;
        }

        $post = $this->page->request->post;
        if (empty($post->controller) || empty($post->method)) {
            echo 'failed';
            exit;
        }

        if ($priv = $this->model->createPrivilege($post)) {
            $this->log("Privilege #{$priv->id} blev oprettet af {$this->model->getLoggedInUser()->user}", 'Privilege', $this->model->getLoggedInUser());
            echo json_encode(array('id' => $priv->id));
        } else {
            echo "failed";
        }

        exit;
    }

    /**
     * deletes a privilege
     *
     * @access public
     * @return void
     */
    public function ajaxDeletePrivilege()
    {
        $this->ajaxHeader();
        if (empty($this->vars['id']) || !($priv = $this->model->findEntity('Privilege', $this->vars['id']))) {
            echo "failed";
            exit;
        }

        $id = $priv->id;
        if ($priv->delete()) {
            $this->log("Privilege #{$id} blev slettet af {$this->model->getLoggedInUser()->user}", 'Privilege', $this->model->getLoggedInUser());
            echo "worked";
        } else {
            echo "failed";
        }

        exit;
    }

    /**
     * deletes a privilege
     *
     * @access public
     * @return void
     */
    public function ajaxDeleteRole()
    {
        $this->ajaxHeader();
        if (empty($this->vars['id']) || !($role = $this->model->findEntity('Role', $this->vars['id']))) {
            echo "failed";
            exit;
        }

        $id = $role->id;
        $name = $role->name;
        if ($role->delete()) {
            $this->log("Role #{$role->id} ({$name}) blev slettet af {$this->model->getLoggedInUser()->user}", 'Roles', $this->model->getLoggedInUser());
            echo "worked";
        } else {
            echo "failed";
        }

        exit;
    }

    /**
     * creates a role
     *
     * @access public
     * @return void
     */
    public function ajaxCreateRole()
    {
        $this->ajaxHeader();
        if (!$this->page->request->isPost()) {
            echo "failed";
            exit;
        }

        try {
            if (!($role = $this->model->createRole($this->page->request->post))) {
                throw new FrameworkException('Could not create role');
            }

            $obj     = new StdClass();
            $obj->id = $role->id;

            $this->log("Role #{$role->id} ({$role->name}) blev oprettet af {$this->model->getLoggedInUser()->user}", 'Roles', $this->model->getLoggedInUser());
            echo json_encode($obj);
        } catch (Exception $e) {
            echo "failed - " . $e->getMessage();
        }

        exit;
    }

    /**
     * adds a privilege to a role
     *
     * @access public
     * @return void
     */
    public function ajaxAddPrivilege()
    {
        $this->ajaxHeader();
        if (empty($this->vars['role_id']) || empty($this->vars['privilege_id'])) {
            echo "failed";
            exit;
        }

        try {
            list($role, $privilege) = $this->model->addPrivilege($this->vars['role_id'], $this->vars['privilege_id']);

            $this->log("Role #{$role->id} ({$role->name}) fik tilføjet privilegiet (" . $this->db->sanitize($privilege->controller) . ':' . $this->db->sanitize($privilege->method) . ") af {$this->model->getLoggedInUser()->user}", 'Roles', $this->model->getLoggedInUser());
            echo "worked";
        } catch (Exception $e) {
            echo "failed";
        }

        exit;
    }

    /**
     * removes a privilege from a role
     *
     * @access public
     * @return void
     */
    public function ajaxRemovePrivilege()
    {
        $this->ajaxHeader();
        if (empty($this->vars['role_id']) || empty($this->vars['privilege_id'])) {
            echo "failed";
            exit;
        }

        try {
            list($role, $privilege) = $this->model->removePrivilege($this->vars['role_id'], $this->vars['privilege_id']);

            $this->log("Role #{$role->id} ({$role->name}) fik fjernet privilegiet (" . $this->db->sanitize($privilege->controller) . ':' . $this->db->sanitize($privilege->method) . ") af {$this->model->getLoggedInUser()->user}", 'Roles', $this->model->getLoggedInUser());
            echo "worked";
        } catch (Exception $e) {
            echo "failed";
        }

        exit;
    }

    //}}}
}
