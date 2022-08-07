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
     * handles storing and displaying messages to the user across page-loads
     *
     * @package    Framework
     * @author     Peter Lind <peter.e.lind@gmail.com>
     */

class Messages
{

    /**
     * Instance of the Session singleton that handles session vars
     *
     * @var object
     */
    protected $session;

    /**
     * Holds the messages kept through a session
     *
     * @var array
     */
    protected static $messages;

    public function __construct(Session $session)
    {
        $this->session = $session;
        if (empty(self::$messages))
        {
            $messages = $session->MessageArray;
            if (!isset($messages['errors']))
            {
                $messages['errors'] = array();
            }
            if (!isset($messages['successes']))
            {
                $messages['successes'] = array();
            }
            self::$messages = $messages;
        }
    }

    /**
     * deletes all stored messages
     *
     * @access protected
     * @return void
     */
    protected function clearMessages()
    {
        self::$messages = array('errors' => array(), 'successes' => array());
        $this->session->delete('MessageArray');
    }

    /**
     * Adds a string to the array of error messages, fails if input param is not a string
     *
     * @param string message to store
     * @return boolean success or failure
     */
    public function addError($message)
    {
        if (is_string($message))
        {
            self::$messages['errors'][] = $message;
            $this->session->MessageArray = self::$messages;
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Adds a string to the array of success messages, fails if input param is not a string
     *
     * @param string message to store
     * @return boolean success or failure
     */
    public function addSuccess($message)
    {
        if (is_string($message))
        {
            self::$messages['successes'][] = $message;
            $this->session->MessageArray = self::$messages;
            return true;
        }
        else
        {
            return false;
        }
    
    }

    /**
     * Returns all stored messages as text, wiping them from the session
     *
     * @return string messages or an empty string
     */
    public function getAllMessagesAsText()
    {
        $outputstring = "";
        foreach (self::$messages as $msgtype => $msgarray)
        {
            if (!empty($msgarray))
            {
                $outputstring .= ucwords($msgtype) . ":";
            }
            foreach ($msgarray as $msg)
            {
                $outputstring .= htmlspecialchars($msg, ENT_QUOTES) . "\n\n";
            }
        
        }
        $this->clearMessages();
        return $outputstring;
    }

    /**
     * Returns all stored messages in HTML, and wipes them from the session
     *
     * @return string messages or an empty string
     */
    public function getAllMessagesAsHtml()
    {
        $outputstring = "";
        foreach (self::$messages as $msgtype => $msgarray)
        {
            if (!empty($msgarray))
            {
                $outputstring .= "<div class='message{$msgtype}'>";
            }
            foreach ($msgarray as $msg)
            {
                $outputstring .= htmlspecialchars($msg, ENT_QUOTES) . "<br />";
            }
            if (!empty($msgarray))
            {
                $outputstring .= "</div>";
            }
        
        }
        $this->clearMessages();
        return $outputstring;
    }

    /**
     * Returns all stored messages in raw form, and wipes them from the session
     *
     * @access public
     * @return string
     */
    public function getAllMessagesRaw()
    {
        $outputstring = "";
        foreach (self::$messages as $msgtype => $msgarray)
        {
            if (!empty($msgarray))
            {
                $outputstring .= ucwords($msgtype) . ":";
            }
            foreach ($msgarray as $msg)
            {
                $outputstring .= $msg . "\n\n";
            }
        }
        $this->clearMessages();
        return $outputstring;
    }
}
