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
     * stores request data
     *
     * @package    Framework
     * @author     Peter Lind <peter.e.lind@gmail.com>
     */
class Request
{

    /**
     * stores the current request, minus any base dir used
     *
     * @var string
     */
    protected static $path;

    /**
     * holds request variables from $_POST
     *
     * @var RequestVars
     */
    protected static $post = null;

    /**
     * holds request variables from $_GET
     *
     * @var RequestVars
     */
    protected static $get = null;

    /**
     * holds variables from $_SERVER
     *
     * @var RequestVars
     */
    protected static $server = null;

    /**
     * stores a Routes object, for matching urls to request routes
     *
     * @var Routes
     */
    protected $routes;

    /**
     * Config object
     *
     * @var Config
     */
    protected $config;

    /**
     * object constructor
     *
     * @access public
     * @return void
     */
    public function __construct(Routes $routes, Config $config)
    {
        $this->config = $config;
        $this->routes = $routes;

        if (empty(self::$path) && !empty($_SERVER['REQUEST_METHOD']))
        {
            if ($_SERVER['REQUEST_METHOD'] == 'GET')
            {
                self::$get = new RequestVars($_GET);
            }
            else if ($_SERVER['REQUEST_METHOD'] == 'POST')
            {
                self::$post = new RequestVars($_POST);
            }
            self::$server = new RequestVars($_SERVER);
        }
        $this->getPath();
    }

    /**
     * magic get function, returns only the post or get objects
     *
     * @param string $key - name of var to fetch
     *
     * @access public
     * @return mixed
     */
    public function __get($key)
    {
        switch(strtolower($key))
        {
            case 'post':
                return self::$post;
            case 'get':
                return self::$get;
            case 'server':
                return self::$server;
            case 'routes':
                return $this->routes;
            default:
                return null;
        }
    }

    /**
     * check whether the current request is a post or not
     *
     * @access public
     * @return bool
     */
    public function isPost()
    {
        return is_object(self::$post) ? true : false;
    }


    /**
     * check whether the current request is a post or not
     *
     * @access public
     * @return bool
     */
    public function isGet()
    {
        return is_object(self::$get) ? true : false;
    }

    /**
     * returns the path requested
     *
     * @access public
     * @return string
     */
    public function getPath()
    {
        if (empty(self::$path))
        {
            if (!isset($_SERVER['REQUEST_URI']))
            {
                $request = '';
            }
            else
            {
                $request = $_SERVER['REQUEST_URI'];
                if (strlen($this->config->get('app.public_uri')) > 0 && $this->config->get('app.public_uri') != '/')
                {
                    $get_parts = explode('?', $request);
                    $uri = $get_parts[0];
                    $parts = explode($this->config->get('app.public_uri'), $uri);
                    $uri = ((count($parts) > 1) ? $parts[1] : $uri);
                    $request = $uri;
                }
                if (substr($request, 0, 1) == '/')
                {
                    $request = substr($request, 1);
                }
            }
            self::$path = $request;
        }
        return self::$path;
    }

    /**
     * gets the route for the current request from a Routes object
     *
     * @access public
     * @return string
     */
    public function getRoute()
    {
        return $this->routes->matchRoute($this->getPath());
    }
}
