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
     * @subpackage Controllers 
     * @author     Peter Lind <peter.e.lind@gmail.com>
     * @copyright  2009 Peter Lind
     * @license    http://www.gnu.org/licenses/gpl.html GPL 3
     * @link       http://www.github.com/Fake51/Infosys
     */


    /**
     * controller handling game groups
     *
     * @package    MVC
     * @subpackage Controllers 
     * @author     Peter Lind <peter.e.lind@gmail.com>
     */
class GroupsController extends Controller
{
    protected $prerun_hooks = array(
                                array('method' => 'checkUser','exclusive' => true),
                               );

    /**
     * Default method of the class
     *
     * @access public
     * @return void
     */
    public function main()
    {
    }

    /**
     * displays all created groups
     *
     * @access public
     * @return void
     */
    public function visAlle()
    {
        $sort = array();
        if ($this->page->request->isPost())
        {
            $post = $this->page->request->post;
            $sort_order = ((!empty($post->sort_order)) ? $post->sort_order : array());
            $sort_direction = ((!empty($post->sort_direction)) ? $post->sort_direction : array());
            $i = 0;
            $sort = array();
            foreach ($sort_order as $key)
            {
                $sort[$key] = $sort_direction[$i];
                $i++;
            }
        }
        $hold = $this->model->findAll($sort);
        if (empty($hold))
        {
            $this->page->setTemplate('noResults');
        }
        else
        {
            $this->page->sort_vars = $sort;
            $this->page->hold = $hold;
        }
    }

    /**
     * displays details for an individual group
     *
     * @access public
     * @return void
     */
    public function visHold()
    {
        if (empty($this->vars['id']) || !($hold = $this->model->findEntity('Hold', $this->vars['id']))) {
            $this->page->setTemplate('noResults');

        } else {
            $this->page->hold = $hold;
            $this->page->karma_stats = $this->model->getKarmaStatsForGroup($hold);
            $this->page->setTemplate('showGroup');
        }
    }


    /**
     * creates a new group
     *
     * @access public
     * @return void
     */
    public function createGroup()
    {
        $user = $this->model->getLoggedInUser();
        if (!($user->hasRole('Activity-admin') || $user->hasRole('Infonaut') || $user->hasRole('admin'))) {
            $this->hardRedirect($this->url('no_access'));
        }

        if (!$this->page->request->isPost())
        {
            $this->page->aktiviteter = $this->model->getAllActivities();
        }
        else
        {
            if ($hold = $this->model->create($this->page->request->post))
            {
                $this->successMessage('Holdet blev oprettet.');
                $this->log("Hold #{$hold->id} blev oprettet af {$this->model->getLoggedInUser()->user}", 'Hold', $this->model->getLoggedInUser());
                $this->hardRedirect($this->url('vis_hold', array('id' => $hold->id)));
            }
            else
            {
                $this->errorMessage('Holdet kunne ikke oprettes.');
                $this->hardRedirect($this->url('holdhome'));
            }
        }
    }


    /**
     * edits a group
     *
     * @access public
     * @return void
     */
    public function edit()
    {
        $user = $this->model->getLoggedInUser();
        if (!($user->hasRole('Activity-admin') || $user->hasRole('Infonaut') || $user->hasRole('admin'))) {
            $this->hardRedirect($this->url('no_access'));
        }

        if (empty($this->vars['id']) || !($hold = $this->model->findEntity('Hold', $this->vars['id']))) {
            $this->page->setTemplate('noResults');
        } else {
            if (!$this->page->request->isPost()) {
                $this->page->hold = $hold;
                $this->page->setTemplate('editHold');
                $this->page->rooms = $this->model->getAllAvailableRooms($hold->getAfvikling());
            } elseif (!empty($this->page->request->post->hold_edit)) {
                (($result = $this->model->edit($hold, $this->page->request->post)) ? $this->successMessage('Holdet blev opdateret.') : $this->errorMessage('Holdet kunne ikke opdateres.'));
                if ($result) {
                    $this->log("Hold #{$hold->id} blev opdateret af {$this->model->getLoggedInUser()->user}", 'Hold', $this->model->getLoggedInUser());
                }

                $this->hardRedirect($this->url('vis_hold', array('id' => $hold->id)));
            } elseif (!empty($this->page->request->post->hold_slet)) {
                $this->page->hold = $hold;
                $this->page->setTemplate('confirmDelete');
            } else {
                $this->hardRedirect($this->url('holdhome'));
            }
        }
    }

    /**
     * deletes a group and returns success message
     *
     * @access public
     * @return void
     */
    public function deleteGroup()
    {
        $user = $this->model->getLoggedInUser();
        if (!($user->hasRole('Activity-admin') || $user->hasRole('Infonaut') || $user->hasRole('admin'))) {
            $this->hardRedirect($this->url('no_access'));
        }

        if (empty($this->vars['id']) || !($hold = $this->model->findEntity('Hold', $this->vars['id'])))
        {
            $this->page->setTemplate('noResults');
        }
        if ($this->page->request->isPost() && !empty($this->page->request->post->delete_hold))
        {
            (($this->model->deleteHold($hold)) ? $this->successMessage('Holdet blev slettet.') : $this->errorMessage('Holdet kunne ikke slettes.'));
            $this->log("Hold #{$this->vars['id']} blev slettet af {$this->model->getLoggedInUser()->user}", 'Hold', $this->model->getLoggedInUser());
        }
        $this->hardRedirect($this->url('vis_alle_hold'));
    }

    //{{{ ajax methods

    /**
     * adds/removes a participant from groups
     *
     * @access public
     * @return void
     */
    public function ajaxScheduleParticipant()
    {
        $user = $this->model->getLoggedInUser();

        if (!($user->hasRole('Activity-admin') || $user->hasRole('Infonaut') || $user->hasRole('admin'))) {
            header('HTTP/1.1 403 Fail');
            echo json_encode(array('status' => 'fail', 'reason' => 'No access'));
            exit;
        }

        $this->ajaxHeader();

        if (!$this->page->request->isPost()) {
            header('HTTP/1.1 500 Fail');
            echo json_encode(array('status' => 'fail', 'reason' => 'Use post'));
            exit;
        }

        try {
            if (!$this->model->handleParticipantAdministration($this->page->request->post)) {
                throw new FrameworkException('bleh');
            }

        } catch (Exception $e) {
            header('HTTP/1.1 500 Fail');
            echo json_encode(array('status' => 'fail', 'reason' => 'Failed to add/remove participant'));
            exit;
        }

        header('HTTP/1.1 200 Success');
        header('Content-Type: application/json');
        echo json_encode($this->model->getAjaxStats($this->page->request->post));
        exit;
    }

    /**
     * tries to create a group for a scheduling
     *
     * @access public
     * @return void
     */
    public function ajaxCreateGroup()
    {
        $user = $this->model->getLoggedInUser();
        if (!($user->hasRole('Activity-admin') || $user->hasRole('Infonaut') || $user->hasRole('admin'))) {
            header('HTTP/1.1 403 Fail');
            echo json_encode(array('status' => 'fail', 'reason' => 'No access'));
            exit;
        }

        if (!empty($this->vars['afvikling_id']) && ($afvikling = $this->model->findentity('Afviklinger', $this->vars['afvikling_id']))) {
            if ($room = $this->model->getRandomAvailableRoom($afvikling)) {
                if ($hold = $this->model->createGroupForSchedule($afvikling, $room)) {
                    $this->log("Hold #{$hold->id} blev oprettet af {$this->model->getLoggedInUser()->user}", 'Hold', $this->model->getLoggedInUser());

                    header('HTTP/1.1 200 Done');
                    header('Content-Type: application/json');

                    echo json_encode(
                        array(
                            'status'            => 'work',
                            'id'                => $hold->id,
                            'holdnummer'        => $hold->holdnummer,
                            'needs_gamemasters' => $hold->needsGMs(),
                            'needs_gamers'      => $hold->needsGamers(),
                            'can_use_gamers'    => $hold->canUseGamers(),
                        )
                    );
                    exit;

                } else {
                    $message = 'Failed to create group';
                }

            } else {
                $message = 'No available room';
            }

        } else {
            $message = 'Could not find schedule';
        }

        header('HTTP/1.1 500 Done');
        header('Content-Type: application/json');
        echo json_encode(array('status' => "fail", 'message' => $message));
        exit;
    }

    /**
     * tries to delete a group
     *
     * @access public
     * @return void
     */
    public function ajaxDeleteGroup()
    {
        $user = $this->model->getLoggedInUser();
        if (!($user->hasRole('Activity-admin') || $user->hasRole('Infonaut') || $user->hasRole('admin'))) {
            header('HTTP/1.1 403 Fail');
            echo json_encode(array('status' => 'fail', 'reason' => 'No access'));
            exit;
        }

        if (!empty($this->vars['id']) && ($hold = $this->model->findEntity('Hold', $this->vars['id']))) {
            $participants = $hold->getDeltagere();

            if ($this->model->deleteHold($hold, $this->page->request->get->override)) {

                $this->log("Hold #{$this->vars['id']} blev slettet af {$this->model->getLoggedInUser()->user}", 'Hold', $this->model->getLoggedInUser());

                header('HTTP/1.1 200 Done');
                header('Content-Type: application/json');
                echo json_encode(array('status' => 'work', 'id' => $this->vars['id']));
                exit;
            }
        }

        header('HTTP/1.1 500 Fail');
        header('Content-Type: application/json');
        echo json_encode(array('status' => "fail"));
        exit;
    }
    //}}}

}
