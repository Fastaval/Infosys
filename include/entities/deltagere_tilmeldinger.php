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
     * handles the deltagere_tilmeldinger table
     *
     * @package MVC
     * @subpackage Entities
     */
class DeltagereTilmeldinger extends DBObject
{

    /**
     * Name of database table
     *
     * @var string
     */
    protected $tablename = 'deltagere_tilmeldinger';

    private $tilmeldingstyper = array('spiller', 'spilleder');

    /**
     * returns array of participants signups for activities
     *
     * @param object $deltager - Deltagere entity
     * @access public
     * @return array
     */
    public function getDeltagerTilmeldinger($deltager)
    {
        if (!is_object($deltager) || !$deltager->id) {
            return array();
        }

        $select = $this->getSelect();
        $select->setWhere('deltager_id', '=', $deltager->id);
        $select->setFrom('afviklinger');
        $select->setTableWhere('afviklinger.id', 'deltagere_tilmeldinger.afvikling_id');
        $select->setOrder('afviklinger.start', 'asc');

        return $this->findBySelectMany($select);
    }

    /**
     * returns the scheduled time for the activity this signup is for
     *
     * @access public
     * @return object|bool
     */
    public function getAfvikling()
    {
        if (!$this->afvikling_id)
        {
            return false;
        }
        return $this->createEntity('Afviklinger')->findById($this->afvikling_id);
    }

    /**
     * returns the activity this signup is for
     *
     * @access public
     * @return object|bool
     */
    public function getAktivitet()
    {
        if (!$this->afvikling_id)
        {
            return false;
        }
        return $this->getAfvikling()->getAktivitet();
    }

    /**
     * wrapper for getAktivitet to move away from danish in code
     *
     * @access public
     * @return bool|object
     */
    public function getActivity()
    {
        return $this->getAktivitet();
    }

    /**
     * returns the participant this signup is for
     *
     * @access public
     * @return object|bool
     */
    public function getDeltager()
    {
        if (!$this->deltager_id) {
            return false;
        }

        return $this->createEntity('Deltagere')->findById($this->deltager_id);
    }

    /**
     * signs a participant up for a scheduled run of an activity
     *
     * @param object $deltager        - Deltagere entity
     * @param object $afvikling       - Afviklinger entity
     * @param int    $prioritet       - priority the user attached to the event
     * @param string $tilmeldingstype - spiller|spilleder

     * @access public
     * @return bool
     */
    public function setTilmelding($deltager, $afvikling, $prioritet, $tilmeldingstype)
    {
        if (!is_object($deltager) || !is_object($afvikling) || !$deltager->id || !$afvikling->id || !in_array(strtolower($tilmeldingstype), $this->tilmeldingstyper)) {
            return false;
        }

        if (!$afvikling->getAktivitet()->needsSpilleder() && $tilmeldingstype == 'spilleder') {
            return false;
        }

        $this->deltager_id     = $deltager->id;
        $this->afvikling_id    = $afvikling->id;
        $this->prioritet       = $prioritet;
        $this->tilmeldingstype = strtolower($tilmeldingstype);
        return $this->insert();
    }

    /**
     * checks if the sign up is as GM
     *
     * @access public
     * @return bool
     */
    public function getRoleAcronym()
    {
        if (!$this->tilmeldingstype || $this->tilmeldingstype != 'spilleder') {
            return 'S';
        }

        return 'SL';
    }

    /**
     * finds schedules that potentially conflict with this
     * one, but only there actually is a conflict
     *
     * @access public
     * @return array
     */
    public function findConflictingSchedules()
    {
        if (!$this->afvikling_id || !$this->deltager_id) {
            return array();
        }

        $priorities = array();
        $schedules  = $this->getConcurrentSchedules();
        foreach ($schedules as $schedule) {
            $priorities[$schedule->prioritet] = empty($priorities[$schedule->prioritet]) ? 1 : $priorities[$schedule->prioritet] + 1;
        }

        foreach ($priorities as $weight => $priority) {
            if ($priority > 1 && $weight < 4) {
                return $schedules;
            }
        }

        return array();
    }

    /**
     * method docblock
     *
     * @param
     *
     * @access public
     * @return void
     */
    public function getConcurrentSchedules()
    {
        if (!$this->afvikling_id || !$this->deltager_id) {
            return array();
        }

        if (!($schedule = $this->createEntity('Afviklinger')->findById($this->afvikling_id))) {
            return array();
        }

        $ids = array();
        foreach ($schedule->getConcurrentSchedulesForSignup($this) as $schedule) {
            $ids[] = $schedule->id;
        }

        if (empty($ids)) {
            return array();
        }

        $select = $this->getSelect()->setWhere('afvikling_id', 'IN', $ids)->setWhere('deltager_id', '=', $this->deltager_id);
        return $this->findBySelectMany($select);
    }
}
