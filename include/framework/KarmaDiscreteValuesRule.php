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
 * PHP version 5
 *
 * sets up the environment, with various defines
 * contains autoload function too
 *
 * @category  Infosys
 * @package   Framework
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2015 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

/**
 * rule for calculating ranges in karma
 *
 * @category Infosys
 * @package  Framework
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class KarmaDiscreteValuesRule implements KarmaRule
{
    /**
     * type of registration to match
     *
     * @var string
     */
    private $type;

    /**
     * match of type
     *
     * @var int
     */
    private $match;

    /**
     * the value of the rule
     *
     * @var array
     */
    private $values;

    /**
     * public constructor
     *
     * @param string $type   Type to match
     * @param int    $match  Sub-type to match
     * @param array  $values Values for discrete matches
     *
     * @access public
     */
    public function __construct($type, $match, array $values)
    {
        $this->type   = $type;
        $this->match  = $match;
        $this->values = $values;
    }

    /**
     * calculates the rule value for the participant
     *
     * @param array $data Participant data
     *
     * @access public
     * @return float
     */
    public function calculate(array $data)
    {
        if (empty($data[$this->type])) {
            return 0;
        }

        $match = $this->match;

        $filter = function ($x) use ($match) {
            return $x == $match;
        };

        $matches = count(array_filter($data[$this->type], $filter));

        if ($matches === 0) {
            return 0;
        }

        if (isset($this->values[$matches])) {
            return $this->values[$matches];
        }

        foreach ($this->values as $match => $value) {
            if ($match > $matches) {
                return $value;
            }

        }

        return $value;
    }
}
