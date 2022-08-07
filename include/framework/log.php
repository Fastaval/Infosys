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
 * handles logging to file
 *
 * @package    Framework
 * @author     Peter Lind <peter.e.lind@gmail.com>
 */
class Log
{
    /**
     * copy of the db connection object
     *
     * @var DB
     */
    protected $db;

    /**
     * Config object
     *
     * @var Config
     */
    protected $config;

    /**
     * initializes the log object
     *
     * @param DB $db - db connection object
     *
     * @throws FrameworkException
     * @access public
     * @return void
     */
    public function __construct(DB $db, Config $config)
    {
        $this->db     = $db;
        $this->config = $config;
    }

    /**
     * Log a string to the log file
     *
     * @param string String to be written to log
     *
     * @access public
     * @return boolean
     */
    public function logToFile($message)
    {
        if (!($file = fopen(LOGS_FOLDER . $this->config->get('app.log_file'), "a"))) {
            throw new FrameworkException("Failed to init log");
        }

        if (empty($message)) {
            throw new FrameworkException("Message to log is empty");
        }

        fwrite($file, date("d-m-Y H:i:s") . ": " . $message . PHP_EOL . PHP_EOL);
        fclose($file);
        return true;
    }
    
    /**
     * saves a log message to the database
     *
     * @param string $message - message to save
     * @param string $type    - type of the message (login, etc)
     * @param int    $user_id
     *
     * @access public
     * @return bool
     */
    public function logToDB($message, $type, $user_id)
    {
        return $this->db->exec("INSERT INTO `log` (type, message, user_id, created) VALUES (?, ?, ?, NOW())", array($type, $message, $user_id)); 
    }
}
