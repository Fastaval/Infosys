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
     * handles the deltagere_wear_order table
     *
     * @package MVC
     * @subpackage Entities
     */
class DeltagereWear extends DBObject
{

    /**
     * Name of database table
     *
     * @var string
     */
    protected $tablename = 'deltagere_wear_order';

    /**
     * Display order of attributes (including or excluding size)
     *
     * @var array
     */
    private static $attribute_order_size;
    private static $attribute_order;

    /**
     * For getting extra attributes
     */
    public function __get($var) {
        if (array_key_exists($var, $this->storage)) {
            return $this->storage[$var];
        }

        // If this isn't loaded from the database, just return null
        if (!$this->isLoaded()) return null;

        // If this is loaded, check if there's an attribute type that matches variable name
        $query = 
            "SELECT * FROM deltagere_wear_order_attributes AS dwoa 
            JOIN wear_attributes AS wa ON dwoa.attribute_id = wa.id 
            WHERE dwoa.order_id = ? AND wa.attribute_type = ?";

        $result = $this->db->query($query, [$this->storage['id'], $var]);
        if(count($result) == 0) {
            $this->storage[$var] = null;
            return null;
        }

        $this->storage[$var] = $result[0]['attribute_id'];
        return $this->storage[$var];
    }

    public function __isset($var) {
        return $this->__get($var) !== null;
    }

    /**
     * returns the orders for a given participant
     *
     * @param object $deltager - Deltagere entity
     * @access public
     * @return array
     */
    public function getDeltagerWearBestillinger($deltager)
    {
        if (!is_object($deltager) || !$deltager->id) {
            return array();
        }

        $select = $this->getSelect();
        $select->setWhere('deltager_id', '=', $deltager->id);
        return $this->findBySelectMany($select);
    }

    /**
     * returns the participant for the current order
     *
     * @access public
     * @return object
     */
    public function getDeltager()
    {
        if (!$this->deltager_id) {
            return false;
        }

        return $this->createEntity('Deltagere')->findById($this->deltager_id);
    }

    /**
     * returns the name for the wear of the order
     *
     * @access public
     * @return string
     */
    public function getWearName($lang = 'da', $with_size = true)
    {
        if (!($wearpris = $this->createEntity('WearPriser')->findById($this->wearpris_id))) {
            return false;
        }

        $wear = $this->createEntity('Wear')->findById($wearpris->wear_id);
        $wear_name = $lang === 'da' ? $wear->navn : $wear->title_en;

        foreach ($this->getAttributes($with_size) as $attribute) {
            $wear_name .= "-". ($lang === 'da' ? $attribute['desc_da'] : $attribute['desc_en']);
        }
        return $wear_name;
    }

    /**
     * returns the wearprice for the current order
     *
     * @access public
     * @return object|bool
     */
    public function getWearpris()
    {
        if (!$this->wearpris_id) {
            return false;
        }

        return $this->createEntity('WearPriser')->findById($this->wearpris_id);
    }

    /**
     * returns the wear type for the current order
     *
     * @access public
     * @return object|bool
     */
    public function getWear()
    {
        if (!($wearpris = $this->getWearpris())) {
            return false;
        }

        return $this->createEntity('Wear')->findById($wearpris->wear_id);
    }

    /**
     * sets an order for a given participant
     *
     * @param object $deltager - Deltagere entity
     * @param object $wear - Wear entity
     * @param int $antal - number of wear pieces to order
     * @param array $attributes - attributes of the item (size,model,color etc.)
     * @access public
     * @return bool
     */
    public function setBestilling($participant, $wear, $amount, ?array $attributes)
    {
        if ($this->isLoaded() || !is_object($participant) || !is_object($wear) || empty($amount) || $amount < 1) {
            return false;
        }

        $wearprice = $wear->getWearprisForDeltager($participant);
        return $this->setOrderDirect($participant, $wearprice, $amount, $attributes);
    }

    /**
     * sets a wear order, given participant, wearprice,
     * amount and size
     *
     * @param $participant Participant to set order for
     *
     * @access public
     * @return bool
     */
    public function setOrderDirect($participant, $wearprice, $amount, ?array $attributes)
    {
        $this->deltager_id = $participant->id;
        $this->wearpris_id = $wearprice->id;
        $this->antal       = $amount;

        if (!$this->insert()) {
            return false;
        }

        foreach($attributes as $att) {
            $query = "INSERT INTO deltagere_wear_order_attributes (order_id,attribute_id) VALUES (?,?)";
            $args = [$this->id, $att];
            $this->db->exec($query, $args);
        }
        
        return true;
    }

     /**
     * returns the human readable name for the size
     *
     * @access public
     * @return string
     */
    public function getSizeName($english = false) {
        return $this->createEntity('Wear')->getSizeName($this->size, $english);
    }

    public function getAttributeOrder($with_size = false) {
        if ($with_size) {
            if (!isset(self::$attribute_order_size)) {
                self::$attribute_order_size = $this->createEntity('Wear')->getAttributeOrder(true);
            }
            return self::$attribute_order_size;
        } else {
            if (!isset(self::$attribute_order)) {
                self::$attribute_order = $this->createEntity('Wear')->getAttributeOrder(false);
            }
            return self::$attribute_order;
        }
    }

    public function getAttributes($with_size = true) {
        // Get all attributes
        $query = 
            "SELECT * FROM deltagere_wear_order_attributes AS dwoa 
            JOIN wear_attributes AS wa ON dwoa.attribute_id = wa.id 
            WHERE dwoa.order_id = ? ORDER BY position";

        $result = $this->db->query($query, [$this->storage['id']]);
        $attributes = [];
        foreach($result as $row) {
            if (!$with_size && $row['attribute_type'] == 'size') continue;
            $attributes[$row['attribute_type']] = $row;
        }

        // Sort attributes in display order
        $attributes_order = $this->getAttributeOrder($with_size);
        $ordered_attributes = [];
        foreach($attributes_order as $type) {
            if (isset($attributes[$type]))
            $ordered_attributes[$type] = $attributes[$type];
        }

        return $ordered_attributes;
    }
}
