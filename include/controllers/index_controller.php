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
     * PHP version 5.3+
     *
     * @package    Infosys
     * @subpackage Controllers 
     * @author     Peter Lind <peter.e.lind@gmail.com>
     * @copyright  2009-2012 Peter Lind
     * @license    http://www.gnu.org/licenses/gpl.html GPL 3
     * @link       http://www.github.com/Fake51/Infosys
     */

    /**
     * default controller
     *
     * @package    Infosys
     * @subpackage Controllers 
     * @author     Peter Lind <peter.e.lind@gmail.com>
     */
class IndexController extends Controller
{
    protected $prerun_hooks = array(
        array(
         'method'     => 'checkUser',
         'exclusive'  => true,
         'methodlist' => array(
                          'login',
                          'noAccess',
                          'sendAutomaticMessages',
                          'forgottenPassDialog',
                          'forgottenPassAction',
                          'resetPassDialog',
                          'resetPassAction',
                         ),
        ),
    );

    /**
     * executes the sms script
     *
     * @access public
     * @return void
     */
    public function sendAutomaticMessages()
    {
        $this->model->sendAutomaticMessages();
        exit;
    }

    /**
     * Default method of the class
     *
     * @access public
     * @return void
     */
    public function main() {
        if (!empty($this->page->request->get->wildcardsearch)) {
            $participant_model = $this->model->factory('Participant');
            if (
                ($id = $this->page->request->get->wildcardsearch)
                && intval($id)
                && ($participant = $participant_model->findParticipant($id))
            ) {
                $querystring = !empty($this->page->request->get->payment_edit) ? '?payment_edit=true' : '';
                $this->hardRedirect($this->url('visdeltager', array('id' => intval($id))) . $querystring);
            }

            $session = $this->dic->get('Session');

            $session->search = array('wildcardsearch' => $this->page->request->get->wildcardsearch);
            $this->hardRedirect($this->url('show_search_result'));
        }

        $this->page->setTitle('Oversigt');
        $this->page->participant_data = $this->model->generateParticipantStats();
        $this->page->wear_data        = $this->model->generateWearStats();
        $this->page->food_data        = $this->model->generateFoodStats();
        $this->page->entrance_data    = $this->model->generateEntranceStats();
    }

    /**
     * handles users trying to log into the system
     *
     * @access public
     * @return void
     */
    public function login() {
        $this->page->setTitle('Login');

        if (!$this->page->request->isPost()) {
            return;
        }

        $post = $this->page->request->post;

        if (empty($post->user) || empty($post->pass)) {
            return;
        }

        if ($this->model->handleLogin($post->user, $post->pass)) {
            if ($post->user == 'mad') {
                $this->hardRedirect($this->url('food_handout'));
            }

            $this->hardRedirect($this->url('home'));
        } else {
            $this->errorMessage('Brugernavn eller kode var forkert. Prøv igen');
        }
    }

    /**
     * logs a user off the system
     *
     * @access public
     * @return void
     */
    public function logout()
    {
        $this->model->logout();
        $this->successMessage('Du er nu logget af');
        $this->page->setTemplate('login');
    }

    /**
     * shows a no access warning
     *
     * @access public
     * @return void
     */
    public function noAccess()
    {
        $this->page->setStatus(403, 'No access');
    }

    /**
     * shows a dialog for resetting password - step 1
     *
     * @access public
     * @return void
     */
    public function forgottenPassDialog()
    {
    }

    /**
     * checks the submission from the forgotten pass dialog
     *
     * @access public
     * @return void
     */
    public function forgottenPassAction()
    {
        $done = false;

        if (!$this->page->request->isPost()) {
            $location = $this->url('forgotten_pass');
            $this->errorMessage('No data sent');

        } else {
            if ($this->model->sendPasswordResetEmail($this->page->request->post->user, $this->page)) {
                $location = $this->url('login_page');
                $this->successMessage('Email with instructions sent to the address provided');

            } else {
                $location = $this->url('forgotten_pass');
                $this->errorMessage('Could not send email to provided address');

            }

        }

        $this->page->setStatus(303, 'See elsewhere')
            ->setHeader('Location', $location)
            ->setTemplate('');
    }

    /**
     * shows a dialog for resetting password - step 2
     *
     * @access public
     * @return void
     */
    public function resetPassDialog()
    {
        if (empty($this->vars['hash']) || !($user = $this->model->getUserForPasswordReset($this->vars['hash']))) {
            $location = $this->url('login_page');
            $this->errorMessage('User not found for password reset');

            $this->page->setTemplate('')
                ->setStatus(303, 'No such user')
                ->setHeader('Location', $location);

            return;

        }

        $this->page->hash = $this->vars['hash'];

        $user->password_reset_time = date('Y-m-d H:i:s', time() - 15 * 20);

        $this->page->user = $user;
    }

    /**
     * resets a users password
     *
     * @access public
     * @return void
     */
    public function resetPassAction()
    {
        $done = false;

        if (empty($this->vars['hash']) || !($user = $this->model->getUserForPasswordReset($this->vars['hash']))) {
            $location = $this->url('login_page');
            $this->errorMessage('User not found for password reset');

            $done = true;

        }

        if (!$done && !($this->page->request->isPost() && !empty($this->page->request->post->pass))) {
            $location = $this->url('reset_pass', ['hash' => $user->password_reset_hash]);
            $this->errorMessage('No data received');

            $done = true;

        }

        if (!$done && $this->page->request->post->pass !== $this->page->request->post->check) {
            $location = $this->url('reset_pass', ['hash' => $user->password_reset_hash]);
            $this->errorMessage('Passwords do not match');

            $done = true;

        }

        if (!$done && mb_strlen($this->page->request->post->pass) < 8) {
            $location = $this->url('reset_pass', ['hash' => $user->password_reset_hash]);
            $this->errorMessage('Passwords too short. Min length is 8 characters.');

            $done = true;

        }

        if (!$done) {
            $user->pass = password_hash($this->page->request->post->pass, PASSWORD_DEFAULT);
            $user->password_reset_time = '0000-00-00 00:00:00';
            $user->update();

            $location = $this->url('login_page');
            $this->successMessage('Password reset');

        }

        $this->page->setTemplate('')
            ->setStatus(303, 'See elsewhere')
            ->setHeader('Location', $location);

        return;
    }
}
