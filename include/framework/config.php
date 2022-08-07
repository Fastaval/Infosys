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
 * PHP version 5.3+
 *
 * @category  Infosys
 * @package   Framework
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2009-2012 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

/**
 * deals with config values
 *
 * @category  Infosys
 * @package   Framework
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2009-2012 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */
abstract class Config
{
    /**
     * holds parsed config data
     *
     * @var array
     */
    protected $parsed_data = array();

    /**
     * flag indicating that config file needs setup
     *
     * @var boolean
     */
    protected $setup_required = false;

    /**
     * returns a value if it's available
     *
     * @param string $key Name of value to retrieve
     *
     * @throws FrameworkException
     * @access public
     * @return mixed
     */
    public function get($key)
    {
        if ($this->setup_required) {
            throw new FrameworkException('Config file not available, cannot supply config data');
        }

        if (isset($this->parsed_data[$key])) {
            return $this->parsed_data[$key];
        }

        return null;
    }

    /**
     * returns true if the config file is missing or invalid
     * and so needs to be set up
     *
     * @access public
     * @return bool
     */
    public function isSetupRequired()
    {
        return $this->setup_required;
    }
}
