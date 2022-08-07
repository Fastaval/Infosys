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
     * handles the deltagere_madtider table
     *
     * @package MVC
     * @subpackage Entities
     */
class DeltagereMadtider extends DBObject {

    /**
     * Name of database table
     *
     * @var string
     */
    protected $tablename = 'deltagere_madtider';

    /**
     * returns what food choices a deltager has signed up for
     *
     * @param object $deltager - deltager entity to check for
     * @access public
     * @return array
     */
    public function getDeltagerMadtider($deltager)
    {
        $ids = array();
        foreach ($this->getForParticipant($deltager) as $tid) {
            $ids[] = $tid->madtid_id;
        }

        if (!$ids) {
            return $ids;
        }

        $select = $this->createEntity('Madtider')->getSelect();
        $select->setWhere('id','in',$ids);
        $select->setOrder('dato','asc');
        return $this->createEntity('Madtider')->findBySelectMany($select);
    }

    /**
     * returns signups for food
     *
     * @param DBObject $participant Participant to get results for
     *
     * @access public
     * @return array
     */
    public function getForParticipant(DBObject $participant)
    {
        if (!is_object($participant) || !$participant->id) {
            return array();
        }

        $select = $this->getSelect();
        $select->setWhere('deltager_id', '=', $participant->id);
        $tider = $this->findBySelectMany($select);
        $result = false;
        if (empty($tider)) {
            return array();
        }

        return $tider;
    }

    /**
     * returns deltagere that have signed up for a specific food choice
     *
     * @param object $madtid - the food choice to check for
     * @access public
     * @return array
     */
    public function getMadtidDeltagere($madtid)
    {
        if (!is_object($madtid) || !$madtid->id) {
            return array();
        }

        $select = $this->getSelect();
        $select->setWhere('madtid_id','=',$madtid->id);
        $tider = $this->findBySelectMany($select);
        $result = false;

        if (empty($tider)) {
            return array();
        }

        $ids = array();

        foreach ($tider as $tid) {
            $ids[] = $tid->deltager_id;
        }

        $select = $this->createEntity('Deltagere')->getSelect();
        $select->setWhere('id', 'in', $ids);
        return $this->createEntity('Deltagere')->findBySelectMany($select);
    }

    public function getHandoutTime() {
        if (!$this->madtid_id || !$this->time_type) {
            return '';
        }
            die(var_dump($madtid_id));
        $madtid = $this->createEntity('Madtid')->findById($this->madtid_id);
        die(var_dump($madtid->dato));
    }
}
