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
     * handles the lokaler table
     *
     * @package MVC
     * @subpackage Entities
     */
class Lokaler extends DBObject
{

    /**
     * Name of table
     *
     * @var string
     */
    protected $tablename = "lokaler";


    /**
     * does a wildcardsearch for rooms
     *
     * @param string|int $input - searchterm to use
     * @access public
     * @return array
     */
    public function wildcardSearch($input)
    {
        $fields = $this->getColumns();
        $select = $this->getSelect();
        foreach ($fields as $field)
        {
            switch ($field)
            {
                case 'beskrivelse':
                case 'omraade':
                case 'skole':
                    $select->setWhereOr($field, 'like', "%{$input}%");
                    break;
                case 'id':
                    if (preg_match('/^lok(\d+)$/i',$input, $matches))
                    {
                        $select->setWhereOr($field, '=', $matches[1]);
                        break;
                    }
                    break;
            }
        }
        $select->setOrder('id', 'asc');
        return $this->findBySelectMany($select);
    }

    /**
     * checks if the room is occupied between two given times
     *
     * @param string $start - start time to check
     * @param string $slut - end time to check
     * @param bool $exclusive - if rooms that can house several activities are counted as busy
     * @access public
     * @return bool
     */
    public function isOccupiedBetween($start, $slut, $exclusive = true)
    {
        $start = strtotime($start);
        $slut  = strtotime($slut);
        if (!$this->isLoaded() || $this->kan_bookes == 'nej') {
            return true;
        }

        if (!$this->busy_cache) {
            $busy_cache = array();
            $hold       = (($result = $this->getHold()) ? $result : array());

            foreach ($hold as $h) {
                $afv          = $h->getAfvikling();
                $busy_cache[] = array('start' => strtotime($afv->start), 'slut' => strtotime($afv->slut), 'lokale_eksklusiv' => $afv->getAktivitet()->lokale_eksklusiv == 'ja');
                if ($afv->hasMultiBlok()) {
                    foreach ($afv->getMultiBlok() as $multiblok) {
                        $busy_cache[] = array('start' => strtotime($multiblok->start), 'slut' => strtotime($multiblok->slut), 'lokale_eksklusiv' => $afv->getAktivitet()->lokale_eksklusiv == 'ja');
                    }
                }
            }

            $this->busy_cache = $busy_cache;
        }

        foreach ($this->busy_cache as $busy) {
            if (($busy['start'] >= $start && $busy['start'] < $slut) || ($busy['slut'] > $start && $busy['slut'] <= $slut) || ($busy['start'] < $start && $busy['slut'] > $slut)) {
                if ($exclusive || $busy['lokale_eksklusiv']) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * returns array of groups that have activities in the room
     *
     * @access public
     * @return array
     */
    public function getHold()
    {
        if (!$this->isLoaded())
        {
            return array();
        }
        $select = $this->createEntity('Hold')->getSelect();
        $select->setWhere('lokale_id','=',$this->id);
        return $this->createEntity('Hold')->findBySelectMany($select);
    }

    /**
     * returns all running activities in the room
     *
     * @access public
     * @return array
     */
    public function getAfviklinger()
    {
        if (!$this->isLoaded())
        {
            return array();
        }
        $afv = array();
        foreach ($this->getHold() as $hold)
        {
            $afv[] = $hold->getAfvikling();
        }
        return $afv;
    }

    /**
     * returns all participants sleeping in the room
     *
     * @access public
     * @return array
     */
    public function getSleepers()
    {
        if (!$this->isLoaded())
        {
            return array();
        }
        $d = $this->createEntity('Deltagere');
        $select = $d->getSelect();
        $select->setWhere('sovelokale_id', '=', $this->id);
        return $d->findBySelectMany($select);
    }
}
