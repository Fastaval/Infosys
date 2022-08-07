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
     * handles the wearpriser table
     *
     * @package MVC
     * @subpackage Entities
     */
class WearPriser extends DBObject
{

    /**
     * Name of database table
     *
     * @var string
     */
    protected $tablename = 'wearpriser';

    /**
     * returns the wear entity connected with this wear price
     *
     * @access public
     * @return object
     */
    public function getWear()
    {
        if (!$this->isLoaded())
        {
            return false;
        }
        return $this->createEntity('Wear')->findById($this->wear_id);
    }

    /**
     * checks whether the current wearprice is for ... or for normal participants
     *
     * @access public
     * @return bool
     */
    public function isArrangoer()
    {
        if (!$this->isLoaded())
        {
            return false;
        }
        return $this->createEntity('BrugerKategorier')->findById($this->brugerkategori_id)->isArrangoer();
    }

    /**
     * returns the kategori for the wearpris
     *
     * @access public
     * @return object
     */
    public function getCategory()
    {
        if (!$this->isLoaded())
        {
            return false;
        }
        return $this->createEntity('BrugerKategorier')->findById($this->brugerkategori_id);
    }

}
