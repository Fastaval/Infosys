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
     * handles session vars
     *
     * @package Framework
     */
class Session
{

    /**
     * stores all data from session
     *
     * @var array
     */
    protected static $session;

    /**
     * set up session state
     *
     * @param string $public_uri Url of app
     *
     * @access public
     * @return void
     */
    public function __construct($public_uri)
    {
        if (!isset(self::$session)) {
            session_set_cookie_params(7200, '/', rtrim(str_replace(['http://', 'https://'], '', $public_uri), '/'));
            if (@session_start()) {
                $session = array();
                foreach ($_SESSION as $key => $val) {
                    $session[$key] = $val;
                }

                self::$session = $session;
            } else {
                throw new FrameworkException('Could not start session');
            }
        }
    }

    /**
     * returns state of session
     *
     * @access public
     * @return bool
     */
    public function getState()
    {
        return isset(self::$session);
    }

    /**
     * saves the session variables to the $_SESSION global
     *
     * @access public
     * @return void
     */
    public function save()
    {
        if (!isset(self::$session)) {
            throw new FrameworkException('Session is not started');
        }

        $_SESSION = array();
        foreach (self::$session as $key => $val) {
            $_SESSION[$key] = $val;
        }
    }

    /**
     * Destroys the current session and unsets all session vars
     *
     * @access public
     * @return void
     */
    public function end()
    {
        if (isset(self::$session)) {
            setcookie(session_name(), "", time() - 42000, "/");
            $_SESSION = array();
            session_destroy();
            self::$session = null;
        } else {
            throw new FrameworkException('Session is not started');
        }
    }
    
    /**
     * Gets a session variable if it exists and the session is set
     *
     * @param string $varname Name of var to get
     *
     * @access public
     * @return mixed
     */
    public function __get($varname)
    {
        if (isset(self::$session) && in_array($varname, array_keys(self::$session))) {
            return self::$session[$varname];
        } else {
            return null;
        }
    }
    
    
    /**
     * Sets a session variable if the session is set
     *
     * @param string $varname Name of var to set
     * @param mixed  $value   Value to set var to
     *
     * @access public
     * @return void
     */
    public function __set($varname, $value)
    {
        if (isset(self::$session)) {
            self::$session[$varname] = $value;
            $this->save();
        }
    }

    /**
     * returns true if varname is set
     *
     * @param string $varname Variable to check for
     *
     * @access public
     * @return bool
     */
    public function __isset($varname)
    {
        return isset(self::$session) && in_array($varname, array_keys(self::$session)) ? true : false;
    }

    /**
     * Unsets a session variable if it exists and the session is set
     *
     * @param string $varname Name of var to unset
     *
     * @access public
     * @return bool
     */
    public function delete($varname)
    {
        if (isset(self::$session) && in_array($varname, array_keys(self::$session))) {
            self::$session[$varname] = null;
            unset(self::$session[$varname]);
            $this->save();
            return true;
        } else {
            return false;
        }
    }
}
