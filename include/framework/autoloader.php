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
 * sets up the environment, with various defines
 * contains autoload function too
 *
 * @category  Infosys
 * @package   Framework
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2009-2012 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

/**
 * responsible for autoloading classes
 *
 * @category Infosys
 * @package  Framework
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class Autoloader
{
    /**
     * locations to check for class files
     *
     * @var array
     */
    protected $class_folders = array();

    /**
     * public constructor
     *
     * @param array $paths Paths to check for files
     *
     * @access public
     * @return void
     */
    public function __construct(array $paths)
    {
        $this->class_folders = $paths;
    }

    /**
     * main autoloader method
     *
     * @param string $class_name Name of class to lad
     *
     * @access public
     * @return void
     */
    public function autoloader($class_name)
    {
        if (strpos($class_name, 'Swift_') === 0) {
            return;
        }

        if (strpos($class_name, 'PHPUnit') === 0) {
            return;
        }

        if (strpos($class_name, 'Composer') === 0) {
            return;
        }

        $normalized_name = $this->normalizeClass($class_name);

        foreach ($this->class_folders as $folder) {
            if (is_file($folder . $normalized_name . '.php')) {
                include $folder . $normalized_name . '.php';
                return true;
            }

            if (is_file($folder . $class_name . '.php')) {
                include $folder . $class_name . '.php';
                return true;
            }

        }
    }

    /**
     * normalize a class name to match naming of files
     * all files are lowercase - whenever camel notation is used, it's converted to lowercase with an underscore put in
     * hence, RequestHandler becomes request_handler. Uppercase letters in a row are treated as one, like DBObject -> dbobject
     *
     * @param string $class_name Class name to normalize
     *
     * @access public
     * @return string
     */
    public function normalizeClass($class_name)
    {
        while (strtolower($class_name) != $class_name) {
            preg_match('/([A-Z]+)/', $class_name, $match);
            $end_string   = strstr($class_name, $match[0]);
            $start_string = "";

            if (strlen($end_string) != strlen($class_name)) {
                $start_string = substr($class_name, 0, (strlen($class_name) - strlen($end_string))) . "_";
            }

            $class_name = $start_string . strtolower($match[1]) . substr($end_string, strlen($match[1]));
        }

        return $class_name;
    }

    /**
     * check if the file for a given class exists - checks in the for possible locations for classes
     *
     * @param string $class_name      Name of the class whose file to check for
     * @param string $specific_folder A specific folder to restrict the search to
     *
     * @access public
     * @return bool
     */
    public function checkClassFile($class_name, $specific_folder = "")
    {
        $class_name = $this->normalizeClass($class_name);

        // if a folder was specified, check there
        if ($specific_folder != "" && in_array($specific_folder, $this->class_folders)) {
            if (file_exists($specific_folder . $class_name . ".php")) {
                return true;
            } else {
                return false;
            }
        } else {
            // otherwise, check in all available folders
            $return = false;
            foreach ($this->class_folders as $folder) {
                if (file_exists($folder . $class_name . ".php")) {
                    $return = true;
                }
            }

            return $return;
        }
    }
}
