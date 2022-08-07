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
     * @subpackage Models
     * @author     Peter Lind <peter.e.lind@gmail.com>
     * @copyright  2009 Peter Lind
     * @license    http://www.gnu.org/licenses/gpl.html GPL 3
     * @link       http://www.github.com/Fake51/Infosys
     */

    /**
     * handles all data fetching for the entrance controller
     *
     * @package    MVC
     * @subpackage Models
     */
class EntranceModel extends Model
{

    /**
     * returns all Indgang entities
     *
     * @access public
     * @return array
     */
    public function getAllEntries()
    {
        return $this->createEntity('Indgang')->findAll();
    }


    /**
     * tries to create a wear type
     *
     * @param RequestVars $post - POST vars
     *
     * @access public
     * @return bool
     */
    public function createEntry(RequestVars $post)
    {
        if (empty($post->type) || !ctype_digit($post->pris)) {
            return false;
        }

        $indgang        = $this->createEntity('Indgang');
        $indgang->type  = $post->type;
        $indgang->pris  = $post->pris;
        $indgang->start = date('Y-m-d H:i:s', strtotime($post->start));
        return (($indgang->insert()) ? $indgang : false);
    }

    /**
     * tries to update a wear type
     *
     * @param object $wear - Wear entity
     * @param RequestVars $post - POST vars
     *
     * @access public
     * @return bool
     */
    public function updateEntry($indgang, RequestVars $post)
    {
        if (!is_object($indgang) || !$indgang->isLoaded())
        {
            return false;
        }
        if (empty($post->type) || empty($post->pris))
        {
            return false;
        }
        $indgang->type =  $post->type;
        $indgang->pris = $post->pris;
        return $indgang->update();
    }

    /**
     * get numbers sold for each Indgang type in array
     *
     * @param array $array - array of Indgang types
     *
     * @access public
     * @return array
     */
    public function getNumbersSold($array)
    {
        if (!is_array($array)) {
            return array();
        }

        foreach ($array as &$indgang) {
            $select = $this->createEntity('DeltagereIndgang')->getSelect();
            $select->setWhere('indgang_id','=',$indgang->id);
            $indgang->solgt = $this->createEntity('DeltagereIndgang')->selectCount($select);
        }

        return $array;
    }

    /**
     * fetches statistics regarding entries
     *
     * @access public
     * @return array
     */
    public function getEntryStats() {
        $return = array();
        $entries = $this->createEntity('Indgang')->findAll();
        foreach ($entries as $entry) {
            $return[$entry->type][$entry->start] = $entry;
        }
        return $return;
    }
}
