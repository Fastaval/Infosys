<?php
/**
 * Copyright (C) 2009-2012 Peter Lind
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
 * @category  Infosys
 * @package   Entities
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2009-2012 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

/**
 * handles the deltagere_gdstilmeldinger table
 *
 * @category Infosys
 * @package  Entities
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class DeltagereGDSTilmeldinger extends DBObject
{

    /**
     * Name of database table
     *
     * @var string
     */
    protected $tablename = 'deltagere_gdstilmeldinger';

    /**
     * returns all gds signups for the given deltager
     *
     * @param object $deltager Deltager to check for
     *
     * @access public
     * @return array
     */
    public function getDeltagerTilmeldinger($deltager)
    {
        $select = $this->getSelect();
        $select->setWhere('deltager_id', '=', $deltager->id);

        return $this->findBySelectMany($select);
    }

    /**
     * returns all gds signups for the given shift
     *
     * @param object $vagt Gdsvagt to check for
     *
     * @access public
     * @return array
     */
    public function getVagtTilmeldinger($vagt)
    {
        throw new FrameworkException('Deprecated');
    }

    /**
     * method docblock
     *
     * @param GDSVagter $shift Shift object
     *
     * @access public
     * @return array
     */
    public function getPeriodSignups(GDSVagter $shift)
    {
        $start_timestamp = strtotime($shift->start);
        $period          = $this->makePeriod($start_timestamp);

        if (!($category = $shift->getGDSCategory())) {
            return array();
        }

        $ids = $this->db->query($this->getSelect()->setWhere('period', '=', $period)->setWhere('category_id', '=', $category->id)->setField('deltager_id')->assemble(), array($period, $category->id));

        $array = array();
        foreach ($ids as $row) {
            $array[] = $row['deltager_id'];
        }

        if (empty($array)) {
            return array();
        }

        $participant = $this->createEntity('Deltagere');
        return $participant->findBySelectMany($participant->getSelect()->setWhere('id', 'IN', $array));
    }

    /**
     * returns datetime string of period
     *
     * @param int $timestamp Unix epoch timestamp
     *
     * @access protected
     * @return string
     */
    protected function makePeriod($timestamp)
    {
        $periods = array(
            '04' => '12',
            '12' => '17',
            '17' => '04',
        );

        $hour = date('H', $timestamp);

        foreach ($periods as $start => $end) {
            if ($hour >= $start && $hour < $end) {
                break;
            }
        }

        if ($hour < 4) {
            $timestamp -= 86400;
        }

        return date('Y-m-d', $timestamp) . ' ' . $start . '-' . $end;
    }

    /**
     * returns true if the participant signed up for the shift
     *
     * @param object $deltager Deltagere entity
     * @param object $vagt     GDSVagter entity
     *
     * @access public
     * @return bool
     */
    public function participantSignedUpForShift($deltager, $vagt)
    {
        if (!$vagt->getPeriod()) {
            return false;
        }

        $query = 'SELECT COUNT(*) AS count FROM deltagere_gdstilmeldinger WHERE period = ? AND category_id = ? AND deltager_id = ?';

        $result = $this->db->query($query, array($vagt->getPeriod(), $vagt->getGDSCategory()->id, $deltager->id));
        return $result[0]['count'];
    }

    /**
     * returns the GDS object for the registration
     *
     * @access public
     * @return GDS
     */
    public function getGDSCategory()
    {
        $gds = $this->createEntity('GDSCategory');
        return $gds->findBySelect($gds->getSelect()->setWhere('id', '=', $this->category_id));
    }

    /**
     * returns meaningful period
     *
     * @access public
     * @return string
     */
    public function getMeaningfulPeriod()
    {
        if (!$this->period) {
            return '';
        }

        $parts = explode(' ', $this->period);
        $date  = array_shift($parts);

        return date('l ' . implode('', $parts), strtotime($date));
    }

    public function getPeriodStart()
    {
        if (!$this->period) {
            return '';
        }

        $parts = explode(' ', $this->period);
        $date  = array_shift($parts);
        $parts = explode('-', $parts[0]);

        return $date . ' ' . $parts[0] . ':00:00';
    }

    public function getTextDescription($lang)
    {
        if (!$this->period) {
            return '';
        }

        $parts = explode(' ', $this->period);

        switch ($parts[1]) {
        case '04-12':
            return $lang === 'en' ? 'Morning' : 'Morgen';

        case '12-17':
            return $lang === 'en' ? 'Midday' : 'Middag';

        case '17-04':
            return $lang === 'en' ? 'Evening' : 'Aften';

        }

        return '';
    }

    public function getDate()
    {
        if (!$this->period) {
            return '';
        }

        $parts = explode(' ', $this->period);

        return $parts[0];
    }
}
