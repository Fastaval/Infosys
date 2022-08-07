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
     * @package    MVC
     * @subpackage Entities
     * @author     Peter Lind <peter.e.lind@gmail.com>
     * @copyright  2009 Peter Lind
     * @license    http://www.gnu.org/licenses/gpl.html GPL 3
     * @link       http://www.github.com/Fake51/Infosys
     */

    /**
     * handles the wear table
     *
     * @package MVC
     * @subpackage Entities
     */
class Wear extends DBObject
{

    /**
     * Name of database table
     *
     * @var string
     */
    protected $tablename = 'wear';

    private $grownup_sizes = array('XXS', 'XS', 'S', 'M', 'L', 'XL','2XL', '3XL','4XL','5XL','6XL');

    private $kids_sizes = array('2ÅR', '4/6ÅR', '8/10ÅR', '12/14ÅR', 'JuniorXS');

    /**
     * checks if a given size is within the sizerange of this wear-object
     *
     * @param string $size - size to check
     * @access public
     * @return bool
     */
    public function sizeInRange($size)
    {
        $size = strtoupper($size);

        if (!$this->isLoaded() || !(in_array($size, $this->grownup_sizes) || in_array($size, $this->kids_sizes))) {
            return false;
        }

        $sizeparts = explode('-', $this->size_range);

        $merged_sizes = array_merge($this->grownup_sizes, $this->kids_sizes);

        if (count($sizeparts) != 2 || !in_array($sizeparts[0], $merged_sizes) || !in_array($sizeparts[1], $merged_sizes)) {
            return false;
        }

        $started = false;
        $count   = count($merged_sizes);

        for ($i = 0; $i < $count; $i++) {
            $started = ((!$started && $sizeparts[0] != $merged_sizes[$i]) ? false : true);

            if ($size == $merged_sizes[$i]) {
                return (($started) ? true : false);
            }

            if ($merged_sizes[$i] == $sizeparts[1]) {
                return false;
            }

        }

    }

    /**
     * returns array of wear prices for the wear object, with the possibility
     * to narrow and select only a given participant category
     *
     * @param object $kategori - BrugerKategorier entity
     * @access public
     * @return array
     */
    public function getWearpriser($kategori = null)
    {
        if (!$this->isLoaded()) {
            return array();
        }

        $select = $this->createEntity('WearPriser')->getSelect();
        $select->setWhere('wear_id', '=', $this->id);
        if (is_object($kategori) && $kategori->isLoaded()) {
            $select->setWhere('brugerkategori_id', '=', $kategori->id);
        }

        return $this->createEntity('WearPriser')->findBySelectMany($select);
    }

    /**
     * returns array of user category id's that already have prices
     * set for them, for this wear item
     *
     * @access public
     * @return array
     */
    public function getUsedUserCategories()
    {
        $return = array();
        $prices = $this->getWearPriser();
        foreach ($prices as $price)
        {
            $return[] = $price->brugerkategori_id;
        }
        return $return;
    }

    /**
     * returns the appropriate wearprice given a participant
     *
     * @param object $deltager - Deltaqere entity
     * @access public
     * @return object
     */
    public function getWearprisForDeltager($deltager)
    {
        if (!$this->isLoaded() || !is_object($deltager) || !$deltager->isLoaded())
        {
            return false;
        }
        $select = $this->createEntity('WearPriser')->getSelect();
        if ($deltager->isArrangoer())
        {
            $select->setWhere('brugerkategori_id', '=', $this->createEntity('BrugerKategorier')->getArrangoer()->id);
        }
        else
        {
            $select->setWhere('brugerkategori_id', '=', $this->createEntity('BrugerKategorier')->getDeltager()->id);
        }
        $select->setWhere('wear_id', '=', $this->id);
        return $this->createEntity('WearPriser')->findBySelect($select);
    }

    /**
     * returns a wearprice for the wear given a category
     *
     * @param BrugerKategorier $category
     *
     * @access public
     * @return WearPriser
     */
    public function getPriceForCategory(BrugerKategorier $category)
    {
        if (!$this->isLoaded() || !$category->isLoaded())
        {
            return false;
        }
        $select = $this->createEntity('WearPriser')->getSelect();
        $select->setWhere('brugerkategori_id', '=', $category->id);
        $select->setWhere('wear_id', '=', $this->id);
        return $this->createEntity('WearPriser')->findBySelect($select);
    }

    /**
     * returns an array of available sizes (overall)
     *
     * @access public
     * @return array
     */
    public function getWearSizes()
    {
        return array_merge($this->grownup_sizes, $this->kids_sizes);
    }

    /**
     * returns the minimum size for the wear type
     *
     * @access public
     * @return string
     */
    public function getMinSize()
    {
        if (!$this->isLoaded()) {
            return false;
        }

        $sizes = explode('-', $this->size_range);
        return $sizes[0];
    }


    /**
     * returns the minimum size for the wear type
     *
     * @access public
     * @return string
     */
    public function getMaxSize()
    {
        if (!$this->isLoaded()) {
            return false;
        }

        $sizes = explode('-', $this->size_range);
        return $sizes[1];
    }

    /**
     * method docblock
     *
     * @param
     *
     * @access public
     * @return void
     */
    public function getName($english = false)
    {
        if (!$english) {
            return $this->navn;
        }

        return $this->title_en;
    }
}
