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
     * handles all data fetching for the groups controller
     *
     * @package    MVC
     * @subpackage Models
     * @author     Peter Lind <peter.e.lind@gmail.com>
     */

class GroupsModel extends Model
{

    /**
     * returns all groups
     *
     * @param array $sort_vars - array of column-name => asc|desc pairs for sorting
     * @access public
     * @return bool|array
     */
    public function findAll($sort_vars = null)
    {
        if (!$sort_vars) {
            return $this->createEntity('Hold')->findAll();
        }

        $i      = 0;
        $array  = array();
        $select = $this->createEntity('Hold')->getSelect();
        foreach ($sort_vars as $key => $val) {
            switch ($key) {
                default:
                    $select->setOrder('hold.' .$key, $val);
                    break;
            }

            $i++;
        }

        $select->setField('hold.*', false);
        return $this->createEntity('Hold')->findBySelectMany($select);
    }
    /**
     * creates a Hold entity and fills it with POSTed data
     *
     * @param RequestVars $post
     *
     * @access public
     * @return bool|object - false on fail or the created lokaler object
     */
    public function create(RequestVars $post)
    {
        if (empty($post->afvikling_id)) {
            return false;
        } else {
            $select = $this->createEntity('Hold')->getSelect();
            $select->setWhere('afvikling_id','=',$post->afvikling_id);
            if ($result = $this->createEntity('Hold')->findBySelectMany($select)) {
                $i = 1;
                $got_number = false;
                foreach ($result as $hold) {
                    if ($hold->holdnummer != $i) {
                        $got_number = true;
                        break;
                    }

                    $i++;
                }

                $post->holdnummer = (($got_number) ? $i : $i+1);
            } else {
                $post->holdnummer = 1;
            }

            $hold = $this->createEntity('Hold');
            foreach ($post->getRequestVarArray() as $key => $val) {
                $hold->$key = $val;
            }

            $hold->holdnummer = $post->holdnummer;
        }

        return (($hold->insert()) ? $hold : false);
    }

    /**
     * updates a group
     *
     * @param object $hold - Hold entity
     * @param RequestVars $post - POST vars
     *
     * @access public
     * @return bool
     */
    public function edit($hold, RequestVars $post)
    {
        if (!is_object($hold) || !$hold->isLoaded()) {
            return false;
        }

        $hold->lokale_id = $post->lokale_id;
        return $hold->update();
    }

    /**
     * deletes a group
     *
     * @param object $hold     Hold entity
     * @param bool   $override True to remove all participants first
     *
     * @access public
     * @return bool
     */
    public function deleteHold($hold, $override = false)
    {
        if (!is_object($hold) || !$hold->isLoaded()) {
            return false;
        }

        if ($override) {
            foreach ($hold->getPladser() as $spot) {
                $spot->delete();
            }
        }

        try {
            return $hold->delete();
        } catch (Exception $e) {
            return false;
        }
    }


    /**
     * returns all activities
     *
     * @access public
     * @return array
     */
    public function getAllActivities()
    {
        return (($return = $this->createEntity('Aktiviteter')->findAll()) ? $return : array());
    }

    /**
     * searches for available rooms for a given time
     *
     * @param object $afvikling - Afviklinger entity
     *
     * @access public
     * @return object|bool
     */
    public function getRandomAvailableRoom($afvikling)
    {
        if (!is_object($afvikling) || !$afvikling->isLoaded()) {
            return false;
        }

        $room = false;
        if ($rooms = $this->createEntity('Lokaler')->findAll()) {
            $lok_eks = (($afvikling->getAktivitet()->lokale_eksklusiv == 'ja') ? true : false);
            foreach ($rooms as $r) {
                if (!$r->isOccupiedBetween($afvikling->start, $afvikling->slut, $lok_eks)) {
                    $room = $r;
                    break;
                }
            }
        }
        return $room;
    }

    /**
     * returns array of available rooms at the time of the schedule
     *
     * @param Afviklinger $schedule Schedule providing start and end time
     *
     * @access public
     * @return array
     */
    public function getAllAvailableRooms(Afviklinger $schedule)
    {
        $rooms = array();
        if ($rooms = $this->createEntity('Lokaler')->findAll()) {
            $lok_eks = (($schedule->getAktivitet()->lokale_eksklusiv == 'ja') ? true : false);
            foreach ($rooms as $id => $r) {
                if ($r->isOccupiedBetween($schedule->start, $schedule->slut, $lok_eks)) {
                    unset($rooms[$id]);
                }
            }
        }

        return $rooms;
    }

    /**
     * creates a group for a given schedule and places it in a given room
     *
     * @param object $afvikling - Afviklinger entity
     * @param object $room - Lokaler entity
     * @access public
     * @return bool
     */
    public function createGroupForSchedule($afvikling, $room)
    {
        if (!is_object($afvikling) || !$afvikling->isLoaded() ||!is_object($room) || !$room->isLoaded() || $room->isOccupiedBetween($afvikling->start, $afvikling->slut, (($afvikling->getAktivitet()->lokale_eksklusiv == 'ja') ? true : false))) {
            return false;
        }

        $hold               = $this->createEntity('Hold');
        $hold->holdnummer   = $hold->getNextHoldnummer($afvikling);
        $hold->lokale_id    = $room->id;
        $hold->afvikling_id = $afvikling->id;
        return $hold->insert();
    }

    /**
     * adds a participant to a group
     *
     * @param object $hold     - Hold entity
     * @param object $deltager - Deltagere entity
     * @param string $type     - spiller|spilleder
     *
     * @access public
     * @return bool
     */
    public function addParticipantToGroup(Hold $hold, $deltager, $type)
    {
        return $this->createEntity('Pladser')->setDeltagerPlads($deltager, $hold, $type);
    }

    /**
     * removes a participant from a group
     *
     * @param object $hold     - Hold entity
     * @param object $deltager - Deltagere entity
     *
     * @access public
     * @return bool
     */
    public function removeParticipantRemoveFromGroup(Hold $hold, $deltager)
    {
        if (!is_object($hold) || !$hold->isLoaded() || !is_object($deltager) || !$deltager->isLoaded()) {
            return false;
        }

        $plads  = $this->createEntity('Pladser');
        $select = $plads->getSelect();
        $select->setWhere('deltager_id','=',$deltager->id);
        $select->setWhere('hold_id','=',$hold->id);
        if (!$plads->findBySelect($select)) {
            return false;
        }

        return $plads->delete();
    }

    /**
     * handles administration of a participant, either
     * adding or removing him/her to groups
     *
     * @param RequestVars $post Post vars
     *
     * @throws Exception
     * @access public
     * @return void
     */
    public function handleParticipantAdministration(RequestVars $post)
    {
        if (!($participant = $this->createEntity('Deltagere')->findById($post->participant_id))) {
            throw new FrameworkException('No such exception');
        }

        if (!empty($post->from_group)) {
            $allocation = $this->createEntity('Pladser');
            if ($allocation = $allocation->findBySelect($allocation->getSelect()->setWhere('deltager_id', '=', $participant->id)->setWhere('hold_id', '=', $post->from_group))) {
                $allocation->delete();
                $this->log("Deltager #{$participant->id} blev fjernet fra hold #{$post->from_group} af {$this->getLoggedInUser()->user}", 'Hold', $this->getLoggedInUser());
            }
        }

        if (!empty($post->to_group) && isset($post->gamer)) {
            if (!($group = $this->createEntity('Hold')->findById($post->to_group))) {
                throw new FrameworkException('Group to add to does not exist');
            }

            if (!$this->createEntity('Pladser')->setDeltagerPlads($participant, $group, $post->gamer == 'true' ? 'spiller' : 'spilleder')) {
                throw new FrameworkException('Could not add to group');
            }
            $this->log("Deltager #{$participant->id} blev sat på hold #{$group->id} af {$this->getLoggedInUser()->user}", 'Hold', $this->getLoggedInUser());
        }

        return true;
    }

    /**
     * fetches relevant statistics to send back
     *
     * @param RequestVars $post Post vars
     *
     * @access public
     * @return array
     */
    public function getAjaxStats(RequestVars $post)
    {
        $output = array('groups' => array());

        try {
            if ($participant = $this->createEntity('Deltagere')->findById($post->participant_id)) {
                $karma = $this->buildKarma();

                $output['participant'] = array(
                    'id'    => $participant->id,
                    'karma' => $karma->calculate($participant),
                );
            }

            if ($group = $this->createEntity('Hold')->findById($post->from_group)) {
                $output['groups'][$group->id] = array(
                    'needs_gamemasters' => !!$group->needsGMs(),
                    'can_use_gamers'    => !!$group->canUseGamers(),
                );

                $output['groups'][$group->id]['needs_gamers'] = !!$group->needsGamers();
            }

            if ($group = $this->createEntity('Hold')->findById($post->to_group)) {
                $output['groups'][$group->id] = array(
                    'needs_gamemasters' => !!$group->needsGMs(),
                    'can_use_gamers'    => !!$group->canUseGamers(),
                );

                $output['groups'][$group->id]['needs_gamers'] = !!$group->needsGamers();
            }

            return $output;

        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * returns stats on participants karma
     *
     * @access public
     * @return array
     */
    public function getKarmaStatsForGroup(Hold $group)
    {
        $karma = $this->buildKarma();

        $stats = $karma->calculate($group->getDeltagere());

        return $stats;
    }
}
