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
 * PHP version 5
 *
 * @category  Infosys
 * @package   Entities
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2009-2012 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

/**
 * handles the gdsvagter table
 *
 * @category Infosys
 * @package  Entities
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class GDSVagter extends DBObject
{

    /**
     * Name of database table
     *
     * @var string
     */
    protected $tablename = 'gdsvagter';

    /**
     * returns the name of the duty this shift is for
     *
     * @param bool $english
     *
     * @access public
     * @return string|bool
     */
    public function getGDSName($english = false)
    {
        if (!$this->isLoaded() || !($gds = $this->createEntity('GDS')->findById($this->gds_id))) {
            return false;
        }

        return $english ? $gds->title_en : $gds->navn;
    }

    /**
     * returns the gds category of the gds object
     *
     * @access public
     * @return GDSCategory|false
     */
    public function getGDSCategory()
    {
        if ($this->getGDS()) {
            return $this->getGDS()->getCategory();
        }

        return false;
    }

    /**
     * returns the GDS activity it's a shift for
     *
     * @access public
     * @return object|bool
     */
    public function getGDS()
    {
        if (!$this->isLoaded()) {
            return false;
        }

        return $this->createEntity('GDS')->findById($this->gds_id);
    }

    /**
     * checks whether a given shift is filled up with participants
     *
     * @access public
     * @return bool
     */
    public function hasEnoughDeltagere()
    {
        if (!$this->isLoaded()) {
            return false;
        }

        $select = $this->createEntity('DeltagereGDSVagter')->getSelect();
        $select->setWhere('gdsvagt_id','=', $this->id);
        $count = $this->selectCount($select);

        return (($count >= $this->antal_personer) ? true : false);
    }

    /**
     * returns string detailing period of the shift
     *
     * @throws Exception
     * @access public
     * @return void
     */
    public function getPeriod()
    {
        if (!$this->start) {
            throw new FrameworkException('Not loaded');
        }

        $timestamp = strtotime($this->start);
        $hour      = date('H', $timestamp);
        if ($hour >= 4 && $hour < 12) {
            return date('Y-m-d 04-12', $timestamp);
        } elseif($hour >= 12 && $hour < 17) {
            return date('Y-m-d 12-17', $timestamp);
        } else {
            return date('Y-m-d 17-04', $timestamp);
        }
    }

    /**
     * returns the participants assigned to this shift
     *
     * @access public
     * @return array
     */
    public function getParticipants()
    {
        if (!$this->isLoaded()) {
            return array();
        }

        $select = $this->createEntity('DeltagereGDSVagter')->getSelect();
        $select->setWhere('gdsvagt_id','=', $this->id);
        $result = $this->createEntity('DeltagereGDSVagter')->findBySelectMany($select);
        if (empty($result)) {
            return array();
        }

        $ids = array();
        foreach ($result as $v) {
            $ids[] = $v->deltager_id;
        }

        $select = $this->createEntity('Deltagere')->getSelect();
        $select->setWhere('id','in',$ids);
        return $this->createEntity('Deltagere')->findBySelectMany($select);
    }

    /**
     * returns the participants assigned to this shift
     *
     * @access public
     * @return array
     */
    public function getSignups()
    {
        if (!$this->isLoaded()) {
            return array();
        }

        return $this->createEntity('DeltagereGDSTilmeldinger')->getPeriodSignups($this);
    }

    /**
     * returns number of participants assigned to the shift
     *
     * @access public
     * @return int
     */
    public function getParticipantCount()
    {
        if (!$this->isLoaded()) {
            return false;
        }

        $select = $this->createEntity('DeltagereGDSVagter')->getSelect();
        $select->setWhere('gdsvagt_id','=', $this->id);
        return (($count = $this->selectCount($select)) ? $count : 0);
    }


    /**
     * attempts to add a participant to the shift
     *
     * @param object $deltager - Deltagere entity
     * @access public
     * @return bool
     */
    public function addParticipant($deltager)
    {
        if (!$this->isLoaded() || !is_object($deltager) || !$deltager->isLoaded()) {
            return false;
        }

        $dgv              = $this->createEntity('DeltagereGDSVagter');
        $dgv->deltager_id = $deltager->id;
        $dgv->gdsvagt_id  = $this->id;
        return $dgv->insert();
    }

    /**
     * attempts to remove a participant to the shift
     *
     * @param object $deltager - Deltagere entity
     * @access public
     * @return bool
     */
    public function removeParticipant($deltager)
    {
        if (!$this->isLoaded() || !is_object($deltager) || !$deltager->isLoaded()) {
            return false;
        }

        $dgv    = $this->createEntity('DeltagereGDSVagter');
        $select = $dgv->getSelect();
        $select->setWhere('deltager_id','=',$deltager->id);
        $select->setWhere('gdsvagt_id','=',$this->id);
        if ($dgv->findBySelect($select)) {
            return $dgv->delete();
        }

        return false;
    }

    /**
     * returns meaningful period
     *
     * @access public
     * @return string
     */
    public function getMeaningfulPeriod()
    {
        if (!$this->start) {
            return '';
        }

        $parts = explode(' ', $this->getPeriod());
        $date  = array_shift($parts);

        return date('l ' . implode('', $parts), strtotime($date));
    }

    /**
     * checks if a participant was marked noshow for
     * this specific diy shift
     *
     * @param DBObject $participant Participant to check
     *
     * @access public
     * @return bool
     */
    public function participantWasNoshow(DBObject $participant)
    {
        if (!$this->isLoaded()) {
            return false;
        }

        $relationship = $this->createEntity('DeltagereGDSVagter');

        try {
            $relationship = $relationship->findBySelect(
                $relationship->getSelect()
                    ->setWhere('deltager_id', '=', $participant->id)
                    ->setWhere('gdsvagt_id', '=', $this->id)
                    ->setWhere('noshow', '=', 1)
                );

            return !!$relationship;

        } catch (FrameworkException $e) {
            $e->logException();

            return false;
        }
    }
}
