<?php
/**
 * Copyright (C) 2017  Peter Lind
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
 * PHP version 5.5+
 *
 * @category  Infosys
 * @package   Framework 
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2017 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

/**
 * handles all id template stuff
 *
 * @category Infosys
 * @package  Framework 
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class IdTemplateData
{
    /**
     * public constructor
     *
     * @param int    $id         ID of template
     * @param string $name       Name of template
     * @param string $background Background of template
     * @param array  $items      Items to render in the template
     *
     * @access public
     */
    public function __construct($id, $name, $background, array $items)
    {
        $this->id         = $id;
        $this->name       = $name;
        $this->background = $background;
        $this->items      = $items;
    }

    /**
     * returns the items of the template
     *
     * @access public
     * @return array
     */
    public function getItems()
    {
        return empty($this->items) ? [] : $this->items;
    }

    /**
     * returns the background of the template
     *
     * @access public
     * @return string
     */
    public function getBackground()
    {
        return $this->background;
    }
}
