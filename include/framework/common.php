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
 * contains common methods for page_controller, page_view and page_model
 *
 * @category Infosys
 * @package  Framework
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class Common
{
    /**
     * instance of DIC object
     *
     * @var DIC
     */
    protected $dic;

    /**
     * wrapper for Log object - logs messages to the db
     *
     * @param string $message Message to store
     * @param string $type    Which kind of message it is
     * @param object $user    User entity
     *
     * @access protected
     * @return bool
     */
    protected function log($message, $type, $user)
    {
        $user_id = ((!is_object($user) || !$user->isLoaded()) ? 0 : $user->id);
        return $this->dic->get('Log')->logToDB($message, $type, $user_id);
    }

    /**
     * wrapper for Log object - logs messages to the db
     *
     * @param string $message Message to store
     * @param string $type    Which kind of message it is
     * @param object $user    User entity
     *
     * @access protected
     * @return bool
     */
    protected function fileLog($message)
    {
        return $this->dic->get('Log')->logToFile($message);
    }

    /**
     * wrapper for the session object - sets a session variable
     *
     * @param string $var Variable to set
     * @param mixed  $val Value to set variable to
     *
     * @access protected
     * @return bool
     */
    protected function sessionSet($var, $val)
    {
        $this->dic->get('Session')->$var = $val;
    }

    /** 
     * wrapper for a call to Routes::url
     *
     * @param string $route Route to output
     * @param array  $vars  Vars to set in the route
     *
     * @access protected
     * @return string
     */
    protected function url($route, $vars = array())
    {
        return $this->dic->get('Routes')->url($route, $vars);
    }

    /**
     * replaces short output names from date() with long Danish names
     *
     * @param string $string String with names to replace
     *
     * @access protected
     * @return string
     */
    protected function replaceDayNames($string)
    {
        return str_replace(array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'), array('Mandag', 'Tirsdag', 'Onsdag', 'Torsdag', 'Fredag', 'Lørdag', 'Søndag'), $string);
    }
    
    /**
     * returns a string that works as a URI to images
     *
     * @param string $filename Filename of image
     *
     * @access protected
     * @return string
     */
    protected function imgLink($filename)
    {
        return $this->config->get('app.public_uri') . "img/{$filename}";
    }

    /**
     * returns a string that works as a URI to javascript files 
     *
     * @param string $filename Filename of image
     *
     * @access protected
     * @return string
     */
    protected function JSLink($filename)
    {
        return $this->config->get('app.public_uri') . "js/{$filename}";
    }

    /**
     * extracts ids (or another given field) from an array of objects
     *
     * @param array  $array Array of objects
     * @param string $field Optional field to use
     *
     * @access protected
     * @return array
     */
    protected function extractIds($array, $field = null)
    {
        if (!is_array($array)) {
            return array();
        }

        $field  = (($field) ? $field : 'id');
        $return = array();

        foreach ($array as $a) {
            $return[] = $a->$field;
        }

        return $return;
    }
}
