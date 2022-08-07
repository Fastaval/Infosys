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
 * data model for log
 *
 * @category Infosys
 * @package  Models
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class LogModel extends Model
{

    /**
     * returns an array of log messages
     *
     * @access public
     * @return array
     */
    public function getLogMessages()
    {
        $query = 'SELECT log.id, log.type, log.message, IFNULL(u.user, "Not logged") AS user, created FROM log LEFT JOIN users AS u ON u.id = log.user_id ORDER BY log.id DESC LIMIT 10';

        if (!($result = $this->db->query($query))) {
            return array();
        }

        return $result;
    }

    /**
     * returns the total number of records in the log
     *
     * @access public
     * @return int
     */
    public function getLogRowCount()
    {
        $total_length = $this->db->query('SELECT COUNT(*) FROM log');
        return $total_length[0][0];
    }

    /**
     * fetches data for the log table to display
     *
     * @param RequestVars $get Object with get vars
     *
     * @access public
     * @return array
     */
    public function getAjaxListData(RequestVars $get)
    {
        $columns     = array('log.created', 'log.type', 'log.message', 'user');

        $limit = "";
        if (isset($get->iDisplayStart) && $get->iDisplayLength != '-1') {
            $limit = "LIMIT " . intval($get->iDisplayStart) . ", " .
            intval($get->iDisplayLength);
        }

        $order = '';
        if (isset($get->iSortCol_0)) {
            $order = "ORDER BY ";
            for ($i = 0, $max = intval($get->iSortingCols); $i < $max ; $i++ ) {
                if ($get->{'bSortable_' . intval($get->{'iSortCol_' . $i})} == "true" ) {
                    $sort_dir = strtolower($get->{'sSortDir_' . $i}) == 'desc' ? 'DESC' : 'ASC';
                    $order .= $columns[intval($get->{'iSortCol_' . $i})] . "
                        " . $sort_dir . ", ";
                }
            }
            
            $order = substr_replace($order, "", -2);
            if ($order == "ORDER BY") {
                $order = "";
            }
        }
    
        $where = '';
        if ($get->sSearch != "") {
            $where = "WHERE (";
            for ($i = 0, $max = count($columns); $i < $max; $i++) {
                $where .= $columns[$i] . " LIKE " . $this->db->sanitize('%' . $get->sSearch . '%') . " OR ";
            }

            $where = substr_replace($where, "", -3);
            $where .= ')';
        }
    
        for ($i = 0, $max = count($columns); $i < $max; $i++) {
            if ($get->{'bSearchable_' . $i} == "true" && $get->{'sSearch_' . $i} != '') {
                if ($where == "") {
                    $where = "WHERE ";
                } else {
                    $where .= " AND ";
                }

                $where .= $columns[$i] . " LIKE " . $this->db->sanitize('%' . $get->{'sSearch_' . $i} . '%') . " ";
            }
        }

        $query = "
            SELECT SQL_CALC_FOUND_ROWS created, type, message, IFNULL(u.user, 'Not logged') AS user
            FROM log
                LEFT JOIN users AS u on u.id = log.user_id
            {$where}
            {$order}
            {$limit}
        ";

        $result = $this->db->query($query);

        $query = 'SELECT FOUND_ROWS() AS rows';

        $result_length = $this->db->query($query);
        $result_length = $result_length[0][0];

        return array($this->getLogRowCount(), $result_length, $result);
    }
}
