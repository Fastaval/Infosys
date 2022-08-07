<?php
/**
 * Copyright (C) 2013 Peter Lind
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
 * @copyright  2013 Peter Lind
 * @license    http://www.gnu.org/licenses/gpl.html GPL 3
 * @link       http://www.github.com/Fake51/Infosys
 */

/**
 * handles the gamestarts table
 *
 * @package MVC
 * @subpackage Entities
 */
class GamestartSchedule extends DBObject
{
    const OPEN   = 1;
    const CLOSED = 2;

    /**
     * Name of table
     *
     * @var string
     */
    protected $tablename = "gamestartschedules";

    protected $schedule;

    protected $activity;

    /**
     * tries to fetch a gamestart schedule given a
     * Schedule object
     *
     * @param DBObject $schedule Schedule to get object for
     *
     * @access public
     * @return GamestartSchedule|null
     */
    public function findByScheduleGamestart(DBObject $schedule, DBObject $gamestart)
    {
        $select = $this->getSelect()
            ->setWhere('schedule_id', '=', $schedule->id)
            ->setWhere('gamestart_id', '=', $gamestart->id);

        return $this->findBySelect($select);
    }

    /**
     * returns the schedule for the object
     * or null if not loaded
     *
     * @access public
     * @return null|Afviklinger
     */
    public function getSchedule()
    {
        if (isset($this->schedule)) {
            return $this->schedule;
        }

        if (!$this->isLoaded()) {
            return null;
        }

        return $this->schedule = $this->createEntity('Afviklinger')->findById($this->schedule_id);
    }

    /**
     * returns the activity for the object
     * or null if not loaded
     *
     * @access public
     * @return null|Aktiviteter
     */
    public function getActivity()
    {
        if (isset($this->activity)) {
            return $this->activity;
        }

        if (!$this->isLoaded()) {
            return null;
        }

        return $this->activity = $this->getSchedule()->getActivity();
    }

    /**
     * returns an array with the status of gamemasters
     * for the gamestart schedule
     *
     * @access public
     * @return array
     */
    public function getGMStatus()
    {
        if (!$this->isLoaded()) {
            return array();
        }

        if ($this->gm_status && ($status = json_decode($this->gm_status, true))) {
            return $status;
        }

        $status          = $this->createGMStatus();
        $this->gm_status = json_encode($status);
        $this->update();

        return $status;
    }

    /**
     * updates the gm_status property - validates
     * the update first
     *
     * @param array $gm_status Gamemaster status for update
     *
     * @throws FrameworkException
     * @access public
     * @return $this
     */
    public function updateGMStatus(array $gm_status)
    {
        foreach ($gm_status as $row) {
            if (!isset($row['gamemaster_id']) || !isset($row['state'])) {
                throw new FrameworkException('Data for updateGMStatus is malformed');
            }
        }

        $this->gm_status = json_encode($gm_status);
        $this->update();

        return $this;
    }

    /**
     * fetches all gamemasters for the schedule
     * and creates an array with the info
     *
     * @access protected
     * @return array
     */
    protected function createGMStatus()
    {
        $status = array();

        foreach ($this->getSchedule()->getAssignedGMs() as $group) {
            foreach ($group as $spot) {
                $status[$spot->deltager_id] = array(
                    'gamemaster_id' => $spot->deltager_id,
                    'state'         => 0,
                );
            }
        }

        return $status;
    }

    /**
     * method docblock
     *
     * @param
     *
     * @access public
     * @return void
     */
    public function getGMsPresent()
    {
        $count = 0;

        foreach ($this->getGMStatus() as $gm) {
            if ($gm['state'] == 1) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * returns all gamestart schedules for the gamestart
     *
     * @param DBObject $gamestart Gamestart to get schedules for
     *
     * @access public
     * @return array
     */
    public function getForGamestart(DBObject $gamestart)
    {
        $select = $this->getSelect()
            ->setWhere('gamestart_id', '=', $gamestart->id);

        return $this->findBySelectMany($select);
    }

    public function getAssignedPlayers()
    {
        $groups = $this->getSchedule()->getAssignedByType('spiller');
        return array_sum(array_map(function($x) {return count($x);}, $groups));
    }

    public function getMissingPlayers()
    {
        return $this->getAssignedPlayers() - $this->gamers_present;
    }
}
