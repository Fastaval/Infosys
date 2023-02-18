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
     * handles the deltagere table
     *
     * @package MVC
     * @subpackage Entities
     */
class DummyParticipant extends DBObject
{
    public $is_dummy = true;
    
    /**
     * Diy signups
     *
     * @var array
     */
    private $diy_signups = [];

    /**
     * food stash
     *
     * @var array
     */
    private $food = [];

    /**
     * wear
     *
     * @var array
     */
    private $wear = [];

    /**
     * activity signups
     *
     * @var array
     */
    private $activity_signups = [];

    /**
     * entrance orders
     *
     * @var array
     */
    private $entrance = [];

    /**
     * Notes
     */
    private $notes = [];

    /**
     * dummy update method
     *
     * @access public
     * @return true
     */
    public function update()
    {
        return true;
    }

    /**
     * dummy update method
     *
     * @access public
     * @return true
     */
    public function insert()
    {
        return true;
    }

    /**
     * removes all recorded wear
     *
     * @access public
     * @return $this
     */
    public function removeAllWear()
    {
        $this->wear = [];

        return $this;
    }

    /**
     * sets a wear order for a specific wearprice,
     * amount and size
     *
     * @param DBObject $wearprice Wearprice to order
     *
     * @access public
     * @return $this
     */
    public function setWearOrder(DBObject $wearprice, $amount, ?array $attributes)
    {
        $this->wear[] = [
            'wearprice' => $wearprice,
            'amount'      => $amount,
            'attributes'=> $attributes,
        ];

        return $this;
    }

    /**
     * returns set wear orders
     *
     * @access public
     * @return array
     */
    public function getWear()
    {
        $output = [];

        foreach ($this->wear as $set) {
            $order = $this->createEntity('DeltagereWear');
            $order->wearpris_id = $set['wearprice']->id;
            $order->antal       = $set['amount'];
            $order->size        = $set['size'];
            $order->received    = 'f';

            $output[] = $order;
        }

        return $output;
    }

    /**
     * removes all activity signups
     *
     * @access public
     * @return $this
     */
    public function removeActivitySignups()
    {
        $this->activity_signups = [];

        return $this;
    }

    /**
     * signs the user up for a given afvikling, using given priority and gamer-type
     *
     * @param object $afvikling - afvikling entity
     * @param int $prioritet - the priority of this event to the user
     * @param string $tilmeldingstype - 'spiller' or 'spilleder'
     *
     * @access public
     * @return $this
     */
    public function setAktivitetTilmelding($afvikling, $prioritet, $tilmeldingstype)
    {
        $this->activity_signups[] = [
                                     'schedule' => $afvikling,
                                     'priority' => $prioritet,
                                     'type'     => $tilmeldingstype,
                                    ];

        return $this;
    }

    /**
     * returns afviklinger the user has signed up for
     *
     * @access public
     * @return array
     */
    public function getTilmeldinger()
    {
        $signups = [];

        foreach ($this->activity_signups as $data) {
            $obj = $this->createEntity('DeltagereTilmeldinger');

            $obj->prioritet       = $data['priority'];
            $obj->tilmeldingstype = $data['type'];
            $obj->afvikling_id    = $data['schedule']->id;

            $signups[] = $obj;
        }

        return $signups;
    }

    /**
     * removes all entrance relationships
     *
     * @access public
     * @return $this
     */
    public function removeEntrance()
    {
        $this->entrance = [];

        return $this;
    }

    /**
     * removes all entrance relationships except ones with the given id
     *
     * @access public
     * @return $this
     */
    public function removeEntranceExcept(Array $ids) {
        $saved = [];
        foreach ($this->entrance as $key => $item) {
            if (!in_array($item->id, $ids)) continue;
            $saved[] = $item;
        }
        $this->entrance = $saved;

        return $this;
    }
    

    /**
     * signs the user up for a given entry type
     *
     * @param object $indgang - Indgang entity
     * @access public
     * @return bool
     */
    public function setIndgang($indgang)
    {
        $this->entrance[] = $indgang;

        return $this;
    }

    /**
     * returns what entry options the deltager selected on signup
     *
     * @access public
     * @return array
     */
    public function getIndgang()
    {
        return $this->entrance;
    }

    /**
     * removes diy signups
     *
     * @access public
     * @return $this
     */
    public function removeDiySignup()
    {
        $this->diy_signups = [];

        return $this;
    }

    /**
     * signs the user up for a gds vagt
     *
     * @param GDSVagter $gdsvagt
     *
     * @access public
     * @return $this
     */
    public function setGDSTilmelding(?GDSCategory $gdscategory, $period) {
        $gdstilmelding = $this->createEntity('DeltagereGDSTilmeldinger');
        $gdstilmelding->category_id = $gdscategory == null ? 0 : $gdscategory->id;;
        $gdstilmelding->period      = $period;

        $this->diy_signups[] = $gdstilmelding;

        return $this;
    }

    /**
     * returns gds vagter the user has signed up for
     *
     * @access public
     * @return array
     */
    public function getGDSTilmeldinger()
    {
        return $this->diy_signups;
    }

    /**
     * removes all food orders
     *
     * @access public
     * @return $this
     */
    public function removeFood()
    {
        $this->food = [];

        return $this;
    }

    public function removeOrderedFood() {
        return $this->removeFood();
    }

    /**
     * returns food the participant has ordered
     *
     * @access public
     * @return void
     */
    public function getMadtider()
    {
        return $this->food;
    }

    /**
     * signs the user up for a food option
     *
     * @param object $madtid - food option
     * @access public
     * @return bool
     */
    public function setMad($madtid)
    {
        $this->food[] = $madtid;

        return $this;
    }

    /**
     * returns whether the deltager is an arrangoer or not
     *
     * @return bool
     * @access public
     */
    public function isArrangoer()
    {
        return $this->createEntity('BrugerKategorier')->findById($this->brugerkategori_id)->isArrangoer();
    }

    public function speaksDanish()
    {
        return $this->main_lang == 'da';
    }

    /**
     * returns age as a float
     *
     * @access public
     * @return float
     */
    public function getAge(DateTime $at_time = null)
    {
        if (!$this->birthdate) {
            return -1;
        }

        $now = $at_time ? $at_time : new DateTime();

        $diff = $now->diff(new DateTime($this->birthdate));

        return floor($diff->y);
    }

    public function setNote($name, $content) {
        $this->notes[$name] = $content;
    }

    public function setCollection(string $column, array $values)
    {
        foreach ($values as &$value) {
            $value = strtolower($value);
        }
        $this->$column = implode(',', $values);
        return true;
    }
}
