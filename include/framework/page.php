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
 * @package    Framework
 * @author     Peter Lind <peter.e.lind@gmail.com>
 * @copyright  2009 Peter Lind
 * @license    http://www.gnu.org/licenses/gpl.html GPL 3
 * @link       http://www.github.com/Fake51/Infosys
 */

/**
 * generic class for templates in the MVC
 *
 * @package    Framework
 * @author     Peter Lind <peter.e.lind@gmail.com>
 */
class Page
{

    /**
     * current status code
     *
     * @var integer
     */
    private $status_code = 200;

    /**
     * current status code message
     *
     * @var string
     */
    private $status_message = 'Served';

    /**
     * stores the layout object that things are output into
     *
     * @var Layout
     */
    private $layout;

    /**
     * headers to send upon output
     *
     * @var array
     */
    private $headers = [];

    /**
     * stores the layout file that will frame everything
     *
     * @var string
     */
    public $layout_template = 'default.phtml';

    /**
     * public storage space
     *
     * @var array
     */
    private $storage = array();

    /**
     * holds the request object
     *
     * @var Request
     */
    private $request;

    /**
     * names of scripts to include in page head
     *
     * @var array
     */
    private $earlyload_js = array(
        'common.js',
    );

    /**
     * names of scripts to include at end of page
     *
     * @var array
     */
    private $lateload_js = array();

    /**
     * css-filename => media type array
     * for including css files in the layout
     *
     * @var array
     */
    private $included_css = array();

    /**
     * Controller of the MVC triad
     *
     * @var object
     */
    private $controller;

    /**
     * name of template to use
     *
     * @var string
     */
    private $template;

    /**
     * contains a copy of a ViewHelper object
     *
     * @var object
     */
    private $viewhelper;

    /**
     * Messages object
     *
     * @var Messages
     */
    private $messages;

    /**
     * web path to public folders
     *
     * @var string
     */
    private $public_uri;

    /**
     * common magic get function, to grab things from the storage space of the classes
     *
     * @param string $var Name of variable to get
     *
     * @access public
     * @return mixed
     */
    public function __get($var)
    {
        if (strtolower($var) == 'request') {
            return $this->request;
        }

        return ((array_key_exists($var, $this->storage)) ? $this->storage[$var] : null);
    }

    /** 
     * Set a variable in the view, checks that the variable exists first
     *
     * @param string $varname Name of var to set
     * @param mixed  $value   Value to set variable to
     */
    public function __set($varname, $value)
    {   
        $this->storage[$varname] = $value;
    }   

    /**
     * checks the internal storage space for variables
     *
     * @param string $varname Variable to check for
     *
     * @access public
     * @return bool
     */
    public function __isset($varname)
    {
        if (isset($this->storage[$varname])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Constructor for the class
     *
     * @param Request  $request    Request object
     * @param Layout   $layout     Layout object
     * @param Messages $messages   Messages object
     * @param string   $public_uri url of the app
     *
     * @access public
     * @return void
     */
    public function __construct(Request $request, Layout $layout, Messages $messages, $public_uri, Config $config)
    {
        $this->messages   = $messages;
        $this->viewhelper = new ViewHelper($this, $config);
        $this->layout     = $layout;
        $this->request    = $request;
        $this->public_uri = $public_uri;
    }

    /**
     * wrapper for Layout::setBodyRendering
     *
     * @param  $flag Description
     *
     * @access public
     * @return self
     */
    public function setBodyRendering($flag)
    {
        $this->layout->setBodyRendering($flag);

        return $this;
    }

    /**
     * sets the controller that's rendering for the request
     *
     * @param Controller $controller Instance of Controller
     *
     * @access public
     * @return void
     */
    public function setController(Controller $controller)
    {
        $this->controller = $controller;
    }

    /**
     * sets the template to use
     *
     * @param string $template Name of template to use, should be in form 'method' or 'controller/method'
     *
     * @access public
     * @return $this
     */
    public function setTemplate($template)
    {
        if (strpos($template, '/') === false && !empty($template)) {
            preg_match('/^([A-Z][a-z]+)[A-Z]+.*/', get_class($this->controller), $match);
            $template = strtolower($match[1]) . '/' . $template;

        }

        $this->template = $template ? strtolower($template) . '.phtml' : '';

        return $this;
    }
    
    /**
     * returns all messages stored in the session - wrapped in html
     *
     * @access public
     * @return string
     */
    public function getMessagesHtml()
    {
        return $this->messages->getAllMessagesAsHtml();
    }

    /**
     * returns the output of the template specified in setTemplate
     * a template doesn't have to be specified - if none are, then there's just no output
     *
     * @throws FrameworkException
     * @access public
     * @return string
     */
    public function render()
    {
        $output = '';

        if (!empty($this->template)) {
            if (!is_file(TEMPLATE_FOLDER . $this->template)) {
                throw new FrameworkException("Template {$this->template} doesn't exist");
            }

            try {
                ob_start();
                require TEMPLATE_FOLDER . $this->template;
                $output = ob_get_contents();
                ob_end_clean();
            } catch (Exception $e) {
                throw new FrameworkException("Exception caught while rendering a template ({$this->template}). Message: {$e->getMessage()}");
            }
        }

        return $output;
    }

    /**
     * generates a string that contains a html select element
     *
     * @param string $name     Name to use for select
     * @param array  $values   Values to be used as options in the select
     * @param string $selected The selected option, if any
     * @param string $class    Name of class to use for select, if any
     *
     * @access public
     * @return string
     */
    public function genSelect($name, $values, $selected = null, $class = null)
    {
        $class  = (($class) ? " class='{$class}'" : '');
        $string = "<select name='{$name}' id='{$name}'{$class}>";

        foreach ($values as $key => $value) {
            $sel     = (($value == $selected) ? ' selected' : '');
            $key     = ((is_string($key)) ? $key : $value);
            $string .= "<option value='{$value}'{$sel}>{$key}</option>";

        }

        $string .= "</select>\n";
        return $string;
    }


    /** 
     * wrapper for a call to Routes::url
     *
     * @param string $route Route to output
     * @param array  $vars  Vars to set in the route
     *
     * @access public
     * @return string
     */
    public function url($route, $vars = array()) {   
        return $this->request->routes->url($route, $vars);
    }

    public function setTitle($title) {
        $this->layout->title = $title;
        return $this;
    }

    /**
     * replaces short output names from date() with long Danish names
     *
     * @param string $string - string with names to replace
     *
     * @access public
     * @return string
     */
    public function replaceDayNames($string)
    {
        return str_replace(
            array('Mon','Tue','Wed','Thu','Fri','Sat','Sun'),
            array('Mandag','Tirsdag','Onsdag','Torsdag','Fredag','Lørdag','Søndag'),
            $string
        );
    }
    
    /**
     * returns a string that works as a URI to images
     *
     * @param string $filename Filename of image
     *
     * @access public
     * @return string
     */
    public function imgLink($filename)
    {
        return $this->public_uri . "img/{$filename}";
    }

    /**
     * returns a string that works as a URI to javascript files 
     *
     * @param string $filename Filename of image
     *
     * @access public
     * @return string
     */
    public function JSLink($filename)
    {
        return $this->public_uri . staticLink("js/{$filename}");
    }

    /**
     * sets up a javascript file for inclusion at the end of the page
     *
     * @param string $filename Javascript to include
     *
     * @access public
     * @return void
     */
    public function registerLateLoadJS($filename)
    {
        if (is_file($this->getPublicPath() . 'js/' . $filename)) {
            $this->lateload_js[] = $filename;
        }
    }

    /**
     * sets up a javascript file for inclusion in the page head
     *
     * @param string $filename Javascript to include
     *
     * @access public
     * @return void
     */
    public function registerEarlyLoadJS($filename)
    {
        if (is_file($this->getPublicPath() . 'js/' . $filename)) {
            $this->earlyload_js[] = $filename;
        }
    }

    /**
     * clears the early load js stack
     *
     * @access public
     * @return void
     */
    public function clearEarlyLoadJs()
    {
        $this->earlyload_js = [];
    }

    /**
     * returns the javascript files that should be loaded in the page head
     *
     * @access public
     * @return array
     */
    public function getEarlyLoadJS()
    {
        return $this->earlyload_js;
    }

    /**
     * returns the javascript files that should be loaded in the page head
     *
     * @access public
     * @return array
     */
    public function getLateLoadJS()
    {
        return $this->lateload_js;
    }

    /**
     * extracts ids (or another given field) from an array of objects
     *
     * @param array  $array Array of objects
     * @param string $field Optional field to use
     *
     * @access public
     * @return array
     */
    public function extractIds($array, $field = null) {
        if (!is_array($array)) {
            return array();
        }

        $field = (($field) ? $field : 'id');
        $return = array();
        foreach ($array as $a) {
            $return[] = $a->$field;
        }

        return $return;
    }

    /**
     * handles a shortcoming of PHPTAL: the inability to get a var dynamically
     *
     * param object $object Object to get property from
     * param string $var    Name of property to return
     *
     * @access public
     * @return mixed
     */
    public function getPropertyDynamically($object, $var) {
        if (is_object($object) && isset($object->$var)) {
            return $object->$var;
        }

        return false;
    }

    /**
     * returns the escaped form of a page variable
     *
     * @param string $var
     *
     * @access public
     * @return string
     */
    public function e($var)
    {
        if (is_string($var)) {
            return $this->$var ? htmlspecialchars($this->$var, ENT_QUOTES) : htmlspecialchars($var, ENT_QUOTES);
        }

        return '';
    }

    /**
     * returns all css files to be included
     * in the layout
     *
     * @access public
     * @return array
     */
    public function getIncludedCSS() {
        return $this->included_css;
    }

    /**
     * wrapper for call to Layout::includeCSS()
     *
     * @param string $filename File to include
     * @param string $media    Media type
     *
     * @access public
     * @return void
     */
    public function includeCSS($filename, $media = 'screen') {
        $this->included_css[$filename] = $media;
    }

    /**
     * calculates the path to the public folders
     *
     * @access public
     * @return string
     */
    public function getPublicPath()
    {
        return realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public') . DIRECTORY_SEPARATOR;
    }

    /**
     * sets a status code and message for HTTP headers
     *
     * @param int    $status  Status code
     * @param string $message Status message
     *
     * @access public
     * @return $this
     */
    public function setStatus($status, $message)
    {
        $this->status_code    = $status;
        $this->status_message = $message;

        return $this;
    }

    /**
     * sets a header for later output
     *
     * @param string $header Header to set
     * @param string $value  Value to set for header
     *
     * @access public
     * @return $this
     */
    public function setHeader($header, $value)
    {
        $this->headers[$header] = $value;

        return $this;
    }

    /**
     * outputs HTTP headers if possible
     *
     * @access public
     * @return $this
     */
    public function sendHeaders()
    {
        if (!headers_sent()) {
            header('HTTP/1.1 ' . intval($this->status_code) . ' ' . $this->status_message);

            foreach ($this->headers as $header => $value) {
                header($header . ': ' . $value);

            }

        }

        return $this;
    }
}
