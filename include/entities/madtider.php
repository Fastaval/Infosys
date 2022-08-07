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
 * @category  Infosys
 * @package   Entities
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2009 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

/**
 * handles the madtider table
 *
 * @category Infosys
 * @package  Entities
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class Madtider extends DBObject
{

    /**
     * Name of database table
     *
     * @var string
     */
    protected $tablename = 'madtider';

    /**
     * the food category
     *
     * @var object
     */
    private $mad;

    /**
     * returns the food type this scheduled food is for
     *
     * @access public
     * @return object
     */
    public function getMad()
    {
        if (!$this->mad) {
            if (!$this->isLoaded()) {
                return false;
            }

            $select = $this->createEntity('Mad')->getSelect();
            $select->setWhere('id', '=', $this->mad_id);
            $this->mad = $this->createEntity('Mad')->findBySelect($select);
        }

        return $this->mad;
    }

    public function isBreakfast()
    {
        if (!$this->isLoaded()) {
            return false;
        }

        $hour = date('H', strtotime($this->dato));
        return intval($hour) < 10;
    }

    public function isDinner()
    {
        if (!$this->isLoaded()) {
            return false;
        }

        $hour = date('H', strtotime($this->dato));
        return intval($hour) >= 17;
    }

    public function isLunch()
    {
        if (!$this->isLoaded()) {
            return false;
        }

        return !$this->isBreakfast() && !$this->isDinner();
    }

    /**
     * returns list of participants buying this food
     *
     * @access public
     * @return array
     */
    public function getDeltagere()
    {
        if (!$this->isLoaded()) {
            return array();
        }

        return $this->createEntity('DeltagereMadtider')->getMadtidDeltagere($this);

    }

    public function getFriendlyName()
    {
        if (!$this->isLoaded()) {
            return '';
        }

        return $this->getMad()->kategori . ", " . date('D',strtotime($this->dato));
    }

    public function getServedCount()
    {
        $query  = "SELECT COUNT(deltager_id) AS count FROM deltagere_madtider WHERE received = 1 AND madtid_id = ?";
        $result = $this->getDB()->query($query, array($this->id));
        return $result[0]['count'];
    }

    public function getLeftCount()
    {
        $query  = "SELECT COUNT(deltager_id) AS count FROM deltagere_madtider WHERE received = 0 AND madtid_id = ?";
        $result = $this->getDB()->query($query, array($this->id));
        return $result[0]['count'];
    }

    public function getHandoutTime(Deltagere $d)
    {
        if (!$this->isLoaded()) {
            return '';
        }

        $select = $this->createEntity('DeltagereMadtider')->getSelect()
            ->setWhere('madtid_id', '=', $this->id)
            ->setWhere('deltager_id', '=', $d->id);

        if ($entity = $this->createEntity('DeltagereMadtider')->findBySelect($select)) {
            if ($entity->time_type) {
                $start_mod = ($entity->time_type - 1) * 30 * 60;
                $end_mod   = ($entity->time_type) * 30 * 60;
                $start     = date('H:i', strtotime($this->dato) + $start_mod);
                $end       = date('H:i', strtotime($this->dato) + $end_mod);
                return $start . ' - ' . $end;
            }
        }

        return '';
    }

    public function isReceived(Deltagere $deltager) {
        if (!$this->isLoaded()) {
            return false;
        }

        $query  = "SELECT deltager_id FROM deltagere_madtider WHERE received = 1 AND madtid_id = ? AND deltager_id = ?";
        $result = $this->getDB()->query($query, array($this->id, $deltager->id));
        return count($result);
    }
}
