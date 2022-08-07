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
     * Handles requests for the framework
     * decides which controller should be instantiated and which method
     *
     * @package    Framework
     * @author     Peter Lind <peter.e.lind@gmail.com>
     */
class RequestHandler
{
    /**
     * Request object, containing request vars
     *
     * @var Request
     */
    protected $request;

    /**
     * Config object
     *
     * @var Config
     */
    protected $config;

    /**
     * DIC object
     *
     * @var DIC
     */
    protected $dic;

    /**
     * setting up base variables
     *
     * @access public
     * @return void
     */
    public function __construct(Request $request, Config $config, DIC $dic)
    {
        $this->request = $request;
        $this->config  = $config;
        $this->dic     = $dic;
    }

    /**
     * Determines which controller should be instantiated and which to call
     *
     * @throws FrameworkException
     * @access public
     * @return void
     */
    public function handleRequest()
    {
        $request = $this->request;
        $match   = $request->getRoute();

        if (empty($match)) {
            throw new FrameworkException("No matching route found for {$request->getPath()}.");
        }

        if (empty($match['controller']) || empty($match['method'])) {
            throw new FrameworkException("Route for {$request->getPath()} lacks controller/method info.");
        }

        // try to see if the file of the requested controller exists
        if (!$this->dic->get('Autoloader')->checkClassFile($match['controller'] . "Controller", CONTROLLER_FOLDER)) {
            throw new FrameworkException("{$match['controller']} could not be found. Route from {$request->getPath()}.");
        }

        // instantiate controller, and see if the requested method exists
        $controller = $match['controller'] . "Controller";
        $method     = $match['method'];

        $page = $this->dic->get('Page');

        $controller = new $controller($match, $this->config, $this->dic);

        // if the indicated method doesn't exist, throw arms up in air and wail
        if (!method_exists($controller, $method)) {
            throw new FrameworkException("Method {$match['method']} of controller {$match['controller']} could not be found. Route from {$request->getPath()}.");
        }

        $page->setController($controller);
        $page->setTemplate($method);

        // store the uri so controllers can get to it
        $session             = $this->dic->get('Session');
        $session->RequestURI = $request->getPath();
        $session->Route      = $match;

        $this->handleRunHooks($controller, $method, true);

        call_user_func(array($controller, $method));

        $this->handleRunHooks($controller, $method, false);

        $this->dic->get('Layout')->render();
    }
    
    /**
     * checks the controller for any prerun hooks and executes them if they should be
     *
     * @param object $controller The controller that will be run as per the request
     * @param string $method     The method of the controller that should be run
     * @param bool   $pre        Whether to run pre or post run hooks
     *
     * @access protected
     * @return void
     */
    protected function handleRunHooks(Controller $controller, $method, $pre)
    {
        $hooks = $controller->getRunHooks($pre);
        if (empty($hooks)) {
            return;
        }

        foreach ($hooks as $hook) {
            $process     = $hook['method'];
            $methodfound = false;
            if (!empty($hook['methodlist']) && in_array($method, $hook['methodlist'])) {
                $methodfound = true;
            }

            if ((!$methodfound && $hook['exclusive']) || ($methodfound && !$hook['exclusive'] )) {
                call_user_func(array($controller, $process));
            }
        }
    }

}
