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
     * handles the deltagere_wear table
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
    protected $tablename = 'deltagere_wear';

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
    public function getWearName($lang = 'da')
    {
        if (!($wearpris = $this->createEntity('WearPriser')->findById($this->wearpris_id))) {
            return false;
        }

        $wear = $this->createEntity('Wear')->findById($wearpris->wear_id); 

        return $lang === 'da' ? $wear->navn : $wear->title_en;
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
     * @param strin $size - size of the item
     * @access public
     * @return bool
     */
    public function setBestilling($deltager, $wear, $antal, $size)
    {
        if ($this->isLoaded() || !is_object($deltager) || !is_object($wear) || empty($antal) || empty($size) || $antal < 1) {
            return false;
        }

        if (!$wear->sizeInRange($size)) {
            return false;
        }

        $this->deltager_id = $deltager->id;
        $this->wearpris_id = $wear->getWearprisForDeltager($deltager)->id;
        $this->antal = $antal;
        $this->size = $size;
        return $this->insert();
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
    public function setOrderDirect($participant, $wearprice, $size, $amount)
    {
        $this->deltager_id = $participant->id;
        $this->wearpris_id = $wearprice->id;
        $this->antal       = $amount;
        $this->size        = $size;

        return $this->insert();
    }

    /**
     * sets an order for a given infonaut
     *
     * @param object $deltager - Deltagere entity
     * @param object $wear - Wear entity
     * @param int $antal - number of wear pieces to order
     * @param strin $size - size of the item
     * @access public
     * @return bool
     */
    public function setInfonautBestilling($deltager, $wear, $antal, $size)
    {
        if ($this->isLoaded() || !is_object($deltager) || !is_object($wear) || empty($antal) || empty($size) || $antal < 1) {
            return false;
        }

        if (!$wear->sizeInRange($size)) {
            return false;
        }

        $wearpriser = $wear->getWearpriser();
        $price = null;

        foreach ($wearpriser as $wearpris) {
            if ($this->createEntity('BrugerKategorier')->getCategoryName($wearpris->brugerkategori_id) == 'Infonaut') {
                $price = $wearpris;
                break;
            }
        }

        if (!$price) {
            return false;
        }

        $select = $this->getSelect();
        $select->setWhere('deltager_id','=',$deltager->id);
        $select->setWhere('wearpris_id','=',$price->id);

        if ($this->createEntity('DeltagereWear')->findBySelect($select)) {
            return false;
        }

        $this->wearpris_id = $price->id;
        $this->deltager_id = $deltager->id;
        $this->antal = $antal;
        $this->size = $size;
        return $this->insert();
    }
}
