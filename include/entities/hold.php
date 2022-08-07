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
     * handles the hold table
     *
     * @package MVC
     * @subpackage Entities
     */
class Hold extends DBObject
{

    /**
     * Name of database table
     *
     * @var string
     */
    protected $tablename = 'hold';

    protected $schedule;
    protected $activity;

    protected $places;

    /**
     * wrapper for getAktivitet
     *
     * @access publi
     * @return object
     */
    public function getActivity()
    {
        return $this->getAktivitet();
    }

    /**
     * loads the aktivitet for the afvikling for the hold
     *
     * @return bool
     * @access public
     */
    public function getAktivitet()
    {
        if (!$this->isLoaded()) {
            return false;
        }

        if (isset($this->activity)) {
            return $this->activity;
        }

        return $this->activity = $this->getAfvikling()->getAktivitet();
    }

    /**
     * checks whether the loaded needs a spilleder
     *
     * @return bool
     * @access public
     */
    public function needsGMs()
    {
        if (!$this->isLoaded()) {
            return false;
        }

        if ($this->countParticipantType('spilleder') >= $this->getAktivitet()->spilledere_per_hold) {
            return false;
        }
        return true;
    }

    /**
     * checks whether the loaded needs gamers 
     *
     * @return bool
     * @access public
     */
    public function needsGamers()
    {
        if (!$this->isLoaded()) {
            return false;
        }

        if ($this->countParticipantType('spiller') >= $this->getAktivitet()->min_deltagere_per_hold) {
            return false;
        }

        return true;
    }

    /**
     * counts how many of type is assigned to the group
     *
     * @param string $type Type to count
     *
     * @throws Exception
     * @access public
     * @return int
     */
    public function countParticipantType($type)
    {
        if (!$this->isLoaded()) {
            return 0;
        }

        if (!in_array($type, array('spiller', 'spilleder'))) {
            throw new FrameworkException('Wrong type: ' . $type);
        }

        $select = $this->createEntity('Pladser')->getSelect();

        $select->setWhere('hold_id', '=', $this->id);
        $select->setWhere('type', '=', $type);

        return $this->selectCount($select);
    }

    /**
     * checks whether the loaded needs gamers 
     *
     * @return bool
     * @access public
     */
    public function canUseGamers()
    {
        if (!$this->isLoaded()) {
            return false;
        }

        if ($this->countParticipantType('spiller') < $this->getAktivitet()->max_deltagere_per_hold) {
            return true;
        }

        return false;
    }

    /**
     * checks whether the group can need more gamers or gms
     *
     * @access public
     * @return bool
     */
    public function isFull()
    {
        if (!$this->isLoaded())
        {
            return false;
        }
        return (!$this->needsGMs() && !$this->needsGamers());
    }

    /**
     * wrapper for getAfvikling
     *
     * @return object
     * @access public
     */
    public function getSchedule()
    {
        return $this->getAfvikling();
    }

    /**
     * loads the afvikling for the hold
     *
     * @return object
     * @access public
     */
    public function getAfvikling()
    {
        if (!$this->isLoaded()) {
            return false;
        }

        if (isset($this->schedule)) {
            return $this->schedule;
        }

        return $this->schedule = $this->createEntity('Afviklinger')->findById($this->afvikling_id);
    }

    /**
     * loads the room for the group
     *
     * @return object
     * @access public
     */
    public function getLokale()
    {
        if (!$this->isLoaded())
        {
            return false;
        }
        return $this->createEntity('Lokaler')->findById($this->lokale_id);
    }

    /**
     * returns array of participants in the group
     *
     * @param string $type - spiller|spilleder
     * @access public
     * @return array
     */
    public function getPladser($type = null)
    {
        if (isset($this->places)) {
            return $this->places;
        }

        if (!$this->isLoaded()) {
            return array();
        }

        $select = $this->createEntity('Pladser')->getSelect();
        $select->setWhere('hold_id', '=',$this->id)
            ->setOrder('pladsnummer', 'asc');

        if ($type && in_array($type, array('spiller','spilleder'))) {
            $select->setWhere('type', '=',$type);
        }
        return $this->createEntity('Pladser')->findBySelectMany($select);
    }

    /**
     * shortcut to getPladser('spiller')
     *
     * @access public
     * @return array
     */
    public function getGamers()
    {
        return $this->getPladser('spiller');
    }

    /**
     * shortcut to getPladser('spilleder')
     *
     * @access public
     * @return array
     */
    public function getGMs()
    {
        return $this->getPladser('spilleder');
    }

    /**
     * returns the highest group number for a given scheduling + 1
     *
     * @param object $afvikling - Afviklinger entity
     * @access public
     * @return int|bool
     */
    public function getNextHoldnummer($afvikling)
    {
        if (!is_object($afvikling) || !$afvikling->isLoaded())
        {
            return false;
        }
        $select = $this->getSelect();
        $select->setWhere('afvikling_id','=',$afvikling->id)->
                 setField('MAX(holdnummer) as max',false);
        $DB = $this->getDB();
        if ($result = $DB->query($select))
        {
            return ($result[0]['max'] + 1);
        }
        return false;
    }

    /**
     * returns Deltagere entities for the group
     *
     * @access public
     * @return array
     */
    public function getDeltagere()
    {
        if (!$this->isLoaded()) {
            return array();
        }

        $pladser = $this->getPladser();
        $ids = array();

        foreach ($pladser as $plads) {
            $ids[] = $plads->deltager_id;
        }

        if (empty($ids)) {
            return array();
        }

        $del = $this->createEntity('Deltagere');
        $select = $del->getSelect();
        $select->setWhere('id','in',$ids);
        return $del->findBySelectMany($select);
    }
}
