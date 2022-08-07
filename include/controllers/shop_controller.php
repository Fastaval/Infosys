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
 * @package    Infosys
 * @subpackage Controllers 
 * @author     Peter Lind <peter.e.lind@gmail.com>
 * @copyright  2009-2012 Peter Lind
 * @license    http://www.gnu.org/licenses/gpl.html GPL 3
 * @link       http://www.github.com/Fake51/Infosys
 */

/**
 * default controller
 *
 * @package    Infosys
 * @subpackage Controllers 
 * @author     Peter Lind <peter.e.lind@gmail.com>
 */
class ShopController extends Controller
{
    /**
     * pre run hooks
     * format of array is: an array of method (string), exclusive (bool), methodlist (array of strings) per hook
     * - method is method to run
     * - exclusive determines whether the next array consists of methods to be excluded or included in the prerun hook
     * - methodlist is the array of methods for which the prerun hook will either be run (inclusive) or not be run (exclusive)
     *
     * @var array
     */
    protected $prerun_hooks = array(
        array('method' => 'checkUser', 'exclusive' => true),
    );

    /**
     * shows all data for shop overview
     *
     * @access public
     * @return void
     */
    public function overview()
    {
        $this->page->products = $this->model->getAllProducts();
        $this->page->stats    = $this->model->getStats($this->page->products);
    }

    public function parseSpreadsheetData()
    {
        if (!$this->page->request->isPost()) {
            header('HTTP/1.1 400 Bad method');
            exit;

        }

        try {
            $updated_data = $this->model->updateShopData($this->page->request->post);

            header('HTTP/1.1 200 Done');
            header('Content-Type: application/json; charset=UTF-8');

            echo json_encode($updated_data);

        } catch (Exception $e) {
            header('HTTP/1.1 500 Error processing data');
            echo $e->getMessage();
        }

        exit;
    }

    public function ajaxUpdate()
    {
        if (!$this->page->request->isPost()) {
            header('HTTP/1.1 400 Bad method');
            exit;

        }

        try {
            $this->model->updateIndividualItem($this->page->request->post);

            header('HTTP/1.1 200 Done');

        } catch (Exception $e) {
            header('HTTP/1.1 500 Error processing data');
            echo $e->getMessage();
        }

        exit;
    }

    public function deleteProduct()
    {
        if (!$this->page->request->isPost()) {
            header('HTTP/1.1 400 Bad method');
            exit;

        }

        try {
            $this->model->deleteIndividualItem($this->page->request->post);

            header('HTTP/1.1 200 Done');

        } catch (Exception $e) {
            header('HTTP/1.1 500 Error processing data');
            echo $e->getMessage();
        }

        exit;
    }

    public function fetchProductStats()
    {
        try {
            $stats = $this->model->fetchProductStats($this->vars['id']);

            header('HTTP/1.1 200 Done');
            header('Content-Type: application/json; charset=UTF-8');

            echo json_encode($stats);

        } catch (Exception $e) {
            header('HTTP/1.1 500 Error processing data');
            echo $e->getMessage();
        }

        exit;
    }
}
