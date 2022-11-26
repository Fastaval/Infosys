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

    /**
     * Name of the column to order by, by default
     * 
     * @var string
     */
    protected $default_order = 'position';

    /**
     * used for storing sizes once loaded from the database
     */
    private static $sizes = null;

    /**
     * checks if a given size is within the sizerange of this wear-object
     *
     * @param string $size - size to check
     * @access public
     * @return bool
     */
    public function sizeInRange($size_id)
    {
        $sizes = $this->getWearSizes();
        $max_order = array_search($this->max_size, array_column($sizes, 'size_id'));
        $min_order = array_search($this->max_size, array_column($sizes, 'size_id'));
        $check_order = array_search($size_id, array_column($sizes, 'size_id'));

        return $check_order <= $max_order && $check_order >= $min_order;
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
     * returns array of wear prices for the wear object, with only one price for organizers
     *
     * @param object $kategori - BrugerKategorier entity
     * @access public
     * @return array
     */
    public function getWearpriserSquashed()
    {
        if (!$this->isLoaded()) {
            return array();
        }

        $select = $this->createEntity('BrugerKategorier')->getSelect();
        $select->setWhere('arrangoer','=','ja');
        $organizer_cats = $this->createEntity('BrugerKategorier')->findBySelectMany($select);

        $select = $this->createEntity('WearPriser')->getSelect();
        $select->setLeftJoin('brugerkategorier','brugerkategori_id', 'brugerkategorier.id');
        $select->setWhere('wear_id', '=', $this->id);
        $select->setWhere('arrangoer','=','nej');
        $select->setField('wearpriser.id');
        $select->setField('wearpriser.wear_id');
        $select->setField('wearpriser.brugerkategori_id');
        $select->setField('wearpriser.pris');
        $participant_prices = $this->createEntity('WearPriser')->findBySelectMany($select);

        $select = $this->createEntity('WearPriser')->getSelect();
        $select->setLeftJoin('brugerkategorier','brugerkategori_id', 'brugerkategorier.id');
        $select->setWhere('wear_id', '=', $this->id);
        $select->setWhere('arrangoer','=','ja');
        $select->setField('wearpriser.id');
        $select->setField('wearpriser.wear_id');
        $select->setField('wearpriser.brugerkategori_id');
        $select->setField('wearpriser.pris');
        $organizer_prices = $this->createEntity('WearPriser')->findBySelectMany($select);

        // Check if we can combine all organizer prices into one
        $combine_organizer = false;
        if (count($organizer_cats) == count($organizer_prices)){
            $combine_organizer = true;
            $price = $organizer_prices[0]->pris;
            for($i = 1; $i < count($organizer_prices); $i++) {
                if ($price != $organizer_prices[$i]->pris) {
                    $combine_organizer = false;
                    break;
                }
            }
        }
        
        // If all organizer prices are the same, add a single price for organizers and return
        if ($combine_organizer) {
            $organizer_price = (object) [
                'id' => 0,
                'brugerkategori_id' => 0,
                'wear_id' => $this->id,
                'pris' => $organizer_prices[0]->pris 
            ];
            $participant_prices[] = $organizer_price;
            return $participant_prices;
        }

        // Return array with all individual prices
        return array_merge($participant_prices,$organizer_prices);
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
        $prices = $this->getWearpriserSquashed();
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
        if (!isset(self::$sizes)) {
            // Load sizes from DB
            $query = "SELECT * FROM wear_attributes WHERE attribute_type = 'size' ORDER BY position";
            self::$sizes = $this->db->query($query);
        }

        return self::$sizes;
    }

    public function getAttributes() {
        $query = "SELECT * from wear_attribute_available as waa JOIN wear_attributes as wa on waa.attribute_id = wa.id WHERE waa.wear_id = ? ORDER BY variant, attribute_type, position";

        $wear_attributes = [null];
        foreach($this->db->query($query, [$this->id]) as $attribute) {
            $wear_attributes[$attribute['variant']][$attribute['attribute_type']][$attribute['attribute_id']] = $attribute;
        }

        return $wear_attributes;
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

        $sizes = $this->getWearSizes();
        return $sizes[array_search($this->min_size, array_column($sizes, 'size_id'))];
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

        $sizes = $this->getWearSizes();
        return $sizes[array_search($this->max_size, array_column($sizes, 'size_id'))];
    }

    /**
     * returns a string representation of the size range
     *
     * @access public
     * @return string
     */
    public function getSizeRange()
    {
        if (!$this->isLoaded()) {
            return false;
        }

        return $this->getMinSize()['size_name_da']." - ".$this->getMaxSize()['size_name_da'];
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

    /**
     * returns the human readable name for the size
     *
     * @access public
     * @return string
     */
    public function getSizeName($id, $english = false)
    {
        $sizes = $this->getWearSizes();
        $size = $sizes[array_search($id, array_column($sizes, 'size_id'))];
        return $english ? $size['size_name_en'] : $size['size_name_da'];
    }

    public function getImages() {
        $query = 
            "SELECT wi.id, wi.image_file, wic.attribute_id, wa.attribute_type FROM wear_image as wi 
            JOIN wear_image_connection as wic ON wic.image_id = wi.id 
            LEFT JOIN wear_attributes as wa ON wa.id = wic.attribute_id
            WHERE wic.wear_id = {$this->id}";
        $result = $this->db->query($query);
        $list = [];
        foreach($result as $row) {
            if (!isset($list[$row['id']])) {
                $list[$row['id']] = [
                    'image_file' => $row['image_file'],
                    'attributes' => [],
                ];
            }
            $list[$row['id']]['attributes'][$row['attribute_type']][] = $row['attribute_id'];
        }
        return $list;
    }
}
