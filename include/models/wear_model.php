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
     * @subpackage Models
     * @author     Peter Lind <peter.e.lind@gmail.com>
     * @copyright  2009 Peter Lind
     * @license    http://www.gnu.org/licenses/gpl.html GPL 3
     * @link       http://www.github.com/Fake51/Infosys
     */

    /**
     * handles all data fetching for the index controller
     *
     * @package    MVC
     * @subpackage Models
     * @author     Peter Lind <peter.e.lind@gmail.com>
     */

class WearModel extends Model
{
    /**
     * returns array of info on ordered wear
     *
     * @access public
     * @return array
     */
    public function getWearBreakdown()
    {
        $results = array();
        $wear_types = $this->createEntity('Wear')->findAll();
        foreach ($wear_types as $wear)
        {
            $results[$wear->id] = array();
        }
        $select = $this->createEntity('DeltagereWear')->getSelect();
        $select->setFrom('wearpriser')->
                 setTableWhere('wearpriser.id','deltagere_wear.wearpris_id')->
                 setGroupBy('wearpris_id')->
                 setGroupBy('size')->
                 setGroupBy('wear_id')->
                 setField('wearpris_id')->
                 setField('size')->
                 setField('wear_id')->
                 setField('SUM(antal) AS antal',false)->
                 setOrder('wearpris_id','asc')->
                 setOrder('size','asc');
        $DB = $this->db;
        if ($result = $DB->query($select))
        {
            foreach ($result as $row)
            {
                $results[$row['wearpris_id']][] = $row;
            }
        }
        return $results;
        
    }

    /**
     * wrapper for Wear->getWearSizes()
     *
     * @access public
     * @return array
     */
    public function getWearSizes()
    {
        return $this->createEntity('Wear')->getWearSizes();
    }

    /**
     * returns all Wear entities
     *
     * @access public
     * @return array
     */
    public function getAllWear()
    {
        return $this->createEntity('Wear')->findAll();
    }

    /**
     * returns all WearPriser entities
     *
     * @access public
     * @return array
     */
    public function getAllWearprices()
    {
        $select = $this->createEntity('WearPriser')->getSelect();
        $select->setOrder('wear_id','asc');
        $select->setOrder('brugerkategori_id','asc');
        return $this->createEntity('WearPriser')->findBySelectMany($select);
    }

    /**
     * returns array with participants that have ordered wear
     *
     * @param int    $type - which type of wear to get results for
     * @param string $size - which size to get results for
     *
     * @access public
     * @return array
     */
    public function getDeltagereWithWearOrders($type = null, $size = null, $unfilled = false) {
        $select = $this->createEntity('DeltagereWear')->getSelect();
        $select->setField('deltager_id');
        $select->setField('MAX(wearpris_id) as wearpris_id', false);
        $select->setField('MAX(size) as size', false);
        $select->setGroupBy('deltager_id');
        $select->setOrder('deltager_id','asc');
        if ($unfilled) {
            $select->setWhere('received', '=', 'f');
        }
        if ($type) {
            $select->setWhere('wearpris_id', '=', $type);
        }
        if ($size) {
            $select->setWhere('size', '=', strtoupper($size));
        }
        return $this->createEntity('DeltagereWear')->findBySelectMany($select);
    }

    /**
     * returns orders for a given wearprice and size
     *
     * @param int    $type - id for Wearpriser
     * @param string $size - size to get orders for
     *
     * @access public
     * @return array
     */
    public function getWearOrders($type = null, $size = null)
    {
        $dw = $this->createEntity('DeltagereWear');
        $select = $dw->getSelect();
        if ($type)
        {
            $select->setWhere('wearpris_id','=',$type);
        }
        if ($size)
        {
            $select->setWhere('size','=',$size);
        }
        return $dw->findBySelectMany($select);
    }

    /**
     * tries to create a wear type
     *
     * @param RequestVars $post - POST vars
     *
     * @access public
     * @return false|Wear
     */
    public function createWear(RequestVars $post)
    {
        $wear         = $this->createEntity('Wear');
        $size_array   = $wear->getWearSizes();
        $size_flipped = array_flip($size_array);

        if (empty($post->navn) || empty($post->min_size) || empty($post->max_size) || !in_array($post->min_size, $size_array) ||  !in_array($post->max_size, $size_array) || $size_flipped[$post->min_size] > $size_flipped[$post->max_size]) {
            return false;
        }

        $wear->navn           = ((!empty($post->navn)) ? $post->navn : '');
        $wear->beskrivelse    = ((!empty($post->beskrivelse)) ? $post->beskrivelse : '');
        $wear->size_range     = $post->min_size . '-' .$post->max_size;
        $wear->title_en       = ((!empty($post->title_en)) ? $post->title_en : '');
        $wear->description_en = ((!empty($post->description_en)) ? $post->description_en : '');

        if (!$wear->insert() || !$this->updatePrices($wear, $post)) {
            return false;
        }

        return $wear;
    }

    /**
     * tries to update a wear type
     *
     * @param Wear        $wear - Wear entity
     * @param RequestVars $post - POST vars
     *
     * @access public
     * @return bool
     */
    public function updateWear(Wear $wear, RequestVars $post)
    {
        if (!$wear->isLoaded()) {
            return false;
        }

        $size_array   = $wear->getWearSizes();
        $size_flipped = array_flip($size_array);

        if (empty($post->min_size) || empty($post->max_size) || !in_array($post->min_size,$size_array) ||  !in_array($post->max_size,$size_array) || $size_flipped[$post->min_size] > $size_flipped[$post->max_size]) {
            return false;
        }

        $wear->navn           = ((!empty($post->navn)) ? $post->navn : '');
        $wear->beskrivelse    = ((!empty($post->beskrivelse)) ? $post->beskrivelse : '');
        $wear->size_range     = $post->min_size . '-' .$post->max_size;
        $wear->title_en       = ((!empty($post->title_en)) ? $post->title_en : '');
        $wear->description_en = ((!empty($post->description_en)) ? $post->description_en : '');

        if (!$wear->update()) {
            return false;
        }

        return $this->updatePrices($wear, $post);
    }

    /**
     * updates the wearprices for a wear type
     *
     * @param Wear        $wear - Wear entity
     * @param RequestVars $post - Post vars
     *
     * @access protected
     * @return bool
     */
    protected function updatePrices(Wear $wear, RequestVars $post)
    {
        $priser     = $wear->getWearpriser();
        $pris_index = array_flip($this->extractIds($priser));
        $success    = true;

        if (!empty($post->wearpriceid) && !empty($post->wearprice_category) && !empty($post->wearprice_price)) {
            foreach ($post->wearpriceid as $index => $id) {
                if (isset($pris_index[$id])) {
                    $wearprice = $priser[$pris_index[$id]];
                    $wearprice->pris = $post->wearprice_price[$index];
                    $wearprice->update();

                    unset($priser[$pris_index[$id]]);

                    continue;

                } elseif ($id > 0) {
                    continue;

                }

                $new_wearprice                    = $this->createEntity('WearPriser');
                $new_wearprice->wear_id           = $wear->id;
                $new_wearprice->brugerkategori_id = $post->wearprice_category[$index];
                $new_wearprice->pris              = $post->wearprice_price[$index];

                if (!$new_wearprice->insert()) {
                    $success = false;
                }

            }

        }

        if (!$success) {
            return false;
        }

        foreach ($priser as $pris) {
            $pris->delete();

        }

        return true;
    }

    /**
     * returns all brugerkategorier
     *
     * @access public
     * @return array
     */
    public function getAllParticipantCategories()
    {
        return (($return = $this->createEntity('BrugerKategorier')->findAll()) ? $return : array());
    }

    /**
     * finds a wear price from post info
     *
     * @param RequestVars $post
     *
     * @access public
     * @return WearPriser
     */
    public function getWearOrder(RequestVars $post)
    {
        $order = $this->createEntity('DeltagereWear');
        $select = $order->getSelect();
        $select->setWhere('deltager_id', '=', $post->deltager_id);
        $select->setWhere('wearpris_id', '=', $post->wearpris_id);
        return $order->findBySelect($select);
    }

    /**
     * flips the received flag of the wear order
     *
     * @param RequestVars $post
     *
     * @access public
     * @return bool
     */
    public function flipWearOrderHandOut(RequestVars $post)
    {
        if (!($order = $this->getWearOrder($post)))
        {
            return false;
        }
        $order->received = $order->received == 't' ? 'f' : 't';
        return $order->update();
    }

    /**
     * marks a participant-wearitem relationship
     * as done
     *
     * @param RequestVars $post
     *
     * @throws Exception
     * @access public
     * @return string
     */
    public function markWearReceived(RequestVars $post) {
        $deltager = $this->ajaxWearCrud($post);
        if (!strtotime($deltager->checkin_time)) {
            throw new FrameworkException("<strong>Fejl:</strong> deltageren (ID: {$deltager->id}) er ikke tjekket ind.");
        }

        $wear_items = $deltager->getWear();
        if (!$wear_items) {
            throw new FrameworkException("<strong>Fejl:</strong> deltageren (ID: {$deltager->id}) har ikke bestilt wear.");
        }
        $handout = array();
        foreach ($wear_items as $item) {
            if ($item->received == 't') {
                throw new FrameworkException("<strong>Fejl:</strong> deltageren (ID: {$deltager->id}) har allerede fået udleveret noget af sit wear.");
            }
            $item->received = 't';
            $item->update();
            $handout[] = '<strong>' . e($item->getWearName()) . ": {$item->antal} stk. i str. {$item->size}</strong> ({$item->getDeltager()->getBrugerKategori()->navn})";
        }
        $this->log("Deltager #{$deltager->id} har fået wear udleveret af {$this->getLoggedInUser()->user}", 'Wear', $this->getLoggedInUser());
        return e($deltager->getName()) . " (ID: {$deltager->id}) markeret wear modtaget - hvis det er en fejl, så tryk på Undo-knappen. Wear, der skal udleveres:<ul><li>" . implode('</li><li>', $handout) . '</li></ul>';
    }

    /**
     * checks input vars for markFoodReceived/markFoodNotReceived
     * and returns the relevant participant
     *
     * @param RequestVars $post
     *
     * @throws Exception
     * @access protected
     * @return Deltagere
     */
    protected function ajaxWearCrud(RequestVars $post) {
        if (empty($post->user_id)) {
            throw new FrameworkException ("<strong>Fejl:</strong> bruger id mangler");
        }
        $temp_id = EANToNumber($post->user_id) ? EANToNumber($post->user_id) : $post->user_id;
        if (!($deltager = $this->createEntity('Deltagere')->findById($temp_id))) {
            throw new FrameworkException ("<strong>Fejl:</strong> ingen bruger med det id");
        }
        if ($deltager->udeblevet == 'ja') {
            throw new FrameworkException ("<strong>Fejl:</strong> deltageren er ikke tjekket ind endnu");
        }
        return $deltager;
    }

    /**
     * marks a participant-fooditem relationship
     * as undone (i.e. undoes it)
     *
     * @param RequestVars $post
     *
     * @throws Exception
     * @access public
     * @return string
     */
    public function undoReceiveWear(RequestVars $post) {
        $deltager = $this->ajaxWearCrud($post);
        $wear_items = $deltager->getWear();
        if (!$wear_items) {
            throw new FrameworkException("<strong>Fejl:</strong> deltageren (ID: {$deltager->id}) har ikke bestilt wear.");
        }
        $handout = array();
        foreach ($wear_items as $item) {
            if ($item->received == 'f') {
                throw new FrameworkException("<strong>Fejl:</strong> deltageren (ID: {$deltager->id}) har ikke fået udleveret sit wear.");
            }
            $item->received = 'f';
            $item->update();
            $handout[] = '<strong>' . e($item->getWearName()) . ": {$item->antal} stk. i str. {$item->size}</strong>";
        }
        $this->log("Deltager #{$deltager->id} har fået wear markeret ikke udleveret af {$this->getLoggedInUser()->user}", 'Wear', $this->getLoggedInUser());
        return htmlspecialchars($deltager->fornavn . " " . $deltager->efternavn, ENT_QUOTES) . " (ID: {$deltager->id}) har fået slettet markeringen for udlevering af wear. Wear markeret ikke udleveret:<ul><li>" . implode('</li><li>', $handout) . '</li></ul>';
    }
}
