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
    public function getWearBreakdown() {
        // Display order of attributes (without 'size')
        $attribute_order = $this->getAttributeOrder(false);

        // Collect all wear orders
        $order_list = [];
        $wear_orders = $this->createEntity('DeltagereWear')->findAll();
        foreach ($wear_orders as $order) {
            // Get attributes for the order
            $attributes = $order->getAttributes();

            // Prepare a unique array key based on attributes
            $combotext = "wear-".$order->getWear()->id;
            foreach($attribute_order as $type) {
                if (isset($attributes[$type])) {
                    $combotext .= "--$type-".$attributes[$type]['id'];
                }
            }

            // Add order to list - depending on whether the item has different sizes
            if (isset($attributes['size'])) {
                $order_list[$combotext][$attributes['size']['id']][$order->id] = $order->antal;
            } else {
                $order_list[$combotext]['none'][$order->id] = $order->antal;
            }
        }

        // Find all wear types
        $result = [];
        $wear_types = $this->createEntity('Wear')->findAll();
        foreach ($wear_types as $wear) {
            $collection = [];
            $variants = $wear->getVariants();
            if (empty($variants)) {
                $combotext = "wear-".$wear->id;

                // Count total number of orders
                $total = 0;
                $orders = [];
                if (isset($order_list[$combotext])) {
                    $orders = $order_list[$combotext];
                    foreach($orders as $size_orders) {
                        foreach($size_orders as $amount) {
                            $total += $amount;
                        }
                    }

                    // Remove asigned orders from the list
                    unset($order_list[$combotext]);
                }

                // All variants for this wear id
                $result['wear'][$wear->id] = [
                    'variants' => [[
                        'items' => [[
                            'attributes' => [],
                            'orders' => $orders,
                            'total' => $total,
                        ]],
                        'sizes' => isset($var['size']) && is_array($var['size']) ? array_keys($var['size']): [],
                    ]],
                    'object' => $wear,
                ];

                continue;
            }

            foreach($variants as $key => $var) {
                // Find each unique combination of attributes available
                $combos = $this->createVariantCombinations(0, $var, $attribute_order);
                
                // Create an item for each combination
                $items = [];
                foreach ($combos as $attributes) {
                    // Create the same unique array key to find any orders related to the item
                    $combotext = "wear-".$wear->id;
                    foreach($attribute_order as $type) {
                        if (isset($attributes[$type])) {
                            $combotext .= "--$type-".$attributes[$type];
                        }
                    }

                    // Count total number of orders
                    $total = 0;
                    $orders = [];
                    if (isset($order_list[$combotext])) {
                        $orders = $order_list[$combotext];
                        foreach($orders as $size_orders) {
                            foreach($size_orders as $amount) {
                                $total += $amount;
                            }
                        }

                        // Remove asigned orders from the list
                        unset($order_list[$combotext]);
                    }

                    // Attributes and orders for each item
                    $items[] = [
                        'attributes' => $attributes,
                        'orders' => $orders,
                        'total' => $total,
                    ];
                }

                // Add items and sizes related to each variant to the variant collection
                $collection[$key] = [
                    'items' => $items,
                    'sizes' => isset($var['size']) && is_array($var['size']) ? array_keys($var['size']): [],
                ];
            }
            // All variants for this wear id
            $result['wear'][$wear->id] = [
                'variants' => $collection,
                'object' => $wear,
            ];
        }

        $result['sizes'] = $this->getWearSizes();

        // In case we have orders that doesn't fit any items, we return them seperately for trouble shooting
        $result['unasigned_orders'] = $order_list;

        return $result;
    }

    private function createVariantCombinations($layer, $variant, $order) {
        if ($layer >= count($order)) return [[]];
        
        $sub_types = $this->createVariantCombinations($layer+1, $variant, $order);
        $type = $order[$layer];
        if (!isset($variant[$type])) return $sub_types;
        
        $combos = [];
        foreach($variant[$type] as $value) {
            foreach($sub_types as $sub) {
                $combos[] = array_merge(
                    ["$type" => $value['attribute_id']],
                    $sub
                );
            }
        }
        return $combos;
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
     * Return wear sizes in a format suitable for selection box
     *
     * @access public
     * @return array
     */
    public function getSelectSizes()
    {
        $sizes = $this->createEntity('Wear')->getWearSizes();
        $select_sizes = [];
        foreach ($sizes as $size) {
            $select_sizes[$size['size_name_da']] = $size['size_id'];
        }
        return $select_sizes;
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
     * returns all Wear entities
     *
     * @access public
     * @return array
     */
    public function getAllWearTypes()
    {
        $select = $this->createEntity('Wear')->getSelect();
        $select->setOrder('id','asc');
        return $this->createEntity('Wear')->findBySelectMany($select);
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
        $select->setGroupBy('deltager_id');
        $select->setOrder('deltager_id','asc');
        if ($unfilled) {
            $select->setWhere('received', '=', 'f');
        }
        if ($type) {
            $select->setWhere('wearpris_id', '=', $type);
        }
        if ($size) {
//            $select->setWhere('size', '=', strtoupper($size));
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
    public function getWearOrders($type = null, ?array $attributes)
    {
        $dw = $this->createEntity('DeltagereWear');
        $select = $dw->getSelect();
        if ($type) {
            $select->setLeftJoin('wearpriser', 'wearpris_id', 'wearpriser.id');
            $select->setWhere('wear_id','=',$type);
        }
        if ($attributes) {
            $query = 
                "SELECT order_id FROM deltagere_wear_order_attributes WHERE attribute_id = ?";
            foreach($attributes as $type => $id) {
                $ids = [];
                $result = $this->db->query($query, [$id]);
                foreach($result as $row) {
                    $ids[] = $row['order_id'];
                }
                if (count($ids) > 0) {
                    $select->setWhere("deltagere_wear_order.id",'IN', $ids);
                }
            }                
            
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
        $wear = $this->createEntity('Wear');

        if (empty($post->navn)) {
            return false;
        }

        $wear->navn           = ((!empty($post->navn)) ? $post->navn : '');
        $wear->beskrivelse    = ((!empty($post->beskrivelse)) ? $post->beskrivelse : '');
        $wear->title_en       = ((!empty($post->title_en)) ? $post->title_en : '');
        $wear->description_en = ((!empty($post->description_en)) ? $post->description_en : '');
        $wear->max_order      = ((!empty($post->max_order)) ? $post->max_order : 0);
        $wear->is_required    = ((!empty($post->is_required)) ? 1 : 0);

        $query = 'SELECT MAX(position) as plast FROM wear;';
        $last_pos = $this->db->query($query)[0]['plast'];
        $wear->position = $last_pos + 1;

        if (!$wear->insert()) {
            return false;
        }

        if (!$this->updateAttributes($wear, $post)) {
            return false;
        }

        if (!$this->updateImages($wear, $post)) {
            return false;
        }

        if (!$this->updatePrices($wear, $post)) {
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
        if (!$wear->isLoaded() || empty($post->navn)) {
            return false;
        }

        $wear->navn           = ((!empty($post->navn)) ? $post->navn : '');
        $wear->beskrivelse    = ((!empty($post->beskrivelse)) ? $post->beskrivelse : '');
        $wear->title_en       = ((!empty($post->title_en)) ? $post->title_en : '');
        $wear->description_en = ((!empty($post->description_en)) ? $post->description_en : '');
        $wear->max_order      = ((!empty($post->max_order)) ? $post->max_order : 0);
        $wear->is_required    = ((!empty($post->is_required)) ? 1 : 0);

        if (!$wear->update()) {
            return false;
        }

        if (!$this->updateAttributes($wear, $post)) {
            return false;
        }

        if (!$this->updateImages($wear, $post)) {
            return false;
        }

        return $this->updatePrices($wear, $post);
    }

    protected function updateAttributes(Wear $wear, RequestVars $post) {
        $attribute_types = $this->getAttributes();
        $current_attributes = $wear->getVariants();

        // Get a copy of the attribute arrays, where we control the internal pointer
        $posted_attributes = $post->attributes;

        $keys = array_keys($current_attributes);
        if (is_array($posted_attributes)) {
            $keys[] = count($posted_attributes) - 1;
        }

        if (!isset($keys) || empty($keys)) return true; // Wear type has no attributes

        $vmax = max($keys);

        $posted_attribute_set = current($posted_attributes);
        for($i = 0 ; $i <= $vmax; $i++) {
            if (is_array($posted_attribute_set)) {
                // Flip the array so we can use the IDs as keys
                $posted_attribute_set = array_flip($posted_attribute_set);
            } else {
                // Or create an empty array if we have no more attribute sets
                $posted_attribute_set = [];
            }

            foreach($attribute_types as $type => $attributes) {
                foreach($attributes as $key => $value) {
                    // Insert missing
                    if (isset($posted_attribute_set[$key]) && !isset($current_attributes[$i][$type][$key])) {
                        $query = "INSERT INTO wear_attribute_available (wear_id, attribute_id, variant) VALUES (?,?,?)";
                        $args = [$wear->id, $key, $i];
                        $this->db->exec($query, $args);
                    }

                    // Remove unselected
                    if (!isset($posted_attribute_set[$key]) && isset($current_attributes[$i][$type][$key])) {
                        $query = "DELETE FROM wear_attribute_available WHERE wear_id=? AND attribute_id=? AND variant=?";
                        $args = [$wear->id, $key, $i];
                        $this->db->exec($query, $args);
                    }
                }
            }

            // Get the next set of attributes
            $posted_attribute_set = next($posted_attributes);
        }

        return true;
    }

    protected function updateImages(Wear $wear, RequestVars $post) {
        $current_images = $wear->getImages();
        $selected_images = $post->wear_image;

        // Delete unselected and collect existing attributes
        $current_attributes = [];
        foreach($current_images as $id => $image) {
            foreach($image['attributes'] as $type => $values) {
                foreach($values as $value) {
                    if (!isset($selected_images[$id]) || !in_array($value, $selected_images[$id])) {
                        $query = "DELETE FROM wear_image_connection WHERE image_id = ? AND wear_id = ? AND attribute_id = ?";
                        $args = [$id, $wear->id, $value];
                        $this->db->exec($query, $args);
                    }
                    $current_attributes[$id][] = $value;
                }
            }
        }

        // Insert missing
        foreach($selected_images as $id => $attributes) {
            foreach($attributes as $value) {
                if (!isset($current_attributes[$id]) || !in_array($value, $current_attributes[$id])) {
                    $query = "INSERT INTO wear_image_connection (image_id,wear_id,attribute_id) VALUES (?,?,?)";
                    $args = [$id, $wear->id, $value];
                    $this->db->exec($query, $args);
                }
            }
        }

        return true;
    }

    public function addImage($location) {
        $query = "INSERT INTO wear_image (image_file) VALUES (?)";
        $args = [$location];
        $this->db->exec($query, $args);
    }

    public function getImages() {
        $query = "SELECT id, image_file FROM wear_image ORDER BY image_file";
        $result = $this->db->query($query);
        $list = [];
        foreach($result as $row) {
            $list[$row['id']] = $row['image_file'];
        }
        return $list;
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
        $categories = array_flip($this->extractIds($priser, 'brugerkategori_id'));
        $success    = true;

        // Update existing and insert new prices
        if (!empty($post->wearpriceid) && !empty($post->wearprice_category) && !empty($post->wearprice_price)) {
            foreach ($post->wearpriceid as $index => $id) {
                $category = $post->wearprice_category[$index];
                $price = $post->wearprice_price[$index];
                
                // Existing price
                if (isset($categories[$category])) {
                    $wearprice = $priser[$categories[$category]];
                    $wearprice->pris = $price;
                    $wearprice->update();

                    unset($priser[$categories[$category]]);

                    continue;
                } elseif ($id > 0) {
                    continue;
                }

                // New price
                $new_wearprice                    = $this->createEntity('WearPriser');
                $new_wearprice->wear_id           = $wear->id;
                $new_wearprice->brugerkategori_id = $category;
                $new_wearprice->pris              = $price;

                if (!$new_wearprice->insert()) {
                    $success = false;
                }
            }
        }

        if (!$success) {
            return false;
        }

        // Delete unselected prices
        foreach ($priser as $pris) {
            $pris->delete();
        }
        
        return $success;
    }

    public function getAttributeOrder($with_size = false) {
        if ($with_size) {
            if (!isset($this->attribute_order_size)) {
                $this->attribute_order_size = $this->createEntity('Wear')->getAttributeOrder(true);
            }
            return $this->attribute_order_size;
        } else {
            if (!isset($this->attribute_order)) {
                $this->attribute_order = $this->createEntity('Wear')->getAttributeOrder(false);
            }
            return $this->attribute_order;
        }
    }

    public function getAttributes() {
        $query = "SELECT * from wear_attributes WHERE attribute_type <> 'special' ORDER BY attribute_type, position";

        $attributes = $this->getAttributeOrder(true);

        $wear_attributes = [];
        foreach($attributes as $type) {
            $wear_attributes[$type] = [];
        }

        foreach($this->db->query($query) as $attribute) {
            $wear_attributes[$attribute['attribute_type']][$attribute['id']] = $attribute;
        }

        return $wear_attributes;
    }

    public function setAttribute($post) {
        if ($post->id) {
            $query = "UPDATE wear_attributes SET desc_da=?, desc_en=?";
            $args = [
                $post->da,
                $post->en,
            ];

            // Add position if set
            if($post->position) {
                $query .= " position=?";
                $args[] = $post->position;
            }
            
            // Add where clause
            $query .= " WHERE id =? AND attribute_type=?";
            $args[] = $post->id;
            $args[] = $post->type;

            $this->db->exec($query, $args);

            return (object) [
                'success' => true,
                'data' => [
                    'id' => $post->id,
                ]
            ];
        } else {
            if (!$post->position) {
                // Get last position of type and add one
                $query = "SELECT MAX(position) as plast from wear_attributes WHERE attribute_type=?";
                $position = $this->db->query($query, [$post->type])[0]['plast'] + 1;
            } else {
                $position = $post->position;
            }

            $query = "INSERT INTO wear_attributes (attribute_type, desc_da, desc_en, position) VALUES (?,?,?,?)";

            $result = $this->db->exec($query, [
                $post->type,
                $post->da,
                $post->en,
                $position,
            ]);

            return (object) [
                'success' => true,
                'data' => [
                    'id' => $result,
                    'position' => $position,
                ]
            ];
        }
    }

    /**
     * returns all brugerkategorier
     *
     * @access public
     * @return array
     */
    public function getAllParticipantCategories()
    {
        $category = $this->createEntity('BrugerKategorier');
        $categories = $category->findAll();
        $categories = $categories ? $categories : array();

        return $categories;
    }

    public function getAllOrganizerCategories(){
        $category = $this->createEntity('BrugerKategorier');
        $select = $category->getSelect();
        $select->setWhere('arrangoer', '=', 'ja');
        return $category->findBySelectMany($select);
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
            $msg = '<strong>' . e($item->getWearName('da', false)) . ": {$item->antal} stk.";
            $msg .= $item->size ? " i str. ".$item->getSizeName() : "";
            $msg .= "</strong> ({$item->getDeltager()->getBrugerKategori()->navn})";
            $handout[] =  $msg;
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
            $msg = '<strong>' . e($item->getWearName('da', false)) . ": {$item->antal} stk.";
            $msg .= $item->size ? " i str. ".$item->getSizeName() : "";
            $msg .= "</strong>";
            $handout[] =  $msg;
        }
        $this->log("Deltager #{$deltager->id} har fået wear markeret ikke udleveret af {$this->getLoggedInUser()->user}", 'Wear', $this->getLoggedInUser());
        return htmlspecialchars($deltager->fornavn . " " . $deltager->efternavn, ENT_QUOTES) . " (ID: {$deltager->id}) har fået slettet markeringen for udlevering af wear. Wear markeret ikke udleveret:<ul><li>" . implode('</li><li>', $handout) . '</li></ul>';
    }

    /**
     * returns data to use for print labels for wear handout data
     *
     * @access public
     * @return array
     */
    public function getLabelData()
    {
        $query = '
SELECT
    d.id, CONCAT(d.fornavn, " ",
    d.efternavn),
    w.navn AS wear,
    dwp.antal,
    dwp.size
FROM
    deltagere AS d
    JOIN deltagere_wear_order AS dwp ON dwp.deltager_id = d.id
    JOIN wearpriser AS wp ON wp.id = dwp.wearpris_id
    JOIN wear AS w on w.id = wp.wear_id
WHERE
    w.id IN (7, 18, 21, 22, 28, 29, 31)
ORDER BY
    d.id, w.navn';

        $labels = [];

        foreach ($this->db->query($query) as $row) {
            if (!isset($labels[$row['id']])) {
                $labels[$row['id']] = [
                                       'name' => e('ID: ' . $row['id']),
                                       'wear' => [],
                                      ];
            }

            $labels[$row['id']]['wear'][] = e(sprintf('%u %s - %s', $row['antal'], $row['size'], $row['wear']));

        }

        $groups = [];

        while (count($labels) > 0) {
            $groups[] = array_splice($labels, 0, 8);
        }

        return $groups;
    }

    public function switchRows($source_id, $dest_id) {
        
        // TODO error checks
        $source = $this->createEntity('Wear')->findById($source_id);
        $dest = $this->createEntity('Wear')->findById($dest_id);

        echo "Moving wear with ID:$source->id from Position:$source->position\n";
        echo "To position of wear with ID:$dest->id with Position:$dest->position\n";

        $source_position = $source->position;
        $source->position = $dest->position;
        $source->update();
        $dest->position = $source_position;
        $dest->update();
    }
}
