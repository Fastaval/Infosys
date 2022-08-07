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
     * handles the gds table
     *
     * @package MVC
     * @subpackage Entities
     */
class GDS extends DBObject
{
    /**
     * Name of database table
     *
     * @var string
     */
    protected $tablename = 'gds';

    /**
     * returns the list of shifts for this duty
     *
     * @access public
     * @return array
     */
    public function getVagter() {
        return $this->getShifts();
    }

    public function getShifts($date_ordered = false) {
        if (!$this->isLoaded()) {
            return array();
        }

        $select = $this->createEntity('GDSVagter')->getSelect()
            ->setWhere('gds_id', '=', $this->id);
        if ($date_ordered) {
            $select->setOrder('start', 'asc');
        } else {
            $select->setOrder('id', 'asc');
        }

        return $this->createEntity('GDSVagter')->findBySelectMany($select);
    }

    /**
     * returns the category of the object
     *
     * @access public
     * @return GDSCategory
     */
    public function getCategory()
    {
        $select = $this->createEntity('GDSCategory')->getSelect()
            ->setWhere('id', '=', $this->category_id);

        return $this->createEntity('GDSCategory')->findBySelect($select);
    }
}
