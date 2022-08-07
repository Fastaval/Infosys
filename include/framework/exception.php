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
 * PHP version 5
 *
 * @category  Infosys
 * @package   Framework
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2009-2012 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */


/**
 * base exception class
 *
 * @category Infosys
 * @package  Framework
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class FrameworkException extends Exception
{
    /**
     * Log object, injected in bootstrap
     *
     * @var Log
     */
    protected static $log;

    /**
     * takes care of logging exception data
     *
     * @access public
     * @return void
     */
    public function logException()
    {
        if (!empty(self::$log)) {
            try {
                $previous = method_exists($this, 'getPrevious') ? $this->getPrevious() : null;
                self::$log->logToFile($this->formatError($this->getMessage(), $this->getCode(), $previous));
            } catch (FrameworkException $e) {
            }
        } else {
            echo "<!-- exception not logged - log not instantiated
            {$this->getMessage()}
            -->";
        }
    }

    /**
     * sets the log object, to be used for logging errors
     *
     * @param Log $log Log object
     *
     * @access public
     * @return void
     */
    public static function setLog(Log $log)
    {
        self::$log = $log;
    }

    /**
     * makes a nice text version of the exception for loggin
     *
     * @param string    $message  Message passed to exception
     * @param int       $code     Code passed to exception
     * @param Exception $previous Previous exception if any
     *
     * @access protected
     * @return string
     */
    protected function formatError($message, $code, $previous)
    {
        $type = get_class($this);
        $time = date('Y-m-d H:i:s');
        $prev = $previous ? get_class($previous) : '';
        $req  = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

        $text = <<<TXT
Exception type {$type} occurred! Info:
Message: {$message}
Code: {$code}
Previous exception: {$prev}
File: {$this->getFile()}
Line: {$this->getLine()}
Time: {$time}
Request: {$req}

Trace:
TXT;
        foreach ($this->getTrace() as $i => $trace) {
            $text .= "* Step {$i}:" . PHP_EOL;
            foreach ($trace as $key => $value) {
                if (is_array($value)) {
                    if (is_object($key)) {
                        $key = get_class($key);
                    }

                    $text .= "  * {$key} = {$this->flattenArray($value)}" . PHP_EOL;
                } else {
                    $text .= "  * {$key} = {$value}" . PHP_EOL;
                }
            }

        }

        return $text;
    }

    /**
     * recursive function to flatten an array and output contents as string
     *
     * @param array $array Array to flatten
     *
     * @access protected
     * @return string
     */
    protected function flattenArray($array)
    {
        $store = array();
        foreach ($array as $item) {
            if (is_scalar($item)) {
                $store[] = $item;
            } elseif (is_object($item)) {
                $store[] = get_class($item);
            } elseif (is_array($item)) {
                $store[] = "array" . $this->flattenArray($item);
            } else {
                $store[] = gettype($item);
            }
        }

        return "(" . implode(' - ', $store) . ")";
    }
}
