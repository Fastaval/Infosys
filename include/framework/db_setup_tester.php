<?php
/**
 * Copyright (C) 2015  Peter Lind
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
 * app class - setups up everything and sandboxes it
 *
 * @category Infosys
 * @package  Framework
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class DbSetupTester
{
    /**
     * array of testers to check db connections
     *
     * @var array
     */
    private $testers;

    /**
     * public constructor
     *
     * @param array $testers Db testers
     *
     * @access public
     */
    public function __construct(array $testers)
    {
        $this->testers = $testers;
    }

    /**
     * returns db types the system would be able to handle
     *
     * @access public
     * @return array
     */
    public function getDbTypes()
    {
        $types = [];

        foreach ($this->testers as $tester) {
            $types[$tester->getTypeValue()] = $tester->getTypeName();
        }

        return $types;
    }

    /**
     * returns arrays of db setup data elements
     *
     * @access public
     * @return array
     */
    public function getSetupData()
    {
        $data = [];

        foreach ($this->testers as $tester) {
            $type   = $tester->getTypeValue();

            $mapper = function ($x) use ($type) {
                $x['class'] = $type;

                return $x;
            };

            $data = array_merge($data, array_map($mapper, $tester->getConfigFields()));
        }

        return $data;
    }

    /**
     * runs the test through the db testers to see if the
     * config will work
     *
     * @param Config $config Config with settings
     *
     * @access public
     * @return string
     */
    public function testConfig(Config $config)
    {
        foreach ($this->testers as $tester) {
            if ($tester->getTypeValue() === $config->get('db.type')) {
                return $tester->testConfig($config);
            }
        }

        return 'DB type not recognized';
    }
}
