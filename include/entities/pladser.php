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
     * handles the pladser table
     *
     * @package MVC
     * @subpackage Entities
     */
class Pladser extends DBObject
{

    /**
     * Name of database table
     *
     * @var string
     */
    protected $tablename = 'pladser';

    protected $participant;

    /**
     * returns all planned activities for a participant
     *
     * @param object $deltager - Deltagere entity
     *
     * @access public
     * @return array
     */
    public function getDeltagerPladser($deltager)
    {
        if (!is_object($deltager) || !$deltager->isLoaded())
        {
            return array();
        }
        $select = $this->getSelect();
        $select->setWhere('deltager_id', '=',$deltager->id);
        $select->setField('pladser.*',false);
        $select->setFrom('hold');
        $select->setTableWhere('hold.id','pladser.hold_id');
        $select->setFrom('afviklinger');
        $select->setTableWhere('afviklinger.id','hold.afvikling_id');
        $select->setOrder('afviklinger.start','asc');
        return $this->findBySelectMany($select);
    }

    /**
     * returns the group that this spot is in
     *
     * @access public
     * @return object
     */
    public function getHold()
    {
        if (!$this->isLoaded())
        {
            return false;
        }
        return $this->createEntity('Hold')->findById($this->hold_id);
    }

    /**
     * returns the scheduling that this activity-spot is tied to
     *
     * @access public
     * @return object
     */
    public function getAfvikling()
    {
        if (!$this->isLoaded() || !($result = $this->getHold()))
        {
            return false;
        }
        return $this->createEntity('Afviklinger')->findById($result->afvikling_id);
    }

    /**
     * returns the room that this activity-spot is tied to
     *
     * @access public
     * @return object
     */
    public function getLokale()
    {
        if (!$this->isLoaded() || !($result = $this->getHold()))
        {
            return false;
        }
        return $this->createEntity('Lokaler')->findById($result->lokale_id);
    }

    /**
     * returns the activity that this activity-spot is tied to
     *
     * @access public
     * @return object
     */
    public function getAktivitet()
    {
        if (!$this->isLoaded() || !($result = $this->getAfvikling()))
        {
            return false;
        }
        return $this->createEntity('Aktiviteter')->findById($result->aktivitet_id);
    }

    /**
     * wrapper for getDeltager
     *
     * @access public
     * @return object
     */
    public function getParticipant()
    {
        return $this->getDeltager();
    }

    /**
     * returns the participant that this activity-spot is tied to
     *
     * @access public
     * @return object
     */
    public function getDeltager()
    {
        if (isset($this->participant)) {
            return $this->participant;
        }

        if (!$this->isLoaded()) {
            return false;
        }
        return $this->participant = $this->createEntity('Deltagere')->findById($this->deltager_id);
    }

    /**
     * creates a link between a deltager and a group for a certain run of an activity
     *
     * @param object $deltager    - Deltagere entity
     * @param object $hold        - Hold entity
     * @param string $type        - spiller|spilleder
     * @param int    $pladsnummer - number in the group
     *
     * @access public
     * @return bool
     */
    public function setDeltagerPlads($deltager, Hold $hold, $type, $pladsnummer = false)
    {
        if ($this->isLoaded() || !is_object($hold) || !$hold->isLoaded() || !in_array(strtolower($type), array('spiller', 'spilleder')) || !is_object($deltager) || !$deltager->isLoaded())
        {
            return false;
        }
        if (($type == 'spiller' && !$hold->canUseGamers()) || ($type == 'spilleder' && !$hold->needsGMs())) {
            return false;
        }

        if (!$pladsnummer || !is_numeric($pladsnummer))
        {
            $pladsnummer = $this->getNextPladsnummer($hold);
        }
        $this->hold_id = $hold->id;
        $this->pladsnummer = $pladsnummer;
        $this->type = $type;
        $this->deltager_id = $deltager->id;
        return $this->insert() ? true : false;
    }

    /**
     * returns next usable pladsnummer in the team
     *
     * @param object $hold - Hold entity to get next pladsnummer for
     * @access public
     * @return int
     */
    public function getNextPladsnummer($hold)
    {
        $select = $this->getSelect();
        $select->setWhere('hold_id','=',$hold->id);
        $select->setOrder('pladsnummer', 'asc');
        $pladser = $this->findBySelectMany($select);
        $counter = 1;
        foreach ($pladser as $plads)
        {
            if ($counter != $plads->pladsnummer)
            {
                return $counter;
            }
            $counter++;
        }
        return $counter;
    }
}
