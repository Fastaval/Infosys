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
FROM diyageranges
WHERE
    diy_id = ?
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
DELETE FROM diyageranges WHERE diy_id = ? AND requirementtype = ?
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
INSERT INTO diyageranges SET diy_id = ?, requirementtype = ?, age = ? ON DUPLICATE KEY UPDATE age = ?
';

        $this->db->exec($query, [$this->id, $type, $age, $age]);

        return $this;
    }
}
