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
     * handles the deltagere_indgang table
     *
     * @package MVC
     * @subpackage Entities
     */
class DeltagereIndgang extends DBObject
{

    /**
     * Name of database table
     *
     * @var string
     */
    protected $tablename = 'deltagere_indgang';


    /**
     * returns Indgang entities that a deltager is signed up to
     *
     * @param object $deltager - deltager entity to check for
     * @access public
     * @return array
     */
    public function getDeltagerIndgang($deltager)
    {
        $ids = array();
        foreach ($this->getForParticipant($deltager) as $tid) {
            $ids[] = $tid->indgang_id;
        }

        if (!$ids) {
            return array();
        }

        $select = $this->createEntity('Indgang')->getSelect();
        $select->setWhere('id','in',$ids);
        $select->setOrder('id', 'asc');
        return $this->createEntity('Indgang')->findBySelectMany($select);
    }

    /**
     * returns all relationships between participant and entrances
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
        $entrances = $this->findBySelectMany($select);
        $result = false;

        if (empty($entrances)) {
            return array();
        }

        return $entrances;
    }
}
