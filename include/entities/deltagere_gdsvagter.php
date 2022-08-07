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
     * handles the deltagere_gdsvagter table
     *
     * @package MVC
     * @subpackage Entities
     */
class DeltagereGDSVagter extends DBObject
{

    /**
     * Name of database table
     *
     * @var string
     */
    protected $tablename = 'deltagere_gdsvagter';

    /**
     * returns gds vagter a deltager has been put on
     *
     * @param object $deltager - deltager entity
     * @access public
     * @return array
     */
    public function getDeltagerVagter($deltager)
    {
        if (!is_object($deltager) || !$deltager->id) {
            return array();
        }

        $select = $this->getSelect();
        $select->setWhere('deltager_id', '=', $deltager->id);
        $results = $this->findBySelectMany($select);

        if (empty($results)) {
            return array();
        }

        $ids = array();

        foreach ($results as $result) {
            $ids[] = $result->gdsvagt_id;
        }

        $select = $this->createEntity('GDSVagter')->getSelect();
        $select->setWhere('id', 'in', $ids);
        $select->setOrder('start', 'asc');
        return $this->createEntity('GDSVagter')->findBySelectMany($select);
    }

    /**
     * returns the amount of shifts a participant is on
     *
     * @param object $deltager - Deltagere entity
     * @access public
     * @return int
     */
    public function countDeltagerVagter($deltager)
    {
        if (!is_object($deltager) || !$deltager->id) {
            return 0;
        }

        $select = $this->getSelect();
        $select->setWhere('deltager_id', '=', $deltager->id);
        return $this->selectCount($select);
    }
}
