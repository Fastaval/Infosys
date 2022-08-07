<?php
/**
 * Copyright (C) 2014 Peter Lind
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
 * @copyright 2014 Peter Lind
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
class LogSender extends SMSSender
{
    /**
     * store for messages to be sent
     *
     * var array
     */
    private $messages = array();

    /**
     * dummy override for safety check, to get all possible messages
     * even if not in sending period
     *
     * @access public
     * @return bool
     */
    public function safetyCheck()
    {
        return true;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function clearMessages()
    {
        $this->messages = array();
    }

    /**
     * sends a message to a recipient
     *
     * @param DBObject $participant Participant to send to
     * @param string   $number      Number to send to
     * @param string   $message     Message to send
     *
     * @throws FrameworkException
     * @access public
     * @return void
     */
    public function sendMessage(DBObject $participant, $number, $message)
    {
        $this->messages[] = array(
            'url'         => $this->createUrl($participant, $number, $message),
            'number'      => $number,
            'message'     => $message,
            'participant' => $participant,
        );
    }
}
