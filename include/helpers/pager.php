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
 * PHP version 5.3+
 *
 * @category  Infosys
 * @package   Helpers
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2009-2012 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

/**
 * contains methods for displaying a generic pager
 *
 * @category Infosys
 * @package  Helpers
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class Pager extends Common
{
    /**
     * current page number
     *
     * @var int
     */
    protected $page;

    /**
     * total number of pages in pager
     *
     * @var int
     */
    protected $pages;

    /**
     * name of route to use for generating page links
     *
     * @var string
     */
    protected $route;

    /**
     * public storage space
     *
     * @var array
     */
    protected $storage = array();

    /**
     * public constructor
     *
     * @param DIC $dic DIC object
     *
     * @access public
     * @return void
     */
    public function __construct(DIC $dic)
    {
        $this->dic = $dic;
    }

    /**
     * common magic get function, to grab things from the storage space of the classes
     *
     * @param string $var - name of variable to get
     * @access public
     * @return mixed
     */
    public function __get($var)
    {   
        return ((array_key_exists($var, $this->storage)) ? $this->storage[$var] : null);
    }   

    /** 
     * Set a variable in the view, checks that the variable exists first
     *
     * @param string name of var to set
     * @param mixed value to set variable to
     */
    public function __set($varname, $value)
    {   
        $this->storage[$varname] = $value;
    }   

    /**
     * sets the current page for the pager
     *
     * @param int $pages
     * @access public
     */
    public function setPage($page)
    {
        $this->page = intval($page);
    }

    /**
     * sets the total page number for the pager
     *
     * @param int $pages
     * @access public
     */
    public function setPages($pages)
    {
        $this->pages = intval($pages);
    }

    /**
     * sets the route for the pager
     *
     * @access public
     */
    public function setRoute($route)
    {
        $this->route = $route;
    }

    /**
     * renders the pager
     *
     * @access public
     */
    public function render()
    {
        if (!isset($this->page) || empty($this->pages) || empty($this->route))
        {
            return;
        }
        $first = (($this->page == 0) ? '' : "<a href='{$this->url($this->route, array('page'=>0))}'>&lt;&lt;</a>");
        $prev = (($this->page == 0) ? '' : "<a href='{$this->url($this->route, array('page'=>$this->page - 1))}'>&lt;</a>");
        $next = (($this->page == ($this->pages - 1)) ? '' : "<a href='{$this->url($this->route, array('page'=> $this->page + 1))}'>&gt;</a>");
        $last = (($this->page == ($this->pages - 1)) ? '' : "<a href='{$this->url($this->route, array('page'=> $this->pages - 1))}'>&gt;&gt;</a>");
        $lower_range = ((($this->page - 5) < 0) ? 0 : $this->page - 5);
        $upper_range = ((($this->page + 5) > ($this->pages - 1)) ? $this->pages -1 : $this->page + 5);
        echo <<<html
<div class='pager'>
    <div class='pager-box'>
        <span class='pager-sign-links'>{$first}</span>
        <span class='pager-sign-links'>{$prev}</span>
html;
        if ($lower_range > 0)
        {
            echo " <a href='{$this->url($this->route,array('page'=>0))}'>1</a> ";
            echo "...";
        }
        for ($i = $lower_range; $i <= $upper_range; $i++)
        {
            $count = $i +1;
            echo " <a href='{$this->url($this->route,array('page'=>$i))}'>{$count}</a> ";
        }
        if ($upper_range < ($this->pages - 1))
        {
            echo "...";
            echo " <a href='{$this->url($this->route,array('page'=>$this->pages - 1))}'>{$this->pages}</a> ";
        }
        echo <<<html
        <span class='pager-sign-links'>{$next}</span>
        <span class='pager-sign-links'>{$last}</span>
    </div>
    <div class='clearit'></div>
</div>
html;
    }

}
