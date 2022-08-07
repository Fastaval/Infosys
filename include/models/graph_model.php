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
 * @package   Models
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2009-2012 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

/**
 * handles all data fetching for the index controller
 *
 * @category Infosys
 * @package  Models
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class GraphModel extends Model
{
    /**
     * returns array of signups per day for days in the signup period
     *
     * @access public
     * @return array
     */
    public function getSignupData()
    {
        $day    = date('Y-m-d', strtotime($this->config->get('con.signupstart')));
        $end    = $this->config->get('con.end');
        $today  = time() < strtotime($end) ? time() : strtotime($end);
        $result = $this->getSignupNumbers($day);

        $ordered_result = array();
        $index          = 0;
        while (strtotime($day) < $today) {
            $ordered_result[$index] = array(
                0 => date('d-m-Y', strtotime($day)),
                1 => 0,
            );

            foreach ($result as $row) {
                if ($row['date'] == $day) {
                    $ordered_result[$index][1] = intval($row['count']);
                    break;
                }
            }

            $day = date('Y-m-d', strtotime($day . ' + 1 day'));
            $index++;
        }

        return $this->makeSignupOutput($ordered_result);
    }

    /**
     * returns array of signups per day grouped by age
     *
     * @access public
     * @return array
     */
    public function getAgeGroupedSignupData()
    {
        $day      = $this->config->get('con.signupstart');
        $last_day = '2013-03-25';

        $query = '
SELECT
    DATE(signed_up) AS date,
    CASE WHEN birthdate > NOW() - INTERVAL 20 YEAR THEN 20
         WHEN birthdate > NOW() - INTERVAL 35 YEAR THEN 35
         ELSE 100 END AS agegroup,
    COUNT(*) AS count
FROM
    deltagere AS d
WHERE
    DATE(signed_up) BETWEEN ? AND ?
GROUP BY
    date,
    agegroup
ORDER BY
    date,
    agegroup
';

        $result = $this->db->query($query, $day, $last_day);

        $ordered_result = array();
        $index          = 0;
        $today          = strtotime($last_day);
        while (strtotime($day) < $today) {
            $ordered_result[$index] = array(
                0 => date('d-m-Y', strtotime($day)),
                1 => 0,
                2 => 0,
                3 => 0,
            );

            foreach ($result as $row) {
                if ($row['date'] == $day) {
                    switch($row['agegroup']) {
                    case 20:
                        $ordered_result[$index][1] = intval($row['count']);
                        break;

                    case 35:
                        $ordered_result[$index][2] = intval($row['count']);
                        break;

                    case 100:
                        $ordered_result[$index][3] = intval($row['count']);
                        break;
                    }
                }
            }

            $day = date('Y-m-d', strtotime($day . ' + 1 day'));
            $index++;
        }

        $structure = array(
            'chart_data'   => $ordered_result,
            'chart_config' => array(
                'columns' => array(
                    array('type' => 'string', 'name' => 'Day'),
                    array('type' => 'number', 'name' => '< 20'),
                    array('type' => 'number', 'name' => '20 - 35'),
                    array('type' => 'number', 'name' => '> 35'),
                ),
                'title'   => 'Signups over time by age group',
                'type'    => 'LineChart',
            ),
        );

        return $structure
        ;
    }

    /**
     * method docblock
     *
     * @param
     *
     * @access protected
     * @return void
     */
    protected function getSignupNumbers($day)
    {
        $query = '
SELECT
    DATE(signed_up) AS date,
    COUNT(*) AS count
FROM
    deltagere AS d
WHERE
    DATE(signed_up) >= ?
    AND DATE(signed_up) <= NOW()
GROUP BY
    date
ORDER BY
    date
';

        return $this->db->query($query, $day);
    }

    /**
     * method docblock
     *
     * @param
     *
     * @access protected
     * @return void
     */
    protected function makeSignupOutput($ordered_result)
    {
        $structure = array(
            'chart_data'   => $ordered_result,
            'chart_config' => array(
                'columns' => array(
                    array('type' => 'string', 'name' => 'Day'),
                    array('type' => 'number', 'name' => 'Signups per day'),
                ),
                'title'   => 'Signups over time',
                'type'    => 'LineChart',
            ),
        );

        return $structure;
    }

    /**
     * returns array of signups per day for days in the signup period
     * with a running total
     *
     * @access public
     * @return array
     */
    public function getTotalSignupData()
    {
        $day    = date('Y-m-d', strtotime($this->config->get('con.signupstart')));
        $end    = $this->config->get('con.end');
        $today  = time() < strtotime($end) ? time() : strtotime($end);
        $result = $this->getSignupNumbers($day);

        $ordered_result = array();
        $index          = $total = 0;

        while (strtotime($day) < $today) {
            $ordered_result[$index] = array(
                0 => date('d-m-Y', strtotime($day)),
                1 => $total,
            );

            foreach ($result as $row) {
                if ($row['date'] == $day) {
                    $total                    += intval($row['count']);
                    $ordered_result[$index][1] = $total;
                    break;
                }
            }

            $day = date('Y-m-d', strtotime($day . ' + 1 day'));
            $index++;
        }

        return $this->makeSignupOutput($ordered_result);
    }

    /**
     * returns array of signups per user category
     *
     * @access public
     * @return array
     */
    public function getShareData()
    {
        $query = '
SELECT
    b.navn,
    COUNT(*) AS count
FROM
    deltagere AS d
    JOIN brugerkategorier AS b ON b.id = d.brugerkategori_id
GROUP BY
    b.navn
ORDER BY
    b.navn
';
        $result = $this->db->query($query);

        $ordered_result = array();
        foreach ($result as $row) {
            $ordered_result[] = array(
                $row['navn'],
                intval($row['count']),
            );
        }

        $structure = array(
            'chart_data'   => $ordered_result,
            'chart_config' => array(
                'columns' => array(
                    array('type' => 'string', 'name' => 'Kategori'),
                    array('type' => 'number', 'name' => 'Signups'),
                ),
                'title'   => 'Signups per type',
                'type'    => 'PieChart',
            ),
        );

        return $structure;
    }

    /**
     * returns array of meal reservations per type
     *
     * @access public
     * @return array
     */
    public function getFoodShareData()
    {
        $query = '
SELECT
    m.kategori,
    DATE(mt.dato) AS date,
    COUNT(*) AS count
FROM
    deltagere_madtider AS dm
    JOIN madtider AS mt ON mt.id = dm.madtid_id
    JOIN mad AS m ON m.id        = mt.mad_id
GROUP BY
    date,
    m.kategori
ORDER BY
    date,
    m.kategori
';

        $result = $this->db->query($query);

        $ordered_result = $temp_result = array();
        foreach ($result as $row) {
            $temp_result[$row['date']][$row['kategori']]= intval($row['count']);
        }

        $food_categories = $this->createEntity('Mad')->findAll();
        foreach ($temp_result as $date => $categories) {
            $row = array($date);
            foreach ($food_categories as $food_category) {
                $row[] = isset($categories[$food_category->kategori]) ? $categories[$food_category->kategori] : 0;
            }
            $ordered_result[] = $row;
        }

        $columns = array(array('type' => 'string', 'name' => 'Day'));
        foreach ($food_categories as $food_category) {
            $columns[] = array('type' => 'number', 'name' => $food_category->kategori);
        }

        $structure = array(
            'chart_data'   => $ordered_result,
            'chart_config' => array(
                'columns' => $columns,
                'title'   => 'Signups per type',
                'type'    => 'ColumnChart',
            ),
        );

        return $structure;
    }

    /**
     * method docblock
     *
     * @param
     *
     * @access public
     * @return void
     */
    public function getAgeGroupedAccommodationData()
    {
        $query = '
SELECT
    (YEAR(NOW()) - YEAR(birthdate)) AS age,
    COUNT(di1.deltager_id) AS entrance,
    COUNT(di2.deltager_id) AS accommodation
FROM
    deltagere AS d
    LEFT JOIN deltagere_indgang AS di1 ON (di1.deltager_id = d.id AND di1.indgang_id IN (1, 2))
    LEFT JOIN deltagere_indgang AS di2 ON (di2.deltager_id = d.id AND di2.indgang_id IN (8))
WHERE
    birthdate > "1900-00-00"
GROUP BY
    age
ORDER BY
    age
';
        $result = $this->db->query($query);

        $ordered_result = array();
        foreach ($result as $row) {
            if (empty($row['age'])) {
                continue;
            }

            $line = array(
                0 => $row['age'],
                1 => round($row['accommodation'] / $row['entrance'] * 100),
            );

            if ($line[1] === false) {
                continue;
            }

            $ordered_result[] = $line;
        }

        $structure = array(
            'chart_data'   => $ordered_result,
            'chart_config' => array(
                'columns' => array(
                    array('type' => 'string', 'name' => 'Age'),
                    array('type' => 'number', 'name' => 'Percent accommodation'),
                ),
                'title'   => 'Accommodation compared to entrance',
                'type'    => 'LineChart',
            ),
        );

        return $structure;
    }
}
