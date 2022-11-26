<?php
/**
 * Copyright (C) 2009-2012  Peter Lind
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
 * @category  Infosys
 * @package   Controllers 
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2009-2012 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

/**
 * default controller
 *
 * @category Infosys
 * @package  Controllers 
 * @author   Peter Lind <peter.e.lind@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GPL 3
 * @link     http://www.github.com/Fake51/Infosys
 */
class WearController extends Controller {
    protected $prerun_hooks = array(
                                array('method' => 'checkUser','exclusive' => true),
                               );

    /**
     * Default method of the class
     *
     * @access public
     * @return void
     */
    public function main() {
    }

    /**
     * shows a list of all the wear types
     *
     * @access public
     * @return void
     */
    public function showTypes() {
        if ($results = $this->model->getAllWear())
        {
            $this->page->registerEarlyLoadJS('wearlist.js');
            $this->page->wear_types = $results;
        }
        else
        {
            $this->page->setTemplate('noResults');
        }
    }

    /**
     * Handles AJAX commands for the wear type list
     * 
     * @access public
     * @return void
     */
    public function showTypesAjax(){
        // ---------------------------------------------------------------------
        // Error checks
        // ---------------------------------------------------------------------
        if (!$this->page->request->isPost()) {
            header('HTTP/1.1 400 only post requests');
            exit;
        }
        $post = $this->page->request->post;
        
        if (!$post->action) {
            header('HTTP/1.1 400 no action specified');
            exit;
        }

        // ---------------------------------------------------------------------
        // Actions
        // ---------------------------------------------------------------------

        // Switch rows
        if ($post->action === 'switch_row') {
            $error = $this->model->switchRows($post->source_row, $post->destination_row);
            $done = true;
        }

        // ---------------------------------------------------------------------
        // Reply
        // ---------------------------------------------------------------------
        if (!$done) header('HTTP/1.1 400 action "'.$post->action.'" not recognized');
        if ($error) header('HTTP/1.1 500 botched it');
        exit;
    }

    /**
     * shows the details of one wear type
     *
     * @access public
     * @return void
     */
    public function showWear() {
        if (empty($this->vars['id']) || !($wear = $this->model->findEntity('Wear', $this->vars['id']))) {
            $this->page->setTemplate('noResults');
        } else {
            $this->page->model = $this->model;
            $this->page->wear = $wear;
        }
    }

    /**
     * deletes a wear type
     *
     * @access public
     * @return void
     */
    public function deleteWear() {
        if (!$this->model->userHasRole('admin')) {
            $this->hardRedirect($this->url('no_access'));
        }

        if (empty($this->vars['id']) || !($wear = $this->model->findEntity('Wear', $this->vars['id']))) {
            $this->page->setTemplate('noResults');
            return;
        }
        if (!$this->page->request->isPost() || !$this->page->request->post->delete_wear) {
            $this->hardRedirect($this->url('vis_wear', array('id' => $wear->id)));
        }

        foreach($wear->getWearpriser() as $wear_price) {
            $wear_price->delete();
        }

        $id = $wear->id;
        $name = $wear->navn;
        if ($wear->delete()) {
            $user = $this->model->getLoggedInUser();
            $this->successMessage('Wear typen blev slettet');
            $this->log("Wear Id #{$id} ({$name}) blev slettet af {$user->user}", 'Wear', $user);
            $this->hardRedirect($this->url('wearhome'));
        } else {
            $this->errorMessage('Kunne ikke slette wear typen');
            $this->hardRedirect($this->url('vis_wear', array('id' => $id)));
        }
    }

    /**
     * creates a wear type given POST vars or displays a form for creating a type
     *
     * @access public
     * @return void
     */
    public function createWear() {
        if (!$this->model->userHasRole('admin')) {
            $this->hardRedirect($this->url('no_access'));
        }

        if (!$this->page->request->isPost()) {
            $this->page->model = $this->model;
            $this->page->setTemplate('editWear');
            return;
        }
        if ($wear = $this->model->createWear($this->page->request->post)) {
            $this->successMessage('Wear typen blev oprettet.');
            $this->log("Wear Id #{$wear->id} ({$wear->navn}) oprettet af {$this->model->getLoggedInUser()->user}", 'Wear', $this->model->getLoggedInUser());
            $this->hardRedirect($this->url('vis_wear', array('id' => $wear->id)));
        } else {
            $this->errorMessage('Wear typen kunne ikke oprettes.');
            $this->hardRedirect($this->url('wearhome'));
        }
    }

    /**
     * updates a wear item or shows the edit form
     *
     * @access public
     * @return void
     */
    public function editWear() {
        if (!$this->model->userHasRole('admin')) {
            $this->hardRedirect($this->url('no_access'));
        }

        if (empty($this->vars['id']) || !($wear = $this->model->findEntity('Wear', $this->vars['id']))) {
            $this->page->setTemplate('noResults');
            return;
        }
        $this->page->wear = $wear;
        if (!$this->page->request->isPost()) {
            $this->page->model = $this->model;
            $this->page->setTemplate('editWear');
        } else {
            $post = $this->page->request->post;
            if (!empty($post->update_wear)) {
                if ($this->model->updateWear($wear, $post)) {
                    $this->successMessage('Wear type blev opdateret.');
                    $this->log("Wear Id #{$wear->id} ({$wear->name}) blev opdateret af {$this->model->getLoggedInUser()->user}", 'Wear', $this->model->getLoggedInUser());
                } else {
                    $this->errorMessage('Kunne ikke opdatere wear typen.');
                }
            } elseif (!empty($post->delete_wear)) {
                $this->page->setTemplate('displayDelete');
                return;
            } else {
                $this->errorMessage('Wear-typen blev ikke opdateret.');
            }
            $this->hardRedirect($this->url('vis_wear', array('id' => $wear->id)));
        }
    }
    /**
     * Edit available wear attributes
     *
     * @access
     * @return void
     */
    public function attributes() {

        if ($this->page->request->isPost()) {
            $post = $this->page->request->post;
            $result = $this->model->setAttribute($post);
            $status = $result->success ? '200' : '400';
            
            $data = json_encode($result->data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
            header('Status: ' . $status);
            header('Content-Type: text/plain; charset=UTF-8');
            header('Content-Length: ' . strlen($data));
            echo $data;
            exit;
        }

        $this->page->wear_attributes = $this->model->getAttributes();
        $this->page->registerEarlyLoadJS('wear_attributes.js');
    }
    /**
     * Upload image used for wear
     *
     * @access
     * @return void
     */
    public function uploadImage() {
        if ($this->page->request->isPost()) {
            if(getimagesize($_FILES["wear-image"]["tmp_name"]) === false) {
                $this->errorMessage("Infosys kunne ikke genkende filen som et billede");
                return;
            }
            
            $filename = basename($_FILES["wear-image"]["name"]);
            $subfolder = 'uploads/wear';
            $folder = PUBLIC_PATH . $subfolder;
            $target_file = $folder .'/' . $filename;
            $external = "/$subfolder/$filename";

            if (file_exists($target_file)) {
                $this->errorMessage("Der findes allerede en fil med samme navn");
                return;
            }

            if (!file_exists($folder)) {
                mkdir($folder, 0777, true);
            }

            if (move_uploaded_file($_FILES["wear-image"]["tmp_name"], $target_file)) {
                $this->successMessage("Billedet ". htmlspecialchars( basename( $_FILES["wear-image"]["name"])). " blev uploaded.");
                $this->log("{$target_file} blev uploaded af {$this->model->getLoggedInUser()->user}", 'Wear', $this->model->getLoggedInUser());
                $this->model->addImage($external);
                $this->page->image_file = $external;
            } else {
                $this->errorMessage("Der skete en ukendt fejl under upload");
            }
        }
    }
    /**
     * shows a graphical breakdown of ordered wear
     *
     * @access
     * @return void
     */
    public function wearBreakdown()
    {
        $this->page->wear_data = $this->model->getWearBreakdown();
        $this->page->wear_types = $this->model->getAllWearTypes();
        $this->page->model = $this->model;
        $this->page->sizes = $this->model->getWearSizes();
        $this->page->size_count = count($this->page->sizes);
    }

    /**
     * sets the proper layout for printing, then calls detailerOrderList
     *
     * @access public
     * @return void
     */
    public function detailedOrderListPrint() {
        $this->page->layout_template = 'printlist.phtml';
        $this->detailedOrderList();
        $this->page->setTemplate('detailedOrderList');
    }

    /**
     * displays detailed list of orders broken down by participants
     *
     * @access public
     * @return void
     */
    public function detailedOrderList() {
        $this->page->orders = $this->model->getDeltagereWithWearOrders();
        $this->page->registerLateLoadJS('weardetails.js');
    }

    /**
     * displays detailed list of unfilled orders broken down by participants
     *
     * @access public
     * @return void
     */
    public function detailedUnfilledOrderList() {
        $this->page->orders = $this->model->getDeltagereWithWearOrders(null, null, true);
        $this->page->setTemplate('detailedOrderList');
        $this->page->registerLateLoadJS('weardetails.js');
    }

    /**
     * handles ajax calls from the detailed order list, setting wear as
     * handed out or not
     *
     * @access public
     * @return void
     */
    public function detailedOrderAjax()
    {
        if (!$this->model->flipWearOrderHandOut($this->page->request->post)) {
            header('HTTP/1.1 500 botched it');
        }
        exit;
    }

    /**
     * returns a list of orders from participants, for a subset of the wear
     *
     * @access public
     * @return void
     */
    public function detailedMiniList()
    {
        $type = ((!empty($this->vars['type'])) ? $this->vars['type'] : null);
        $size = ((!empty($this->vars['size'])) ? strtoupper($this->vars['size']) : null);
        $this->page->orders = $this->model->getWearOrders($type, $size);
        $this->page->size = $size;
        $this->page->type = (($type) ? $this->model->findEntity('Wear', $type) : null);
        $this->page->headname = $type ? $this->page->type->navn : '';
        $this->page->headsize = $size ? 'str. ' . $size : '';
    }

    /**
     * outputs a string of <option> elements containing groups running an activity
     *
     * @access public
     * @return void
     */
    public function ajaxGetWear()
    {
        if (empty($this->vars['id']) || !($wear = $this->model->findEntity('Wear', $this->vars['id']))) {
            exit;
        } else {
            $result = $wear->getWearpriser();
            if (!empty($result)) {
                header('Content-Type: text/plain; encoding: UTF-8');
                $strings = array();
                foreach ($result as $wearpris) {
                    $strings[] = '{"value": "' . $wearpris->id . 
                        '", "min_size": "' . $wear->min_size .
                        '", "max_size": "' . $wear->max_size .
                        '", "text": "' . $wearpris->getCategory()->navn . ' - ' . $wearpris->pris . ',-"}';
                }
                echo '{"pairs": [' . implode(',', $strings) . ']}';
            }
        }
        exit;
    }

    /**
     * displays the wear handout registration page
     *
     * @access public
     * @return void
     */
    public function displayHandout() {
    }

    /**
     * handles ajax calls from the food handout interface
     *
     * @throws Exception
     * @access public
     * @return void
     */
    public function ajaxHandout() {
        $this->ajaxHeader();
        if (!$this->page->request->isPost()) {
            header('HTTP/1.1 400 Not using post');
            exit;
        }
        $post = $this->page->request->post;
        if (empty($post->action)) {
            header('HTTP/1.1 400 No action specified');
            exit;
        }
        try {
            switch ($post->action) {
                case 'mark-received':
                    $message = $this->model->markWearReceived($post);
                    break;
                case 'undo-received':
                    $message = $this->model->undoReceiveWear($post);
                    break;
                    /*
                case 'get-food-stats':
                    $message = json_encode($this->_model->retrieveHandoutStats($post));
                    break;
                    */
                default:
                    throw new FrameworkException("Bad action specified");
            }
            echo $message;
            exit;
        } catch (Exception $e) {
            header('HTTP/1.1 500 Messed up');
            echo $e->getMessage();
            exit;
        }
    }

    /**
     * show labels for printing for wear handout bags
     *
     * @access public
     * @return void
     */
    public function showPrintLabels()
    {
        $this->page->layout_template = 'contentonly.phtml';
        $this->page->groups = $this->model->getLabelData();
    }
}
