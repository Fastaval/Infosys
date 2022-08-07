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

define('KARMATYPE_BIG', 1);
define('KARMATYPE_MEDIUM', 2);
define('KARMATYPE_SMALL', 3);

/**
 * handles the Aktiviteter table
 *
 * @package MVC
 * @subpackage Entities
 */
class Aktiviteter extends DBObject
{

    /**
     * Name of database table
     *
     * @var string
     */
    protected $tablename = "aktiviteter";


    /**
     * performs a wildcard search across the tables fields
     *
     * @param string|int @input - term to look for
     * @access public
     * @return array
     */
    public function wildcardSearch($input) {
        $objects = array();
        $fields = $this->getColumns();
        $select = $this->getSelect();
        foreach ($fields as $field)
        {
            switch ($field)
            {
                case 'note':
                case 'foromtale':
                case 'navn':
                case 'title_en':
                    $select->setWhereOr($field, 'like', "%{$input}%");
                    break;
                default:
                    break;
            }
        }
        $select->setOrder('id', 'asc');
        $results = $this->findBySelectMany($select);
        if (!empty($results)) {
            $objects = $results;
        }
        return $objects;
    }

    /**
     * returns the afviklinger for the loaded aktivitet
     *
     * @access public
     * @return bool|array
     */
    public function getAfviklinger()
    {
        if (!$this->isLoaded())
        {
            return array();
        }
        $select = $this->createEntity('Afviklinger')->getSelect();
        $select->setWhere('aktivitet_id','=',$this->id);
        $select->setOrder('start', 'asc');
        return $this->createEntity('Afviklinger')->findBySelectMany($select);
    }

    /**
     * returns both normal schedules and attached multiblok schedules
     *
     * @access public
     * @return array
     */
    public function getCompleteScheduling()
    {
        $schedules = $this->getAfviklinger();
        foreach ($schedules as $schedule) {
            $schedules = array_merge($schedules, $schedule->getMultiBlok());
        }

        return $schedules;
    }

    /**
     * checks whether the aktivitet should have spilleder or not
     *
     * @access public
     * @return bool
     */
    public function needsSpilleder()
    {
        return $this->spilledere_per_hold > 0;
    }

    /**
     * returns array of activity types
     *
     * @access public
     * @return array
     */
    public function getAvailableTypes()
    {
        $types = $this->getValidColumnValues('type');
        return $types['values'];
    }

    /**
     * returns array of all participants already signed up for the activity
     *
     * @access public
     * @return array
     */
    public function getParticipantsOnTeams($type = '')
    {
        if (!$this->isLoaded())
        {
            return array();
        }
        $participants_on_teams = array();
        foreach ($this->getAfviklinger() as $afvikling)
        {
            foreach ($afvikling->getHold() as $hold)
            {
                foreach ($hold->getPladser() as $plads)
                {
                    if ($type && $type !== $plads->type) {
                        continue;
                    }

                    $participants_on_teams[] = $plads->deltager_id;
                }
            }
        }
        return $participants_on_teams;
    }

    /**
     * checks for empty properties and inserts defaults
     * in their place
     *
     * @access public
     * @return void
     */
    public function convertProblematicToDefault()
    {
        $this->kan_tilmeldes          = $this->kan_tilmeldes ? $this->kan_tilmeldes : 'nej';
        $this->varighed_per_afvikling = $this->varighed_per_afvikling ? $this->varighed_per_afvikling : 0;
        $this->min_deltagere_per_hold = $this->min_deltagere_per_hold ? $this->min_deltagere_per_hold : 0;
        $this->max_deltagere_per_hold = $this->max_deltagere_per_hold ? $this->max_deltagere_per_hold : 0;
        $this->spilledere_per_hold    = $this->spilledere_per_hold ? $this->spilledere_per_hold : 0;
        $this->pris                   = $this->pris ? $this->pris : 0;
        $this->lokale_eksklusiv       = $this->lokale_eksklusiv ? $this->lokale_eksklusiv : 'ja';
        $this->wp_link                = $this->wp_link ? $this->wp_link : 0;
        $this->type                   = $this->type ? $this->type : 'rolle';
        $this->tids_eksklusiv         = $this->tids_eksklusiv ? $this->tids_eksklusiv : 'ja';
        $this->sprog                  = $this->sprog ? $this->sprog : 'dansk';
        $this->replayable             = $this->replayable ? $this->replayable : 'nej';
        $this->hidden                 = $this->hidden ? $this->hidden : 'nej';
        $this->karmatype              = $this->karmatype ? $this->karmatype : 0;
    }

    /**
     * returns a meaningful text for the karmatype
     *
     * @access public
     * @return string
     */
    public function getMeaningfulKarmatype()
    {
        switch ($this->karmatype) {
        case KARMATYPE_BIG:
            return 'Stor';

        case KARMATYPE_MEDIUM:
            return 'Medium';

        case KARMATYPE_SMALL:
            return 'Lille';

        default:
            return 'Ingen';
        }
    }

    /**
     * returns minimum required age for the activity, if any
     *
     * @access public
     * @return int|false
     */
    public function getMinAge()
    {
        return $this->getAgeRequirement('min');
    }

    /**
     * returns maximum required age for the activity, if any
     *
     * @access public
     * @return Int|false
     */
    public function getMaxAge()
    {
        return $this->getAgeRequirement('max');
    }

    /**
     * returns age requirement
     *
     * @param string $type Type of requirement
     *
     * @access protected
     * @return int|false
     */
    protected function getAgeRequirement($type)
    {
        $query = '
SELECT age
FROM activityageranges
WHERE
    activity_id = ?
    AND requirementtype = ?
';

        $result = $this->db->query($query, [$this->id, $type]);

        if (count($result)) {
            return intval($result[0]['age']);
        }

        return false;
    }

    /**
     * removes a max age requirement
     *
     * @access public
     * @return self
     */
    public function removeMaxAge()
    {
        return $this->removeAgeRequirement('max');
    }

    /**
     * removes a max age requirement
     *
     * @access public
     * @return self
     */
    public function removeMinAge()
    {
        return $this->removeAgeRequirement('min');
    }

    /**
     * removes an age requirement
     *
     * @param string $type Type of requirement to remove
     *
     * @access protected
     * @return self
     */
    protected function removeAgeRequirement($type)
    {
        $query = '
DELETE FROM activityageranges WHERE activity_id = ? AND requirementtype = ?
';

        $this->db->exec($query, [$this->id, $type]);

        return $this;
    }

    /**
     * sets a max age requirement
     *
     * @param int $age Age to set
     *
     * @access public
     * @return self
     */
    public function setMaxAge($age)
    {
        return $this->setAgeRequirement('max', $age);
    }

    /**
     * sets a min age requirement
     *
     * @param int $age Age to set
     *
     * @access public
     * @return self
     */
    public function setMinAge($age)
    {
        return $this->setAgeRequirement('min', $age);
    }

    /**
     * sets an age requirement
     *
     * @param string $type Type of requirement to set
     * @param int    $age  Age to set
     *
     * @access protected
     * @return self
     */
    protected function setAgeRequirement($type, $age)
    {
        $query = '
INSERT INTO activityageranges SET activity_id = ?, requirementtype = ?, age = ? ON DUPLICATE KEY UPDATE age = ?
';

        $this->db->exec($query, [$this->id, $type, $age, $age]);

        return $this;
    }
}
