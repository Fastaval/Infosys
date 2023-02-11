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
     * @package    MVC
     * @subpackage Entities
     * @author     Peter Lind <peter.e.lind@gmail.com>
     * @copyright  2009 Peter Lind
     * @license    http://www.gnu.org/licenses/gpl.html GPL 3
     * @link       http://www.github.com/Fake51/Infosys
     */

    /**
     * stores SMS logs
     *
     * @package MVC
     * @subpackage Entities
     */
class SMSLog extends DBObject
{

    /**
     * Name of database table
     *
     * @var string
     */
    protected $tablename = 'smslog';

    public function safetyCheck($num) {
        if ($this->getDB()->query("SELECT * FROM smslog AS sl JOIN messages as m ON sl.message_id = m.id WHERE sl.phone_number = ? AND m.send_time > NOW() - INTERVAL 10 MINUTE", $num)) {
            return false;
        }

        return true;
    }
}
