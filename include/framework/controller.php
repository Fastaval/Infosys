<?php
/**
 * Copyright (C) 2009-2012  Peter Lind
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
 * base class for all page controllers
 *
 * @category Infosys
 * @package  Framework
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class Controller extends Common
{

    /**
     * Holds the model object of the MVC triad
     *
     * @var object
     */
    protected $model;


    /**
     * stores vars from the called route, if any are present
     *
     * @var array
     */
    protected $vars;

    /**
     * stores the called route, with info controller and method called
     *
     * @var array
     */
    protected $route;

    /**
     * stores the layout that the controller/view uses
     *
     * @var object
     */
    protected $layout_object;

    /**
     * page object used for outputting stuff
     *
     * @var Page
     */
    protected $page;

    /**
     * Holds a user object representing the current user
     *
     * @var object
     */
    protected $user;
    
    /**
     * db connection object
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
     * DIC object
     *
     * @var DIC
     */
    protected $dic;
    
    /**
     * This is the default method called of a controller
     *
     * @access public
     * @return void
     */
    public function main()
    {
    }

    /**
     * base constructor for all controllers
     *
     * @param array  $route  Array of vars from the Routes object
     * @param Config $config Config object
     * @param DIC    $dic    DI container
     *
     * @access public
     * @return void
     */
    public function __construct(array $route, Config $config, DIC $dic)
    {
        if (!empty($route['vars'])) {
            $this->vars = $route['vars'];
        }

        $this->route  = $route;
        $this->page   = $dic->get('Page');
        $this->db     = $dic->get('DB');
        $this->routes = $dic->get('Routes');
        $this->config = $config;

        $this->layout_object = $dic->get('Layout');

        $classname   = substr(get_class($this), 0, strpos(get_class($this), "Controller"));
        $modelname   = $classname . 'Model';
        $this->model = new $modelname($dic->get('DB'), $this->config, $dic);

        $this->layout_object->user = $this->model->getLoggedInUser();

        $this->dic   = $dic;
    }


    /**
     * Loads a user object, checks privilege level and redirects if the user fails the check
     *
     * @param integer $privilege privilege level to check for
     * @param string  $method    method to redirect to
     *
     * @access protected
     * @return void
     */
    protected function redirectIfNoPrivilege($privilege, $method)
    {
        if (!($user = $this->model->getLoggedInUser()) || !$user->hasPrivilege($privilege)) {
            $this->get('Messages')->addError('Du har ikke adgang til denne side');
            call_user_method($method, $this);
        }
    }

    /**
     * does a hard redirect using header
     *
     * @param string $url - url to redirect to
     *
     * @access public
     * @return void
     */
    public function hardRedirect($url)
    {
        header("HTTP/1.0 303 GO");
        header("Location: {$url}");
        exit;
    }

    /**
     * wrapper for the messages object's addSuccess method - to add easy setting of messages for controllers
     *
     * @param string $message - message to set
     *
     * @access protected
     * @return bool
     */
    protected function successMessage($message)
    {
        $this->dic->get('Messages')->addSuccess($message);
    }

    /**
     * wrapper for the messages object's addError method - to add easy setting of messages for controllers
     *
     * @param string $message Message to set
     *
     * @access protected
     * @return bool
     */
    protected function errorMessage($message)
    {
        $this->dic->get('Messages')->addError($message);
    }

    /**
     * returns an array of pre-run hooks that should be executed
     * before the requested method of the controller is called
     *
     * @param bool $pre Whether to return pre or post run hooks
     *
     * @access public
     * @return array
     */
    public function getRunHooks($pre)
    {
        if ($pre) {
            return ((!empty($this->prerun_hooks)) ? $this->prerun_hooks : array());
        } else {
            return ((!empty($this->postrun_hooks)) ? $this->postrun_hooks : array());
        }
    }

    /**
     * checks that a user is logged in and has access to the called controller & method
     *
     * @access public
     * @return void
     */
    public function checkUser()
    {
        if (!($user = $this->model->getLoggedInUser())) {
            $this->hardRedirect($this->url('login_page'));
        }

        if (!$user->canAccess(get_class($this), $this->route['method'])) {
            $this->hardRedirect($this->url('no_access'));
        }

        $this->refreshSession();
    }

    /**
     * sets the expiry date of the session cookie
     * to now + 1 hour
     *
     * @access public
     * @return void
     */
    public function refreshSession()
    {
        setCookie(session_name(), session_id(), time() + 3600, '/');
    }

    /**
     * sets headers for outputting JSON / plain text
     *
     * @access protected
     * @return void
     */
    protected function ajaxHeader()
    {
        header('Content-Type: text/plain; encoding: UTF-8');
    }

    /**
     * returns a list of users
     *
     * @access public
     * @return void
     */
    public function ajaxList()
    {
        $get = $this->page->request->get;
        list($total_length, $result_length, $result) = $this->model->getAjaxListData($get);

        $output = array(
            'sEcho' => intval($get->sEcho),
            'iTotalRecords' => $total_length,
            'iTotalDisplayRecords' => $result_length,
            'aaData' => $result,
        );

        header('HTTP/1.1 200 done');
        header('Content-Type: text/plain; charset=UTF-8');
        echo json_encode($output);

        exit;
    }

    /**
     * Returns data as a csv file
     * 
     * @access public
     * @return void
     */
    public function returnCSV($data, $filename = "infosys") {
        header('Content-Type: text/csv;charset=utf-8');
        header('Content-Disposition: attachment;filename="'.$filename.'.csv"');
        header('Cache-Control: max-age=0');
        
        echo chr(0xEF).chr(0xBB).chr(0xBF); // UTF8 BOM
        foreach($data as $row) {
            foreach($row as $cell) {
                echo "\"$cell\";";
            }
            echo "\n";
        }
    }

    /**
     * outputs json data and sets headers accordingly
     *
     * @param string $data        Data to output
     * @param string $http_status HTTP status code
     *
     * @access protected
     * @return void
     */
    protected function jsonOutput($data, $http_status = '200', $content_type = 'text/plain') {
        if (!is_string($data)) {
            $data = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT );
        }
        header('Status: ' . $http_status);
        header('Content-Type: ' . $content_type . '; charset=UTF-8');
        header('Content-Length: ' . strlen($data));
        echo $data;
        exit;
    }
}
