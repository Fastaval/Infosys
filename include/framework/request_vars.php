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
     * @package    Framework
     * @author     Peter Lind <peter.e.lind@gmail.com>
     * @copyright  2009 Peter Lind
     * @license    http://www.gnu.org/licenses/gpl.html GPL 3
     * @link       http://www.github.com/Fake51/Infosys
     */

    /**
     * contains post or get vars
     *
     * @package    Framework
     * @author     Peter Lind <peter.e.lind@gmail.com>
     */
class RequestVars
{

    /**
     * public storage space
     *
     * @var array
     */
    private $storage = array();

    /**
     * common magic get function, to grab things from the storage space of the classes
     *
     * @param string $var - name of variable to get
     * @access public
     * @return mixed
     */
    public function __get($var)
    {   
        return ((array_key_exists($var, $this->storage)) ? $this->storage[$var] : null);
    }   

    public function __isset($key)
    {
        return ((array_key_exists($key, $this->storage)) ? true : false);
    }

    /**
     * stores away all request vars handed to it
     *
     * @param array $vars - request vars
     *
     * @access public
     * @return void
     */
    public function __construct(Array $vars)
    {
        foreach ($vars as $key => $val)
        {
            $this->storage[$key] = $val;
        }
    }

    /**
     * returns the array of request vars
     *
     * @access public
     * @return array
     */
    public function getRequestVarArray()
    {
        return $this->storage;
    }
}
