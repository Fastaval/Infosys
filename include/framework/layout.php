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
 * @package   Framework
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2009-2012 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

/**
 * base class for layout files - provides layout functionality
 *
 * @category Infosys
 * @package  Framework
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class Layout
{
    /**
     * whether the layout has been rendered or not
     *
     * @var bool
     */
    protected $render_state = false;

    /**
     * stores vars used for page layout, like title, keywords, etc
     *
     * @var array
     */
    protected $pagevars = array();

    /**
     * doctype to use for the current page
     *
     * @var string
     */
    protected $doctype;

    /**
     * html namespace to use for the current page
     *
     * @var string
     */
    protected $namespace;

    /**
     * encoding to use for the current page
     *
     * @var string
     */
    protected $encoding;

    /**
     * stores the content to be printed on the page
     *
     * @var string
     */
    protected $content;

    /**
     * Page instance, for output rendering
     *
     * @var Page
     */
    protected $page;

    /**
     * flag controlling if a http body is sent
     *
     * @var bool
     */
    private $skip_body_rendering;

    protected $routes;
    protected $config;

    /**
     * magic set, stores vars in _pagevars
     *
     * @param string @var - name to store variable under
     * @param mixed @val - variable to store
     * @access public
     */
    public function __set($var, $val)
    {
        $this->pagevars[$var] = $val;
    }

    /**
     * magic set, retrieves vars from _pagevars
     *
     * @param string @var - name to store variable under
     * @access public
     */
    public function __get($var) {
        return ((array_key_exists($var, $this->pagevars)) ? $this->pagevars[$var] : null);
    }

    /**
     * magic check if a given variable will be available
     *
     * @param string $var Variable name to check for
     *
     * @access public
     * @return bool
     */
    public function __isset($var) {
        return array_key_exists($var, $this->pagevars) ? true : false;
    }

    /**
     * public constructor
     *
     * @param Config $config Config object
     * @param Routes $routes Routes object
     *
     * @access public
     * @return void
     */
    public function __construct(Config $config, Routes $routes)
    {
        $this->config = $config;
        $this->routes = $routes;
    }

    /**
     * returns the set doctype, if any is set
     *
     * @access public
     * @return string
     */
    public function getDocType()
    {
        if (!empty($this->doctype)) {
            return $this->doctype;
        }

        return false;
    }

    /**
     * sets the doctype for the layout
     *
     * @param string $doctype
     * @access public
     * @return string
     */
    public function setDocType($doctype)
    {
        $this->doctype = $doctype;
    }

    /**
     * returns the set namespace, if any is set
     *
     * @access public
     * @return string
     */
    public function getNameSpace()
    {
        if (!empty($this->namespace)) {
            return $this->namespace;
        }

        return false;
    }

    /**
     * sets the namespace for the layout
     *
     * @param string $namespace
     * @access public
     * @return string
     */
    public function setNameSpace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * returns encoding for a page
     *
     * @access public
     * @return string
     */
    public function getEncoding()
    {
        return ((empty($this->encoding)) ? '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' : $this->encoding);
    }

    /**
     * sets the page encoding to use
     *
     * @param string $encoding - what encoding to use in output
     *
     * @access public
     * @return void
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
    }

    /**
     * sets the page instance to use for rendering
     *
     * @param Page $page - page instance to use
     *
     * @access public
     * @return void
     */
    public function setPage(Page $page)
    {
        $this->page = $page;
    }

    /**
     * return true if no body will be rendered
     *
     * @access public
     * @return bool
     */
    public function skipBodyRendering()
    {
        return $this->skip_body_rendering;
    }

    /**
     * sets the flag on body rendering
     *
     * @param bool $flag False to skip body rendering
     *
     * @access public
     * @return self
     */
    public function setBodyRendering($flag)
    {
        $this->skip_body_rendering = !$flag;

        return $this;
    }

    /**
     * renders the page, by outputing layout plus content
     *
     * @access public
     * @return void
     */
    public function render()
    {
        // only render things once per request
        if ($this->render_state == true) {
            return;
        }

        if (!is_file(LAYOUT_FOLDER . $this->page->layout_template)) {
            throw new FrameworkException("Specified layout file ({$this->page->layout_template}) does not exist");
        }

        $this->page->sendHeaders();

        if ($this->skipBodyRendering()) {
            return;
        }

        if (!$this->getDocType()) {
            $doctype = <<<XML
<!DOCTYPE html>
XML;
            $this->setDocType($doctype);
        }

        $this->content = $this->page->render();

        require LAYOUT_FOLDER . $this->page->layout_template;
        $this->render_state = true;
    }

    /**
     * outputs a link element that will include a css file
     *
     * @param string $url - relative uri of file to include
     * @param string $media - which media to aim at, defaults to screen
     * @access public
     */
    public function includeCSS($file, $media = 'screen')
    {
        return "<link rel='stylesheet' href='" . $this->config->get('app.public_uri') . staticLink("css/{$file}") . "' type='text/css' media='{$media}' />";
    }

    /**
     * outputs a conditional IE comment that will include a css file
     *
     * @param string $url - relative uri of file to include
     * @param string $version - which version of IE to target
     * @param string $media - which media to aim at, defaults to screen
     * @access public
     */
    public function includeIECSS($url, $version = false, $media = 'screen')
    {
        if (!$version) {
            $version = '';
        }

        return "<!--[if LTE IE {$version}]> <link rel='stylesheet' href='" . $this->config->get('app.public_uri') . staticLink("css/{$url}") . "' type='text/css' media='{$media}' /><![endif]-->";
    }

    /**
     * outputs html to include early load javascript
     *
     * @access protected
     * @return void
     */
    protected function renderEarlyLoadJS()
    {
        $return = '';
        foreach ($this->page->getEarlyLoadJS() as $js) {
            $return .= $this->includeJS($js) . PHP_EOL;
        }

        return $return;
    }


    /**
     * outputs html to include late load javascript
     *
     * @access protected
     * @return void
     */
    protected function renderLateLoadJS()
    {
        $return = '';
        foreach ($this->page->getLateLoadJS() as $js) {
            $return .= $this->includeJS($js) . PHP_EOL;
        }

        return $return;
    }

    /**
     * runs includeCSS for the previously registered CSS files
     *
     * @access public
     * @return string
     */
    protected function renderIncludedCSS() {
        $return = '';
        foreach ($this->page->getIncludedCSS() as $css => $media) {
            $return .= $this->includeCSS($css, $media) . PHP_EOL;
        }

        return $return;
    }

    /**
     * outputs a script element that will include a js file
     *
     * @param string $url - relative uri of file to include
     * @access public
     */
    public function includeJS($url)
    {
        return "<script src='" . $this->config->get('app.public_uri') . staticLink("js/{$url}") . "' type='text/javascript'></script>";
    }

    /**
     * wrapper function for Routes::url
     *
     * @param string $route - route to fetch
     * @param array $vars - vars to set in the route
     * @access protected
     * @return string
     */
    protected function url($route, $vars = array())
    {
        return $this->routes->url($route, $vars);
    }

    /**
     * returns a string that works as a URI to images
     *
     * @param string $filename - filename of image
     * @access protected
     * @return string
     */
    protected function imgLink($filename)
    {
        return $this->config->get('app.public_uri') . "img/{$filename}";
    }

    /**
     * returns a string with html for the menu of the site
     *
     * @access protected
     * @return string
     */
    protected function generateMenu() {
        $return = '';

        if ($this->user) {
            $return = <<<HTML
<ul class='topmenu'>
    <li class='topmenu-item'>
        Deltagere
        <ul class='submenu'>
            <li><a href='{$this->url('deltagerehome')}'>Oversigt</a></li>
            <li><a href='{$this->url('vis_alle_deltagere')}'>Alle deltagere</a></li>
            <li><a href='{$this->url('vis_alle_deltagere')}?show_search_box=true'>Deltager-søgning</a></li>
            <li><a href='{$this->url('vis_spilledere')}'>Spilledere</a></li>
            <li><a href='{$this->url('opret_deltager')}'>Opret deltager</a></li>
            <li><a href='{$this->url('checkin_interface')}'>Checkin registrering</a></li>
            <!--<li><a href='{$this->url('edit_participant_types')}'>Rediger deltagertyper</a></li> This one isn't implemented yet-->
            <li><a href='{$this->url('show_double_bookings')}'>Tjek for dobbelt-bookinger</a></li>
            <li><a href='{$this->url('show_refund')}'>Deltagere der skal have penge tilbage</a></li>
HTML;

            if ($this->user->hasRole('Infonaut') || $this->user->hasRole('Admin')) {
                $return .= <<<HTML
            <li><a href='{$this->url('template_editing')}'>ID skabeloner</a></li>
            <li><a href='{$this->url('show_missing_photo')}'>Arrangører med manglende ID-billede</a></li>
            <li><a href='{$this->url('photo_download')}'>Hent billeder til ID</a></li>
HTML;
            }

            $return .= <<<HTML
            <li><a href='{$this->url('name_tag_list')}'>Liste til navneskilte</a></li>
HTML;
            if ($this->user->hasRole('Admin')) {
                $return .= <<<HTML
            <li><a href='{$this->url('register_mobilepay_payments')}'>Registrer betalinger</a></li>
HTML;
            }
            $return .= <<<HTML
        </ul>
    </li>
    <li class='topmenu-item'>
        Aktiviteter
        <ul class='submenu'>
            <li><a href='{$this->url('aktiviteterhome')}'>Oversigt</a></li>
            <li><a href='{$this->url('vis_alle_aktiviteter')}'>Alle aktiviteter</a></li>
            <li><a href='{$this->url('activities_graphed')}'>Grafisk oversigt</a></li>
            <li><a href='{$this->url('opret_aktivitet')}'>Opret aktivitet</a></li>
			<li><a href='{$this->url('import_activities')}'>Importer/eksporter aktiviter</a></li>
            <li><a href='{$this->url('show_vote_stats')}'>Afstemnings-statistik</a></li>
            <li><a href='{$this->url('priority_signup_statistics')}'>Tilmeldings-statistik</a></li>
            <li><hr/></li>
            <li><a href='{$this->url('create_gm_briefings')}'>Opret spilleder briefings</a></li>
            <li><hr/></li>
            <li><a href='{$this->url('holdhome')}'>Hold</a></li>
            <li><a href='{$this->url('vis_alle_hold')}'>Alle hold</a></li>
            <li><a href='{$this->url('opret_hold')}'>Opret hold</a></li>
            <li><hr/></li>
            <li><a href='{$this->url('gamemaster_list_export')}'>Eksporter GM liste</a></li>
        </ul>
    </li>
    <li class='topmenu-item'>
        Lokaler
        <ul class='submenu'>
            <li><a href='{$this->url('vis_alle_lokaler')}'>Alle lokaler</a></li>
            <li><a href='{$this->url('lokale_brug', array('day' => 0))}'>Lokalebrug</a></li>
            <li><a href='{$this->url('opret_lokale')}'>Opret lokale</a></li>
            <li><a href='{$this->url('sleep_statistics')}'>Sovestatistik</a></li>
        </ul>
    </li>
    <li class='topmenu-item'>
        Wear
        <ul class='submenu'>
            <li><a href='{$this->url('wearhome')}'>Oversigt</a></li>
            <li><a href='{$this->url('wear_breakdown')}'>Bestillings-oversigt</a></li>
            <li><a href='{$this->url('detailed_order_list')}'>Alle bestillinger</a></li>
            <li><a href='{$this->url('detailed_unfilled_order_list')}'>Alle ikke-udleverede</a></li>
            <li><a href='{$this->url('show_wear')}'>Wear-typer</a></li>
            <li><a href='{$this->url('create_wear')}'>Opret wear-type</a></li>
            <li><a href='{$this->url('wear_handout')}'>Wear-udlevering</a></li>
        </ul>
    </li>
    <li class='topmenu-item'>
        Mad
        <ul class='submenu'>
            <li><a href='{$this->url('madhome')}'>Oversigt</a></li>
            <li><a href='{$this->url('show_food_types')}'>Mad-typer</a></li>
            <li><a href='{$this->url('create_food')}'>Opret mad-type</a></li>
            <li><a href='{$this->url('food_stats')}'>Statistik</a></li>
            <li><a href='{$this->url('food_handout')}'>Mad-udlevering</a></li>
            <li><a href='{$this->url('show_tradeable_food')}'>Madbørs</a></li>
HTML;

        if ($this->user && $this->user->hasRole('admin')) {
            $return .= <<<HTML
            <li><a href='{$this->url('reset_participant_foodtime')}'>Reset madtidsfordeling</a></li>
HTML;

        }

        $return .= <<<HTML
        </ul>
    </li>
    <li class='topmenu-item'>
        <span>Economy</span>
        <ul class='submenu'>
            <li><a href='{$this->url('economy_breakdown')}'>Budget oversigt</a></li>
            <li><a href='{$this->url('detailed_budget')}'>Deltager-budget</a></li>
            <li><a href='{$this->url('accounting_overview')}'>Regnskabs-oversigt</a></li>
        </ul>
    </li>
    <li class='topmenu-item'>
        GDS
        <ul class='submenu'>
            <li><a href='{$this->url('gdshome')}'>Vagt-oversigt</a></li>
            <li><a href='{$this->url('gds_categories')}'>Vagt-kategorier</a></li>
        </ul>
    </li>
    <li class='topmenu-item'>
        Indgang
        <ul class='submenu'>
            <li><a href='{$this->url('show_entries')}'>Alle indgangs-typer</a></li>
            <li><a href='{$this->url('create_entry')}'>Opret indgangs-type</a></li>
            <li><a href='{$this->url('entry_stats')}'>Statistik</a></li>
        </ul>
    </li>
HTML;

        if ($this->user && $this->user->canAccess('ShopController', 'main')) {
            $return .= <<<HTML
    <li class='topmenu-item'>
        <a href='{$this->url('shop_overview')}'>Kiosk</a>
    </li>
HTML;
        }

        if ($this->user && $this->user->canAccess('BoardgamesController', 'overview')) {
            $return .= <<<HTML
    <li class='topmenu-item'>
        <a href='{$this->url('boardgames_overview')}'>Brætspil</a>
    </li>
HTML;
        }

        if ($this->user && ($this->user->hasRole('Infonaut') || $this->user->hasRole('Admin'))) {
            $return .= <<<HTML
    <li class='topmenu-item'>
        <a href='{$this->url('loans_overview')}'>Udlån</a>
    </li>
HTML;
        }

        $return .= <<<HTML
    <li class='topmenu-item'>
        <a href='{$this->url('log')}'>Log</a>
    </li>
HTML;
        }

        if ($this->user && $this->user->hasRole('admin')) {
            $return .= <<<HTML
    <li class='topmenu-item'>
        SMS
        <ul class="submenu">
            <li><a href='{$this->url('sms_auto_dryrun')}'>Auto send dryrun</a></li>
            <li><a href='{$this->url('admin_handle_users')}'>Manual send dryrun</a></li>
            <li><a href='{$this->url('sms_stats')}'>Statistics</a></li>
        </ul>
    </li>
    <li class='topmenu-item'>
        Admin
        <ul class='submenu'>
            <li><a href='{$this->url('admin_handle_users')}'>Users</a></li>
            <li><a href='{$this->url('admin_handle_roles')}'>Roles</a></li>
            <li><a href='{$this->url('admin_handle_privileges')}'>Privileges</a></li>
            <li><a href='{$this->url('admin_reset_signup_confirm')}'>Reset signup</a></li>
        </ul>
    </li>
    <li class='topmenu-item'>
        Tilmelding
        <ul class='submenu'>
            <li><a href='{$this->url('signup_pages')}'>Tilpads sider</a></li>
        </ul>
    </li>
HTML;
        }

        return $return . ($return ? '</ul>' : '');
    }
}
