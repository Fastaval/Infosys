<?php
/**
 * Copyright (C) 2015 Peter Lind
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
 * PHP version 5.3+
 *
 * @category  Infosys
 * @package   Framework
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2015 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

/**
 * requirements fulfilment interface
 *
 * @category Infosys
 * @package  Framework
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
interface AgeFulfilment extends Fulfilment
{
    /**
     * returns age as a float
     *
     * @param DateTime $at_time Optional time for comparison
     *
     * @access public
     * @return int
     */
    public function getAge(DateTime $at_time = null);

    /**
     * returns true if the instance is younger than the
     * provided limit
     *
     * @param int      $limit   Age limit to check against
     * @param DateTime $at_time Optional time for comparison
     *
     * @access public
     * @return bool
     */
    public function isYoungerThan($limit, DateTime $at_time = null);

    /**
     * returns true if the instance is older than or
     * the same age as the provided limit
     *
     * @param int      $limit   Age limit to check against
     * @param DateTime $at_time Optional time for comparison
     *
     * @access public
     * @return bool
     */
    public function isOlderThan($limit, DateTime $at_time = null);
}
