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
     * handles the brugerkategorier table
     *
     * @package MVC
     * @subpackage Entities
     */
class BrugerKategorier extends DBObject
{

    /**
     * Name of database table
     *
     * @var string
     */
    protected $tablename = 'brugerkategorier';

    /**
     * returns the name of the category for a given object id
     *
     * @param int $id - id of category object to load
     * @access public
     * @return string
     */
    public function getCategoryName($id)
    {
        if (!is_numeric($id))
        {
            return '';
        }
        return (($result = $this->createEntity('BrugerKategorier')->findById($id)) ? $result->navn : '');
    }

    /**
     * returns the category for a given deltager
     *
     * @param object $deltager - deltager entity to find category for
     * @access public
     * @return bool|object
     */
    public function getDeltagerCategory($deltager)
    {
        if (!is_object($deltager) || !$deltager->isLoaded())
        {
            return false;
        }
        return (($result = $this->createEntity('BrugerKategorier')->findById($deltager->brugerkategori_id)) ? $result : '');
    }

    /**
     * checks whether the loaded object has arrangoer-status or not
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
        return $this->arrangoer == 'ja';
    }

    /**
     * returns an arrangoer category
     *
     * @access public
     * @return object
     */
    public function getArrangoer()
    {
        $bk = $this->createEntity('BrugerKategorier');
        return $bk->findByName('Arrangør');
    }

    /**
     * returns a deltager category
     *
     * @access public
     * @return object
     */
    public function getDeltager()
    {
        $bk = $this->createEntity('BrugerKategorier');
        return $bk->findByName('Deltager');
    }
}
