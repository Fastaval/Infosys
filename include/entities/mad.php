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
     * handles the mad table
     *
     * @package MVC
     * @subpackage Entities
     */
class Mad extends DBObject
{

    /**
     * Name of database table
     *
     * @var string
     */
    protected $tablename = 'mad';

    /**
     * returns all scheduled times for this category of food
     *
     * @access public
     * @return array
     */
    public function getMadtider()
    {
        if (!$this->isLoaded())
        {
            return array();
        }
        $select = $this->createEntity('Madtider')->getSelect();
        $select->setWhere('mad_id', '=',$this->id);
        return $this->createEntity('Madtider')->findBySelectMany($select);
    }

    /**
     * method docblock
     *
     * @param
     *
     * @access public
     * @return void
     */
    public function getCategory($english = false)
    {
        if (!$english) {
            return $this->kategori;
        }

        switch ($this->kategori) {
        case 'Luksus':
            return 'Luxury';
        case 'Vegetar':
            return 'Vegetarian';
        case 'Morgenmad':
            return 'Breakfast';
        }

        return $this->kategori;
    }
}
