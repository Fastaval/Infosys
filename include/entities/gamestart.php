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
class Gamestart extends DBObject
{
    const OPEN   = 1;
    const CLOSED = 2;

    /**
     * Name of table
     *
     * @var string
     */
    protected $tablename = "gamestarts";

    /**
     * tries to fetch a gamestart given a
     * datetime or timestamp to search with
     *
     * @param string $time Time to get gamestart for
     *
     * @access public
     * @return GameStart|null
     */
    public function findByDatetime($time)
    {
        try {
            $formatted_time = new DateTime($time);

        } catch (Exception $e) {
            return null;

        }

        $select = $this->getSelect()
            ->setWhere('datetime', '=', $formatted_time->format('Y-m-d H:i:s'));;

        return $this->findBySelect($select);
    }

    /**
     * returns all schedules for the gamestart
     *
     * @access public
     * @return array
     */
    public function getGamestartSchedules()
    {
        if (isset($this->gamestart_schedules)) {
            return $this->gamestart_schedules;
        }

        if (!$this->isLoaded()) {
            return array();
        }

        return $this->createEntity('GamestartSchedule')->getForGamestart($this);
    }

    /**
     * returns all running activities in the room
     *
     * @access public
     * @return array
     */
     /*
    public function getAfviklinger()
    {
        if (!$this->isLoaded()) {
            return array();
        }

        $afv = array();
        foreach ($this->getHold() as $hold) {
            $afv[] = $hold->getAfvikling();
        }

        return $afv;
    }
    */
}
